---
layout: post
title: "MIME type sniffing and the X-Content-Type-Options: nosniff header"
thumbnail: sniffing-dog-480.jpg
date: 2023-03-29
---

Even though browsers won't plainly disregard your Content-Type response header as in the days of Internet Explorer, the `X-Content-Type-Options: nosniff` header is still useful.

<!-- Photo source: https://pixabay.com/nl/photos/hond-teckel-dier-vier-legged-5082505/ -->

The MIME type indicates the file format. HTTP responses can contain a Content-Type header that indicates the MIME type. On top of that, browsers try to determine the MIME type based on the response data. E.g. if it starts with `<html>`, it is probably HTML.

IE7 used to render responses as HTML if they looked like HTML, even when the Content-Type header indicated it was not HTML, e.g. text/plain. This resulted in security issues. If an attacker could inject `<script>alert(1)` in a text response, this would be rendered as HTML and JavaScript would be executed, even if it would be rendered as plaintext before the attacker injected their content.

This kind of insecure behavior is no longer present in modern browsers. First, they take the Content-Type header pretty seriously. When in doubt, they avoid interpreting content as dangerous. So when the Content-Type header says `text/plain` but the content looks like HTML, they render it is plaintext because that is the more secure thing to do.

When there is no Content-Type response header, however, sniffing determines how the response is rendered. A response without a Content-Type header that looks like HTML gets rendered as HTML. This can be a security risk, and here X-Content-Type-Options: nosniff can help. With nosniff, a page that looks like HTML gets rendered as plain text. Of course, setting a Content-Type header would also help.

Content sniffing is also performed when loading scripts and styles. Normally, when a page contains `<script src="script.js"></script>`, the file script.js is loaded, even if it does not have the correct Content-Type response header. With nosniff, the script is not loaded. This behavior is actually [specified in the Fetch Standard](https://fetch.spec.whatwg.org/#should-response-to-request-be-blocked-due-to-nosniff?).

This in turn makes it possible for [Cross Origin Read Blocking](https://www.chromium.org/Home/chromium-security/corb-for-developers/) (CORB) to be stricter in blocking cross-origin JavaScript requests. CORB is meant to block certain cross-origin responses. When specifying nosniff, more responses can be reliably blocked.

Finally, some modern web specifications such as [Signed Exchanges](https://web.dev/signed-exchanges/) only work when nosniff is included in the response headers.

So, even though XSS through MIME type sniffing is pretty rare, the `X-Content-Type-Options` header is alive and well. It is still useful for responses without Content-Type header, and to enable stricter security behavior of the browser.
