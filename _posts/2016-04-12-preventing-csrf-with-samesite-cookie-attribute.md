---
layout: post
title: "Preventing CSRF with the same-site cookie attribute"
thumbnail: castle-240.jpg
date: 2016-04-14
---

Cookies are typically sent to third parties in cross origin requests. This can be abused to do CSRF attacks. Recently a new cookie attribute was proposed to disable third-party usage for some cookies, to prevent CSRF attacks. This post will describe the same-site cookie attribute and how it helps against CSRF.

## Third party cookies

When requesting a web page, the web page may load images, scripts and other resources from another web site. Many pages load fonts and scripts from Google, and share buttons from Facebook and Twitter. These requests are called cross-origin requests, because one "origin" or web site requests data from another one.

When requesting data from another site, any cookies that you had on that site are also sent with the request. If you are logged in to Facebook, your session cookie is sent to Facebook whenever you visit a page that contains a Facebook share button. This can be used by Facebook to track which pages you are visiting.

In this scenario, the cookies sent to Facebook are called third-party cookies. The user and the web page are the first and second party. Any other site is a third party.

![When a site includes a Facebook button, your session cookie is sent to Facebook](/images/third-party-cookies-benign.png)

## CSRF

In the previous example, a random web site caused a request to Facebook. The same principle can also be used to cause your browser to do a request to your banking web application whenever you visit an attacker's web site. This is called cross site request forgery. The user needs to be logged in to the banking application for this to work, so that the attacker can perform authenticated requests as if he were the user. 

![If you visit a malicious site, the attacker may let you send requests to your bank](/images/third-party-cookies-attack.png)

In this scenario, the session cookie for the web application is a third-party cookie, and for this attack it is crucial that it is sent to the banking application. We can prevent CSRF attacks by withholding third-party cookies: if we don't send the cookie to our bank, the bank thinks we are not logged in and the attacker won't be able to transfer money to his account.

It was already possible to disable third-party cookies in the browser settings ([Chrome](https://support.google.com/chrome/answer/95647?hl=en), [Firefox](https://support.mozilla.org/en-US/kb/disable-third-party-cookies)). This is mainly advertised as preventing tracking by Facebook, but it is also useful in preventing CSRF attacks. 

This browser setting lets the end-user disable all third-party cookies. The same-site cookie attribute, on the contrary, gives web sites fine-grained control over how to handle their cookies.

## Same-site cookie attribute

The [same-site cookie attribute](https://tools.ietf.org/html/draft-west-first-party-cookies-07) can be used to disable third-party usage for a specific cookie. It is set by the server when setting the cookie, and requests the browser to only send the cookie in a first-party context, i.e. when you are using the web application directly. When another site tries to request something from the web application, the cookie is not sent. This effectively makes CSRF impossible, because an attacker can not use a user's session from his site anymore.

The server can set a same-site cookie by adding the `SameSite=…` attribute to the `Set-Cookie` header:

    Set-Cookie: key=value; HttpOnly; SameSite=strict

There are two possible values for the same-site attribute:

* Lax
* Strict

In the strict mode, the cookie is withheld with any cross-site usage. Even when the user follows a link to another website the cookie is not sent.

In lax mode, some cross-site usage is allowed. Specifically if the request is a GET request and the request is top-level. Top-level means that the URL in the address bar changes because of this navigation. This is not the case for iframes, images or XMLHttpRequests.

This table shows what cookies are sent with cross-origin requests. As you can see cookies without a same-site attribute (indicated by 'normal') are always sent. Strict cookies are never sent. Lax cookies are only send with a top-level get request.

| request type,  | example code, | cookies sent |
|-----------|-----------|
| link      | `<a href="…">` | normal, lax |
| prerender | `<link rel="prerender" href="…">` | normal, lax |
| form get  | `<form method="get" action="…">` | normal, lax |
| form post | `<form method="post" action="…">` | normal      |
| iframe    | `<iframe src="…">` | normal      |
| ajax      | `$.get('…')` | normal      |
| image     | `<img src="…">` | normal      |

As you would expect strict mode gives better security, but breaks some functionality. Links to protected resources (e.g. https://github.com/Sjord/privateProject) won't work from other sites. Even if you are logged in to GitHub and would have access to this private project, your strict cookies won't be sent to GitHub when coming from another site. With lax mode this still works, while providing decent security by blocking cross site post requests.

As of April 2016, the same-site attribute for cookies is [implemented](https://www.chromestatus.com/feature/4672634709082112) in Chrome 51 and Opera 39.

## Conclusion

The same-site attribute gives the possibility to disable third-party usage for any cookie. This is a good method to protect against CSRF attacks, because it seems to the attacker as though you are no longer logged in to the website under attack.
