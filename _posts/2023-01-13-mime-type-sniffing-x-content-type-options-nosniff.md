---
layout: post
title: "MIME type sniffing and the X-Content-Type-Options: nosniff header"
thumbnail: sniffing-dog-480.jpg
date: 2023-03-29
---

To render things correctly, browsers want to know the file type of a response. The Content-Type response header indicates the file type, but the browser also looks at the content of a response and guesses the file format. This "sniffing" can be disabled with the header `X-Content-Type-Options: nosniff`. Even though sniffing vulnerabilities are not as common as in the days of Internet Explorer, this nosniff header is still useful.

<!-- Photo source: https://pixabay.com/nl/photos/hond-teckel-dier-vier-legged-5082505/ -->

## Sniffing caused XSS

Browsers want to know what file format a response contains, to render the response correctly. For example, the brower displays an image differently from a HTML document. The file format is typically indicated by the MIME type passed in the Content-Type response header. On top of that, browsers try to determine the MIME type based on the response data. If it starts with `<html>`, it is probably HTML.

IE7 used to render responses as HTML if they looked like HTML, even when the Content-Type header indicated it was not HTML, e.g. text/plain. This resulted in security issues. If an attacker could inject `<script>alert(1)` in a text response, this would be rendered as HTML and JavaScript would be executed, even if it would be rendered as plaintext before the attacker injected their content.

### Solved in modern browers

This kind of insecure behavior is no longer present in modern browsers. First, they take the Content-Type header pretty seriously. When in doubt, they avoid interpreting content as dangerous. So if the Content-Type header says `text/plain` but the content looks like HTML, they render it is plaintext because that is the more secure thing to do.

## Nosniff header still useful

Browsers no longer plainly ignore the Content-Type response header, but the nosniff header can still offer security in some situations.

When the response does not contain a Content-Type response header, sniffing determines how the response is rendered. A response without a Content-Type header that looks like HTML gets rendered as HTML. This can be a security risk, and here `X-Content-Type-Options: nosniff` can help. With nosniff, the page gets rendered as plain text, whether it looks as HTML or not. Of course, setting a Content-Type header would also help.

### Cross origin read blocking

Cross origin read blocking (CORB) prevents sensitive responses from being read by the current process, where they can potentially be read by side-channel vulnerabilities such as Spectre. In this scenario, the attacker's page would have something like `<img src="https://bank.com/secret-pincode.html">`. The browser will load this page. Even though it doesn't display as an image, and JavaScript can't directly access the response, it would have loaded the response in the current process memory. Using Spectre or other side-channel attacks, this memory can be read by the attacker's site. To prevent this CORB only allows image content types to be loaded in img tags.

It should also only load script content types in script tags, but it doesn't by default. When a page contains `<script src="script.js"></script>`, the file script.js is loaded, even if it does not have the correct Content-Type response header. Many pages on the internet load scripts that are incorrectly marked as HTML instead of JavaScript, and CORB does not block these responses, to prevent breaking existing pages.

With nosniff, however, the script is not loaded. This behavior is actually [specified in the Fetch Standard](https://fetch.spec.whatwg.org/#should-response-to-request-be-blocked-due-to-nosniff?). For stylesheets it works in much the same way. When specifying nosniff, more responses can be reliably blocked.

### Required for some resources

Finally, some modern web specifications such as [Signed Exchanges](https://web.dev/signed-exchanges/) only work when nosniff is included in the response headers. The [specification](https://wicg.github.io/webpackage/draft-yasskin-http-origin-signed-responses.html#section-5.3) says that a signed exchange must specify both a Content-Type header and a nosniff header. I don't think this is more out of caution than to avoid a specific vulnerability. But if a new standard from 2022 enforces nosniff, it's at least not a thing of the past.

## Peculiarities

While testing this header in Firefox and Chrome, I came across strange behavior.

### Bad support for quoted lists

The specification for `X-Content-Type-Options` allows for a list of comma-separated options. Currently, nosniff is the only supported option, but the [fetch spec](https://fetch.spec.whatwg.org/#x-content-type-options-header) is clear that the header should be interpreted as a comma-separated list of quoted strings. So this should work:

    X-Content-Type-Options: "nosniff", hello

It doesn't. Both [Firefox](https://bugzilla.mozilla.org/show_bug.cgi?id=1811029) and [Chrome](https://bugs.chromium.org/p/chromium/issues/detail?id=1408458) break on quoted strings. Firefox supports multiple unquoted strings, and Chrome supports it for loading scripts and styles, but not for toplevel navigation. This is inconsistent behavior even within one browser. Even though the Fetch spec allows for multiple values, sniffing is only disabled if `nosniff` is the first value.

### It doesn't disable sniffing

You would think that setting "nosniff" disables sniffing. [It doesn't totally disable sniffing in Firefox](https://bugzilla.mozilla.org/show_bug.cgi?id=1810123). For MP3 files, it still shows a music player, even if there is no Content-Type header. In that case, the only way Firefox can know that a response contains a MP3 file is by looking at the content. So apparently it still does sniffing, even with nosniff. Also, it will render text content and download binary content, again a result of looking at the content of the response.

## Conclusion

So, even though XSS through MIME type sniffing is pretty rare, the `X-Content-Type-Options` header is alive and well. It is still useful for responses without Content-Type header, and to enable stricter security behavior of the browser. I recommend developers and administrators to include it in all responses.
