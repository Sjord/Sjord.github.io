---
layout: post
title: "Should each form have a different CSRF token?"
thumbnail: dice-480.jpg
date: 2019-12-18
---

A common protection against CSRF attacks is to have a secret token in each POST request. Typically, this token is the same throughout the session, but in some circumstances it is more secure to rotate CSRF tokens often, or make them specific to the form they are on.

## Introduction

In a [CSRF attack](/2019/01/09/csrf/), the attacker's website sends a request that triggers an action on the target website. The "forged" request triggers an action in the user's session, even though the user did not willfully do so from the application.

A common way to prevent CSRF is to include a secret token in each form, and check that token on the server. Only the target website knows the secret token, so if a request contains that token it can be safely assumed that it is a legitimate request, and not from an attacker's site.

CSRF tokens are often bound to the user's session: while the user is logged in, they keep the same CSRF token. However, there are some security advantages to changing the CSRF token more often, or even on every request.

## Limited usability of leaked tokens

To reduce the impact of a leaked token, CSRF tokens can be made specific to a certain action or form.  If an attacker obtains a CSRF token, he can use that to perform a forged request. However, when binding CSRF tokens to a specific form, the leaked CSRF token only works on that one form. This can mitigate the impact of a leaked token. 

Consider the case when a web application has a bug that leaks the CSRF token on a user's profile page. If the CRSF token is not bound to the form, this CSRF token can be used to add a new administrator user, for example. If the use of the CSRF token is limited to the same form, it can only be used to change the user's profile. This seriously limits the result of an attack.

However, this improvement is only achieved when an attack that leaks a token is limited to a certain page. With a XSS vulnerability, it is possible to steal all CSRF tokens on all pages. In that case, it doesn't matter that CSRF tokens are restricted to a certain page, since the target page can be retrieved using JavaScript.

## BREACH

Keeping the CSRF token the same on every request makes it easier to discover it using attacks that need many requests. [BREACH](/2016/11/07/current-state-of-breach-attack/) is a side channel attack that makes it possible for a man-in-the-middle attacker to steal secrets in web pages, even on an encrypted connection. A successful attack needs many requests to extract a single secret, and this doesn't work if the secret keeps changing. Changing the CSRF token on every request provides adequate protection against BREACH, and both [Django](https://code.djangoproject.com/ticket/20869) and [Rails](https://github.com/meldium/breach-mitigation-rails) have implemented changing CSRF tokens.

Both frameworks have implemented it by encoding the actual CSRF token. The token is encoded randomly on each page, thus preventing repetitive output. However, this doesn't limit the tokens the server accepts. All tokens given out by the server are valid on every request.

## Browsing functionality

Changing the CSRF token between forms or between requests may break functionality. Form submits should keep working, even when users use multiple tabs or use the back button. Allowing one CSRF token and changing it with each request makes the site very secure, but it also breaks legitimate functionality.

## Conclusion

Having static CSRF tokens throughout the session provides adequate CSRF protection. Rotating tokens are beneficial in mitigating other attacks. While providing a slight security advantage, I would not recommend implementing rotating tokens to everyone. For most applications, this would be a lot of effort for little gain. Even more so since there are additional and easier solutions against CSRF: just use [same site cookies](/2016/04/14/preventing-csrf-with-samesite-cookie-attribute/).

## Read more

* [Preventing CSRF with the same-site cookie attribute](/2016/04/14/preventing-csrf-with-samesite-cookie-attribute/)
* [ZoneMinder #2470: Weak Cross-site Resource Forgery (CSRF) Protection](https://github.com/ZoneMinder/zoneminder/issues/2470)
* [Why refresh CSRF token per form request?](https://security.stackexchange.com/questions/22903/why-refresh-csrf-token-per-form-request)
