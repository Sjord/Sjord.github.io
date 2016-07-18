---
layout: post
title: "Using headers to avoid loading in iframe"
thumbnail: frames-240.jpg
date: 2016-07-15
---

A typical clickjacking attack loads a site in a transparent iframe and asks the user to click an underlying element. The user thinks it is interacting with the attacker's page, while the input actually goes to the transparent iframe. To avoid this, the `X-Frame-Options` header and `frame-ancestors` option in the content security policy are available to instruct browsers to not load the site in an iframe. This post explains more about these headers.

## Clickjacking with iframes

Clickjacking occurs due to a lack of display integrity: there is an element on the page that can be clicked, but it is not displayed. This means that the user can not determine the consequences of clicking on the page.

This is typically done using iframes, because iframes make it possible for input actions to perform something that the attacker's site can not: perform actions using the user's current session on another site. Although iframes are not the cause of clickjacking, they are a useful tool and a typical solution to protect against clickjacking is to refuse the page to load in an iframe.

This is a bit of a hack, because iframes are actually useful sometimes and they are not the cause of the problem. A better solution would to establish display integrity, for example by requiring elements to be fully visible to receive input. Dan Kaminsky [presented a solution](http://www.slideshare.net/dakami/i-want-these-bugs-off-my-internet-51423044) on Defcon 23, and the W3C is [working on a standard](https://dvcs.w3.org/hg/user-interface-safety/raw-file/tip/user-interface-safety.html) to ensure display integrity.

Refusing to load in an iframe is still an effective way to avoid clickjacking, because many sites have no legitimate use to be loaded in an iframe. Therefore we will look further into the headers that control this behavior.

## Headers to block iframe loading

There are two headers that control iframe loading:

    X-Frame-Options: DENY
    Content-Security-Policy: frame-ancestors 'none'

Both headers have parameters that makes it possible to block framing altogether, allow it only from within the same site, or allow it from another site.

The `X-Frame-Options` header was never standardized and is deprecated, but it is currently supported in more browsers than the `frame-ancestors` directive. `X-Frame-Options` is supported from Internet Explorer 8 on.

These headers kindly request the browser to not display the page in an iframe. The HTTP request is still done, but the resulting web page is not displayed in the iframe, and the browser typically gives an error message in the console:

![Refused to display 'http://good.internal/good.php' in a frame because an ancestor violates the following Content Security Policy directive: "frame-ancestors 'none'".](/images/chromium-iframe-block.png)

## Checking all ancestors

With both headers it is possible to specify that a page may only be framed by other pages on the same origin:

    X-Frame-Options: SAMEORIGIN
    Content-Security-Policy: frame-ancestors 'self'

This way, pages on the same domain can include each other in an iframe. However, there is a subtle difference between these two headers: `frame-ancestors` will check the origin of *all* frames, while `X-Frame-Options` only checks the frame against the top-level location. This makes a difference when you have an iframe in an iframe. 

In this example, `good.internal` has the header `X-Frame-Options: SAMEORIGIN`. It is still possible to include it in an iframe from another domain, given that than page is included in an iframe on `good.internal` again:

![Good.internal includes an iframe with evil.internal with an iframe with good.internal](/images/chromium-iframe-in-iframe.png)

This works because the origin of the inner frame, `good.internal`, is checked against the origin of the top-level frame, which is also `good.internal`. The fact that `evil.internal` is in between is ignored.

In contrast, `frame-ancestors` checks all intermediate frames, and when this header is enabled the inner frame is no longer displayed:

![Good.internal is not loaded in the iframe](/images/chromium-iframe-in-iframe-blocked.png)

## Specifying other origins

With both headers you can specify another web site that is allowed to load the content in an iframe:

    X-Frame-Options: ALLOW-FROM https://example.com/
    Content-Security-Policy: frame-ancestors https://example.com/

There are some limitations: `allow-from` is not supported in Chrome, Safari or Opera, and you can specify just one URL. With `frame-ancestors` you can specify as many locations as you want.

## Meta tag

Normally you can fake headers in HTML using a meta tag:

    <meta http-equiv="content-type" content="text/html; charset=UTF-8">

This does not work for the `X-Frame-Options` header or the `frame-ancestors` directive. In contrast to the headers, the meta tag is embedded in the page. It is handled during the rendering of the page, something which the headers are supposed to block when it is done in an iframe. To correctly block iframe loading, the frame options should be known *before* rendering the page, and that is why the options should be in a header.

Chrome tried to correctly handle the `meta` tag for `X-Frame-Options`, but subsequently [removed it](https://www.chromestatus.com/feature/6450843930853376) because it does not provide a reliable protection.

## Browser support

X-Frame-Options IE8
    ALLOW-FROM Firefox 18, IE9
frame-ancestors Chrome 45, Safari 10, Firefox 36, Opera 38
