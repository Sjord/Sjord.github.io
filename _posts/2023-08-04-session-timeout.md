---
layout: post
title: "Short session expiration does not help security"
thumbnail: time-expired-480.jpg
date: 2023-08-16
---

When logged into a web application, the session does not remain valid forever. Typically, the session expires after a fixed time after login, or after the user has been idle for some time. How long should these times be?

<!-- Photo source: https://commons.wikimedia.org/wiki/File:Time_Expired_(237355699).jpeg -->

## Introduction

> Roses are red, \
> Violets are blue, \
> Sessions expire, \
> Just to spite you.

In some web applications, sessions expire. You are logged out after a while and need to authenticate again. Current security advice is to use quite short session timeouts, such as after 15 minutes of inactivity. However, most mobile apps and big web applications such as Gmail or GitHub don't adhere to this. You can be logged in seemingly forever without authenticating again. Are these insecure? Do Google and Microsoft know better than NIST and OWASP?

## Threat model

The threat model involves an attacker gaining unauthorized access to a user's active session. This could happen through various means, such as stealing session cookies, exploiting session fixation vulnerabilities, or by using the same device as the victim.

However, would session takeover be prevented by expiring the session after 15 minutes of inactivity?

### XSS

If an attacker steals a session cookie with XSS or session fixation, the attacker immediately gains access to a valid session and can keep performing requests to keep the session alive. An absolute timeout would limit the amount of time the attacker has, but realistically this wouldn't really hinder any attacker. By definition they steal a valid session token, and can use it immediately. Presumably they are going to immediately make themselves admin, or wire all your bitcoin to their account. Since this is a targeted attack, the attacker knows about session timeouts of the application and can automate their attack to strike before the session times out.

### Logged token

If an attacker sees an old session token in the logs, or on your hard drive after they steal your computer, the session timeout probably prevented session takeover. This is an argument for session timeouts, but not necessarily for short session timeouts. Also, it would be better to protect against this by securing the logs or using hard drive encryption.

### Shared computers

Perhaps you used the shared computer in the library to access your web application, and forgot to log out. The next user of that computer could reopen the web application and take over your session.

Is this a thing? Are shared computers without user separation a thing? If so, these shouldn't be used to access web applications with sensitive information at all, no matter how short the session expiry time is. The device may already be compromised, or the [browser may remember your password](https://textslashplain.com/2023/05/16/how-do-random-credentials-mysteriously-appear/), or sensitive information remains in the browser cache.

Even if internet cafes still exist, some applications are used strictly within an company from company devices. Or people use their own mobile device to access the application. For most web applications, the threat of shared public computers is not realistic.

### The attacker has access to your device

You forgot to lock your computer when you went to lunch, and the attacker sat down at your desk and gained access to your machine.

In this case, session expiration may prevent them from gaining access to your session, if they weren't fast enough. However, they now have access to your email, Slack, password vault, SSH agent, browser, and files. They don't need your active session, they can just create a new one. Either by using the password vault or using the "forgot password" to mail a password reset mail.

One situation in which immediate access to the web application could still be prevented, if when 2FA is enabled and you took your phone or yubikey with you to lunch. But even then, the attacker could install a browser extension that sends your credentials to them the next time you log in.

## Reauthentication is risky

Perhaps you prefer short sessions just to be on the safe side. However, short sessions have disadvantages, both in user experience and in security. If someone needs to log in again every 15 minutes, they are going to make authenticating as easy as possible. That means keeping the password vault open, choosing an easier password, or putting the password on the clipboard every time. Reauthentication comes with its own risks. A shorter expiration time does not automatically reduce the overall risk.

## Conclusion

Session tokens are pretty secure. The threats described above are easily fixed with other measures, such as disk encryption, locking your computer, or HttpOnly cookies.

Even so, if someone compromises your session, you're screwed whether it lasts five minutes or for ever. Attacks that are prevented by short session timeouts are really rare.

Finally, short session timeouts come with security and user experience costs.

Facebook, Google, Amazon and GitHub have sessions that never expire. They think it's an acceptable risk. I think they are right.

## Read more

* [Balance User Experience and Security to Retain Customers](https://auth0.com/blog/balance-user-experience-and-security-to-retain-customers/)
* [Why aren‘t physically-local attacks in Chrome’s threat model?](https://chromium.googlesource.com/chromium/src/+/master/docs/security/faq.md#why-arent-physically_local-attacks-in-chromes-threat-model)
