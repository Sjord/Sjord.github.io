---
layout: post
title: "Preventing CSRF with the Same-site cookie attribute"
thumbnail: castle-240.jpg
date: 2016-04-12
---

TODO write intro

## Third party cookies

When requesting a web page, the web page may load images, scripts and other resources from another web site. Many pages load fonts and scripts from Google, and share buttons from Facebook and Twitter. These requests are called cross-origin requests, because one "origin" or web site requests data from another one.

When requesting data from another site, any cookies that you had from that site are also sent with the request. If you are logged in to Facebook, your session cookie is sent to Facebook whenever you visit a page that contains a Facebook share button. 

In this scenario, the cookies sent to Facebook are called third-party cookies. The user and the webpage are the first and second party. Any other site is a third party.

![When a site includes a Facebook button, your session cookie is sent to Facebook](/images/third-party-cookies-benign.png)

## CSRF

Cross site request forgery is when an attacker's site lets you perform actions on another site without your consent. This requires the victim to visit a malicious webpage, and to be logged in in the web application under attack. When the attacker's site causes the browser to perform a request to the other web application, the session cookie is sent with the request and the request is performed as an authorized user. 


It was already possible to disable third-party cookies in the browser settings ([Chrome](https://support.google.com/chrome/answer/95647?hl=en), [Firefox](https://support.mozilla.org/en-US/kb/disable-third-party-cookies)). This is mainly advertised as preventing tracking by any page that has a Facebook button, but it is also useful in preventing CSRF attacks.

If you disable third-party cookies, your session cookie is no longer sent when the attacker forges a request to another web application. This means that the attacker can't use your authenticated session to perform actions on the third-party website.

So blocking third-party cookies helps in preventing CSRF. Until now it was only possible for end-users to allow or disable all third-party cookies. The new same-site cookie attribute changes this, giving websites fine-grained control over how to handle their cookies.

![When a site includes a Facebook button, your session cookie is sent to Facebook](/images/third-party-cookies-attack.png)

## Same-site cookie attribute

The same-site cookie attribute can disable third-party usage for a specific cookie. When set on a session cookie, for example, this session cookie will only be sent in a first-party session, i.e. when you are using the web application directly. When another site tries to request something from the web application, the cookie is not sent. This effectively makes CSRF impossible, because an attacker can not use a users session from his site anymore.

There are two possible values for the samesite attribute:

* Lax
* Strict

In the strict mode, the cookie is witheld with any cross-site usage. Even when the user follows a link to another website the cookie is not sent.

In lax mode, some cross-site usage is allowed. Specifically if the request is a GET request and the request is top-level. Top-level means that the URL in the address bar changes because of this navigation. This is not the case for iframes, images or XMLHttpRequests.
