---
layout: post
title: "Prevent CSRF with the Origin request header"
thumbnail: switchboard-240.jpg
date: 2018-08-01
---

<!-- photo source https://www.flickr.com/photos/88121076@N02/8455371254 -->

## Cross-site requests



## The Referer header

The Referer header is a pretty old header that contains the URL the user came from. If you click on a link, the URL of the current page is sent in the Referer header to the requested link. In other words, this could be used to determine where the user came from, which can help us to block cross-site requests. However, there are two problems with the Referer header.

First, the Referer header is poorly specified. It is not specified on which requests the header should be sent, or even if it should be sent at all. Even though most browsers do send this header, there is no specification that says they should.

Secondly, the Referer header leaks the whole URL to other domains. If the URL contains sensitive data such as the session token or some other identifier, that is leaked when the URL is sent in the Referer header when the user clicks a link. This is the reason that many anti-virus solutions strip the Referer header from all HTTP requests, to avoid leaking sensitive data in the URL. Because so many anti-virus solutions strip the header, we can't rely on the Referer header to be present.

## The Origin header

### null value

> In some scenarios, the string "null" is sent in lieu of origin information. This is done to indicate that the cause of the request is not trustworthy, even though it may come from the same origin. Certain requests are not generally useful as state-changing triggers (like requests for stylesheets, images or window navigation) and probably should not be trusted even if sent same-origin. 

There can be multiple origins in the origin header.

* [Bug 446344 - Implement Origin header CSRF mitigation](https://bugzilla.mozilla.org/show_bug.cgi?id=446344)
* [Issue #10482384 - Send "Origin" HTTP header on POST form submit](https://developer.microsoft.com/en-us/microsoft-edge/platform/issues/10482384/)
* [Robust Defenses for Cross-Site Request Forgery (PDF)](https://seclab.stanford.edu/websec/csrf/csrf.pdf)
* [RFC6454 - The Web Origin Concept](https://datatracker.ietf.org/doc/rfc6454/)
* [Origin header on Mozilla wiki](https://wiki.mozilla.org/Security/Origin)
* [When does Firefox set the Origin header to null in POST requests?](https://stackoverflow.com/questions/42239643/when-does-firefox-set-the-origin-header-to-null-in-post-requests)
