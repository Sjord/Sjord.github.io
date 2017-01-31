---
layout: post
title: "Cookie prefixes"
thumbnail: cookies-sprinkle-240.jpg
date: 2017-02-02
---

Cookies can be overwritten by a man-in-the-middle attacker, even when using HTTPS. Using special cookie prefixes makes cookies more secure.

## Same origin policy and cookies

The same origin policy prevents sites from interacting with each other. For example, it would be a bad thing if evil.com could interact with legitbank.com using your current session. To prevent this, the same origin policy dictates that sites can only communicate with themselves.

The same origin policy was thought up in the early days of the web, in 1995. Cookies were invented even earlier and although they have some form of same origin policy, it is not the same strict policy that is used for Javascript. For Javascript, the scheme, domain and port must match. For cookies, only the domain name is considered.

This makes it possible for sites that share a domain to modify cookies belonging to another web application. The implications for this are particularly interesting when considering a man-in-the-middle attacker.

## Cookie clobbering by a man in the middle attacker

Suppose you are visiting legitbank.com over HTTPS. During your visit, you take a break to visit cutecatpictures.com over HTTP. A man-in-the-middle attacker may change the response from cutecatpicutres.com to include a request to legitbank.com over HTTP. The attacker can then change that response to overwrite a cookie for legitbank.com. This means that a man-in-the-middle attacker can overwrite cookies, even for HTTPS sites.

This even works for secure cookies. Insecure sites can overwrite secure cookies.

It does not work if legitbank.com has strict transport security with includeSubdomains on. The includeSubdomains flag is important, as subdomains can also set cookies for the parent domain. The attacker can use any subdomain, like something.legitbank.com, to set a cookie for legitbank.com.

## Making cookies more secure

There is [a proposal](https://tools.ietf.org/html/draft-ietf-httpbis-cookie-alone-01) to disallow modification of secure cookies by insecure sites. However, will probably take some time and maybe break a few things.

To make cookies more secure in a backward compatible way, [cookie prefixes](https://tools.ietf.org/html/draft-ietf-httpbis-cookie-prefixes-00) is a possible solution. It uses a dirty trick to put a flag in the name of the cookie. When a cookie name starts with this flag, it triggers additional browser policy on the cookie in supporting browsers.

The `__Secure-` prefix makes a cookie accessible from HTTPS sites only. A HTTP site can not read or update a cookie if the name starts with `__Secure-`. This protects against the attack we earlier described, where an attacker uses a forged insecured site to overwrite a secure cookie.

The `__Host-` prefix does the same as the `__Secure-` prefix and more. A `__Host-`-prefixed cookie is only accessible by the same domain it is set on. This means that a subdomain can no longer overwrite the cookie value.

## Conclusion

Cookies can be overwritten by attackers in some cases, even if using secure cookies over HTTPS. Cookie prefixes change the browser policy on cookies, making this no longer possible.

Cookie prefixes are [currently supported](https://www.chromestatus.com/feature/4952188392570880) in Chrome and Firefox.
