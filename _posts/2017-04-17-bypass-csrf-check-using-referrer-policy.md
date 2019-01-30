---
layout: post
title: "Bypass CSRF checks using referrer policy"
thumbnail: silence-240.jpg
date: 2017-06-21
---

The referer header is sometimes used as [CSRF protection](/2019/01/09/csrf/). This post describes a method for the attacker to remove the referer header, which can bypass some CSRF checks.

## Checking for CSRF with the referer header

The `Referer` HTTP header contains the URL of the previous page. If you click on a link on this page, a GET request is done with the URL of this page as the value for the referer header. This is useful to see where the traffic to your site comes from, but can also be used to prevent CSRF. Cross site request forgery consists of requests that, by definition, come from another site. By blocking any request that has another domain in the referer header, you can block forged requests.

The advantage of this is that you don't need to keep state on the server, like with most CSRF tokens. The disadvantage is that the referer header is not exactly mandatory, so some clients may not send a referer header at all. This means that web applications that check the CSRF need to have a policy as to what to do with requests that have no referer header, block them or allow them?

## Removing the referer header with referrer policy

Up until recently it was not possible for a web site to remove the referer header from requests that it caused, but this changed with the introduction of [referrer policy](https://www.w3.org/TR/referrer-policy/). This specification is meant to solve another security problem: the leaking of URLs. If you are on a secret page and click a link to another web site, the URL of the secret page is sent in the referer header. To prevent leaking of any secrets in the URL, referrer policy makes it possible to limit the information in the referer header, or to disable the referer header at all.

This is bad news for sites that use the referer header for CSRF prevention, and allow any requests that are missing a referer header. Since the referer header is now under the attacker's control, he can remove the referer header from any forged requests and bypass the CSRF protection.

All that is needed is the following HTML on the page:

    <meta name="referrer" content="no-referrer">

This will remove the referer header from any outgoing request. This is [supported](http://caniuse.com/#feat=referrer-policy) in all modern browsers.

Referrer policy has no impact on the [`Origin` header](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Origin), which is similar to the referer header.

## Conclusion

The referer header can in some circumstances be used as an effective protection against CSRF. However, with the introduction of referrer policy the attacker can determine whether or not this header is sent with the request, and CSRF protection mechanisms need to handle an empty referer header by blocking it.
