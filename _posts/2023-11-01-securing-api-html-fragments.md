---
layout: post
title: "Securing HTML fragments returned by API endpoints"
thumbnail: html-fragment-480.jpg
date: 2023-11-08
---

API endpoints are not supposed to be accessed directly with a browser, but attackers can still use this to exploit vulnerabilities. By checking request headers and setting response headers, we can change the behavior of an API endpoint when it is accessed with top-level navigation.

<!-- Photo source: https://pixabay.com/photos/smartphone-paper-letter-write-pen-4905176/ -->

## Accessing API endpoints with a browser

Many web applications request information from the backend using JavaScript. For example, a profile page performs a request to `/user/profile.php`, which responds with the user properties in a JSON document, which the profile page then shows to the user. This backend endpoint, `/user/profile.php`, is only meant to be accessed through the profile page. But of course it can also be accessed directly with the browser. This has security implications: this page can be vulnerable to cross-site scripting or content injection.

With JSON APIs, this is easy to mitigate by setting the content type correctly to `application/json`. This disables execution of JavaScript, and changes the layout of the page in browsers so that content injection is no longer a risk. However, with APIs that return HTML, this is not a suitable solution.

## Replacing parts of the page with HTML

Some web applications use JavaScript to replace part of the page with dynamic HTML. JavaScript performs a call to the backend, which returns a little piece of HTML. That HTML is then inserted in the correct place on the page, possibly replacing something else.

This pattern was all the rage around 2006, when it was possible to dynamically update parts of the page using [XMLHttpRequest](https://en.wikipedia.org/wiki/XMLHttpRequest). This was made easier by jQuery, with [jQuery.load](https://api.jquery.com/load/). It lost popularity for a while, but is now back as the backbone of [htmx](https://htmx.org/) and [Unpoly](https://unpoly.com/).

<img src="/images/html-fragment-pattern.svg" style="width: 100%">

The main page contains something like this:

```
<h1>Welcome <span hx-get="username.php" hx-trigger="load"></span></h1>
```

This triggers a request to `username.php`, which returns HTML:

```
<?php
$username = $_COOKIE['username'] ?? "Anonymous";
echo "<i>".htmlentities($username)."</i>";
```

[See it in action](https://demo.sjoerdlangkemper.nl/fragments/), [view the code](https://github.com/Sjord/Sjord.github.io/tree/master/_demo/fragments/)

## Risk of accessing the HTML API with a browser

The `username.php` endpoint is supposed to be accessed only through JavaScript requests, but of course it is also directly accessible with the browser. Even though it is never meant to be used this way, someone can browse directly to `https://demo.sjoerdlangkemper.nl/fragments/username.php` which then serves a HTML snippet with the current user's username.

If there is an cross-site scripting (XSS) vulnerability in `username.php`, this can be exploited by luring a victim to open this page in the browser. This page does not have to be accessible by a browser directly, and having that possibility increases the attack surface.

Besides cross site scripting, there is also the risk of content injection. An attacker may put a convincing message on the page. Even though this message originates from the attacker, it seems trustworthy because it is hosted on `demo.sjoerdlangkemper.nl`.

This article talks about APIs that return HTML, but it also applies partially to APIs that return XML. XML can also contain JavaScript, and may be rendered as documents in some cases.

## Solutions

### Binary content type

What is the correct content type for a HTML fragment? It probably uses `text/html` in most cases, even though it is not a full HTML document. Since it is content that is specific to our web application, it can be argued that it should be `application/...`. If we set the content type to `application/octet-stream` or `application/html`, the response will be offered for download in most browsers.

The disadvantage of this is that changing the content type from `text/html` to something else disables cross-origin read blocking (CORB). CORB protects certain responses from being read through side-channel attacks. It only protects HTML, XML and JSON responses, so marking our response as something else disables this protection.

Conceptually, the response is not really a binary stream, and `text/html` fits better. There is not really a standard MIME type for HTML fragments. I think there should be!

### Attachment disposition

Using `Content-Disposition: attachment` instructs the browser to handle the response as a download. The document is not shown and no JavaScript is executed, so this is a suitable solution.

The browser downloads the response as HTML file. Of course, it would be logically for a user to immediately open this downloaded file to inspect what is in it, partially defeating the protection. When opened this way, the file is served from the file system and not from the domain. This means that cross-site scripting is not effective anymore (because the script does not run on the same origin). Content injection may still be trustworthy, because the user just downloaded this file from a trusted domain. Other protecting headers, such as `Content-Security-Policy`, no longer apply if the file is opened from disk.

### Sandbox

Using `Content-Security-Policy: sandbox` ([MDN](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/sandbox)) restricts severely what a HTML page is able to do. One of the main restrictions is that the page cannot run JavaScript. This is an effective solution against XSS. The fragment is still visible in the browser. This is an advantage when it comes to developing and debugging, but a disadvantage in that content injection may still be possible.

Many developers seem to be under the impression that a site should have one `Content-Security-Policy` for the whole domain, but it is actually a good idea to give different parts or the application different policies.

### Checking request headers

The API endpoint should only be accessible when called from the frontend with JavaScript, so why not enforce that? Most frameworks send their own request headers:

* [HTMX](https://htmx.org/docs/#request-headers) has `HX-Request: true`
* [UnPoly](https://unpoly.com/X-Up-Version) has `X-Up-Version`
* jQuery and many other libraries have `X-Requested-With: XMLHttpRequest`

Many frameworks are moving from [XMLHttpRequest](https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest) to the [fetch API](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch), so this header is going to be removed or become a historical artifact.

The above headers are set with JavaScript within the frameworks themselves. There are also headers set by the browser:

* [Sec-Fetch-Dest](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Dest), the initiator for the request. `empty` for JavaScript requests, `document` for direct access.
* [Sec-Fetch-Mode](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Mode), the request mode. `navigator` for direct access, JavaScript requests can have several modes.
* [Sec-Fetch-Site](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Site), whether the request is cross-site or cross-origin.
* [Sec-Fetch-User](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-User), whether the request was initiated by the user.

These headers are supported in all modern browsers, and work across frameworks. This makes it easy to detect when our API endpoint is accessed in the intended manner, or whether an attacker lured a victim to open it in their browser.

When the endpoint is not used in the intended way, the application can handle it in several ways:

* Respond with 400 Bad Request or 403 Forbidden, and serve no content.
* Show the HTML response, but make it clear in the layout that this is a HTML fragment.
* Show the source code of the HTML fragment.

## Conclusion

I would use the correct content type (i.e. `text/html`) and not force downloading of API responses. Disabling JavaScript with the CSP header is an easy way to get additional security without any downsides.

* Respond with the correct `Content-Type`, and set `Content-Type-Options: nosniff`.
* Disable JavaScript with `Content-Security-Policy: sandbox; default-src 'none'; frame-ancestors 'none'`.
* For requests that have `Sec-Fetch-Mode: navigator`, deny the request or prepend a warning that the content is a HTML fragment.
* If the frontend and API are on the same origin/site, only allow requests where `Sec-Fetch-Site` is `same-origin`/`same-site`.

I am pretty sure about the first two, but have less experience with checking Sec-Fetch headers.

## Read more

* [Discussion: serving content when resource is not meant to be accessed directly · Issue #1009 · OWASP/ASVS](https://github.com/OWASP/ASVS/issues/1009)
