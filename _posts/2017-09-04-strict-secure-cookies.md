---
layout: post
title: "Leave secure cookies alone"
thumbnail: cookiejar-240.jpg
date: 2017-10-25
---

With the introduction of strict secure cookies, browsers won't allow overwriting secure cookies from insecure origins.

## Cookie clobbering

Cookies were invented before the same-origin policy was formalized. That is why historically they have had a little bit peculiar rules about which webpage can set which cookie. Specifically, it was possible to set a secure, HTTPS cookie from an insecure HTTP site. This "cookie clobbering" makes it possible for man-in-the-middle attackers to write cookies for a site that is protected by HTTPS. This problem was recently fixed by the introduction of [strict secure cookies](https://tools.ietf.org/html/draft-ietf-httpbis-cookie-alone-01).

## Strict secure cookies

The strict secure cookies specification is an addendum to the [specification on the behavior of cookies](https://tools.ietf.org/html/rfc6265), which aims to "deprecate modification of 'secure' cookies from non-secure origins". It states that exising cookies with a "secure" flag can't be overwritten by insecure hosts. So http://example.com/ can't overwrite a secure cookie on https://example.com/, something which was possible before. After the specification was finalized, this new behavior was soon implemented in browsers, and in modern browsers secure cookies can no longer be overwritten.

## Don't remove your cookies

Secure cookies can't be overwritten, but insecure sites can still create new cookies. This means that if a user does not have a secure cookie for https://example.com/, the insecure http://example.com/ can still write a secure cookie for https://example.com. If a cookie is set, it is protected from being overwritten from an insecure host. If a cookie is absent, it can still be written.

The result of this is that cookies are better secured if they are not deleted on logout. Instead of deleting the cookie, write some garbage into it. By letting the cookie exist it is protected from being overwritten.

## Or use cookie prefixes

An even better alternative is to use [cookie prefixes](/2017/02/09/cookie-prefixes/). A cookie prefixed with `__Secure-` or `__Host-` can't be written by an insecure host, whether it already exists or not.

## Conclusion

Strict secure cookies improve the security of secure cookies. It is currently implemented in browsers, and websites don't have to change anything to get advantage of it.

## Read more

* [Chrome Platform Status: Strict Secure Cookies](https://www.chromestatus.com/feature/4506322921848832)
* [Cookie prefixes](/2017/02/09/cookie-prefixes/)
* [Preventing CSRF with the same-site cookie attribute](/2016/04/14/preventing-csrf-with-samesite-cookie-attribute/)
* [Prevent session hijacking with token binding](https://www.sjoerdlangkemper.nl/2017/07/05/prevent-session-hijacking-with-token-binding/)
