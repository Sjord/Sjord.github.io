---
layout: post
title: "Should each form have a different CSRF token?"
thumbnail: dice-480.jpg
date: 2019-12-18
---


## Introduction

With cross-site request forgery (CSRF), an attacker's website submits a request that performs an action to the target website. The "forged" request triggers an action in the user's session, without the user's consent. A common way to prevent CSRF is to include a secret token in each form. Only the target website knows the secret token, so if a request contains that token it can be assumed to originate from the website itself, and not from an attacker's site.

Most web applications use a CSRF token that is bound to the current session; it stays the same throughout a user's session. However, it has some slight security advantages to use a different CSRF token for each form or for each request.

## Limit useability of leaked tokens

If an attacker obtains a CSRF token, he can use that to perform a forged request. However, when binding CSRF tokens to a specific form, the leaked CSRF token only works on that one form. This can mitigate the impact of a leaked token. 

Consider the case when a web application has a bug that leaks the CSRF token on a user's profile page. When the CRSF token is not bound to the form, this CSRF token can be used to add a new administrator user, for example. If the use of the CSRF token is limited to the same form, it can only be used to change the user's profile. This seriously limits the result of an attack.

However, this improvement is only achieved when an attack that leaks a token is limited to a certain page. With a XSS vulnerability, it is possible to steal all CSRF tokens on all pages.

## BREACH

[BREACH](/2016/11/07/current-state-of-breach-attack/) is a side channel attack that makes it possible to steal secrets. A succesful attack needs many requests to extract a secret, and this doesn't work if the secret keeps changing. Changing the CSRF token on every request provides adequate protection against BREACH, and both [Django](https://code.djangoproject.com/ticket/20869) and [Rails](https://github.com/meldium/breach-mitigation-rails) have implemented changing CSRF tokens.

## Browsing functionality

Form submits should keep working, even when users use multiple tabs or use the back button. Allowing one CSRF token and changing it with each request makes the site very secure, but it also breaks legitimate functionality.

## Conclusion

## Read more

* [Preventing CSRF with the same-site cookie attribute](/2016/04/14/preventing-csrf-with-samesite-cookie-attribute/)
* [ZoneMinder #2470: Weak Cross-site Resource Forgery (CSRF) Protection](https://github.com/ZoneMinder/zoneminder/issues/2470)
* [Why refresh CSRF token per form request?](https://security.stackexchange.com/questions/22903/why-refresh-csrf-token-per-form-request)
