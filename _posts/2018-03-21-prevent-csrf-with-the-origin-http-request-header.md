---
layout: post
title: "Prevent CSRF with the Origin request header"
thumbnail: switchboard-480.jpg
date: 2019-02-27
---

The Origin header in a HTTP request indicates where the request originated from. This can be useful in preventing cross-site request forgery.

<!-- photo source https://www.flickr.com/photos/88121076@N02/8455371254 -->

## Cross-site requests

Some cross-site requests are fine. For example, you want to be able to display an image from another domain. However, you don't want to be able to perform an authenticated request to another domain that performs some action, an attack known as [CSRF](/2019/01/09/csrf/). For sites to actively defend against CSRF, it is useful to determine which page triggers a request. If a site can determine where the request comes from, it can allow some sites and block others. The Referer header contains this information but is unreliable. The Origin header is a way to reliably add this information to a request.

## The Referer header

The Referer header is a pretty old header that contains the URL the user came from. If you click on a link, the URL of the current page is sent in the Referer header to the requested link. In other words, this could be used to determine where the user came from, which can help us to block cross-site requests. However, there are two problems with the Referer header.

First, the Referer header is poorly specified. It is not specified on which requests the header should be sent, or even if it should be sent at all. Even though most browsers do send this header, there is no specification that says they should.

Secondly, the Referer header leaks the whole URL to other domains. If the URL contains sensitive data such as the session token or some other identifier, that is leaked when the URL is sent in the Referer header when the user clicks a link. This is the reason that many anti-virus solutions strip the Referer header from all HTTP requests, to avoid leaking sensitive data in the URL. Because so many anti-virus solutions strip the header, we can't rely on the Referer header to be present.

## The Origin header

The Origin header only contains the scheme, hostname and port of the URL. This avoids the problem of leaking URL parameters to other hosts, while still making it possible to see where a request came from. The header may look like this:

    Origin: https://www.sjoerdlangkemper.nl

According to the specification it can contain multiple origins, although I haven't seen that implemented in browsers. There is also the special _null_ value to indicate the origin is not trustworthy.

### null value

If a document does not have a clear origin, or the request should not be trusted no matter the origin, the null origin can be used to indicate this.

> In some scenarios, the string "null" is sent in lieu of origin information. This is done to indicate that the cause of the request is not trustworthy, even though it may come from the same origin. Certain requests are not generally useful as state-changing triggers [...] and probably should not be trusted even if sent same-origin. 

Browsers do use the _null_ origin, for example for an iframe with a data URL.

    <iframe src="data:text/html,<form method="POST"...

Data URLs are treated as unique opaque origins by modern browsers, rather than inheriting the origin of the including page. This means that the origin of a data URL is never trustworthy, and the _null_ origin is sent to indicate this.

### Browsers differences

Even though the origin header specification describes the header and points out some uses for it, it is not well defined when to include the origin header in the request and what the value should be. Both Chrome and Firefox include the origin header in JavaScript POST requests, and their behavior differs on all other types of navigation. The origin header is included on:

* all POST forms in Chrome, none in Firefox.
* all JavaScript fetch requests in Firefox, but not HEAD and GET in Chrome.
* redirects across domains, but Firefox uses the originating domain as origin and Chrome uses the null origin.

Use [the demo page](https://demo.sjoerdlangkemper.nl/origin.php) to see how your browser behaves.

These differences make the origin header of limited use at the moment. You can't depend on the origin header to be present in a certain type of request. It is limited to a defense-in-depth: check it when it is present, but don't depend on it.

## Conclusion

Checking the origin header is a good additional measure to prevent cross-site request forgery and related attacks. Although it is already implemented in browsers, its behavior is not defined enough to depend on the origin header to be present in particular requests.

## Read more

* [Bug 446344 - Implement Origin header CSRF mitigation](https://bugzilla.mozilla.org/show_bug.cgi?id=446344)
* [Issue #10482384 - Send "Origin" HTTP header on POST form submit](https://developer.microsoft.com/en-us/microsoft-edge/platform/issues/10482384/)
* [Robust Defenses for Cross-Site Request Forgery (PDF)](https://seclab.stanford.edu/websec/csrf/csrf.pdf)
* [RFC6454 - The Web Origin Concept](https://datatracker.ietf.org/doc/rfc6454/)
* [Origin header on Mozilla wiki](https://wiki.mozilla.org/Security/Origin)
* [When does Firefox set the Origin header to null in POST requests?](https://stackoverflow.com/questions/42239643/when-does-firefox-set-the-origin-header-to-null-in-post-requests)
