---
layout: post
title: "Changing your password through CSRF in IceHRM"
thumbnail: icebreaker-240.jpg
date: 2018-08-15
---

IceHRM is an open source human resource management system. Its functionality to change the user's password is vulnerable to CSRF.

<!-- photo source: http://archive.defense.gov/homepagephotos/leadphotoimage.aspx?id=102878 -->

## Changing your password

Just like any application, IceHRM has accounts and users can log in with their username and password. Users can also change their own password. After clicking the button "Change Password", the following dialog appears:

<img src="/images/icehrm-change-password.png" alt="IceHRM dialog that asks twice for the new password">

When submitting this form, this causes a GET request to the following URL:

    https://host/app/service.php?t=Employee&a=ca&sa=changePassword&mod=modules%3Demployees&req=%7B%22pwd%22%3A%22newpassword%22%7D

This URL has several query parameters:

* `t` for type is the name of the object we are changing. It is unused for changing the password.
* `a` means action. I'm not sure what `ca` means, but it triggers code to call something on a specified module.
* `sa` for subaction is the method that is called on the module.
* `mod` is the name of the module.
* `req` is JSON-encoded data that is passed to the method. This contains the new password to set.

So something like this:

    if ($a == "ca") {
        $mod->$sa(json_decode($req));
    }

The real code can be viewed in [service.php](https://github.com/gamonoid/icehrm/blob/master/core/service.php#L106-L163), and it calls the [changePassword function in EmployeesActionManager.php](https://github.com/gamonoid/icehrm/blob/master/core/src/Employees/User/Api/EmployeesActionManager.php#L131-L150).

## Cross-site request forgery

The GET request mentioned above changes the password for the current user. The request doesn't contain any value that an attacker doesn't know, such as the current password of the user or a secret token. This makes this functionality vulnerable to CSRF: it is possible to "forge" the request from another site and change someone's password.

The attacker wants to trick the user into performing the change password request, with a password that he knows, while the user is logged in. So the attacker wants the user to visit the URL

    https://host/app/service.php?a=ca&sa=changePassword&mod=modules%3Demployees&req=%7B%22pwd%22%3A%22attacker%22%7D

Maybe he can just send him this link in an email or instant message. He can use a URL shortener to obfuscate the URL. Or he can make a website with an image that links to the URL:

    <img src="https://host/app/service.php?a=ca&sa=changePassword&mod=modules%3Demployees&req=%7B%22pwd%22%3A%22attacker%22%7D">

When the user views the page, the browser will try to request the image and by doing so, change the password of the user. The attacker knows the new password, so the attacker can log in on the account using the new password.

## Conclusion

Visiting some link can result in a compromised account. IceHRM already has CSRF tokens in some places, and they should also check for CSRF on this crucial form. Furthermore, they should use POST instead of GET for state-modifying requests, and ask the user for his current password when changing the password.
