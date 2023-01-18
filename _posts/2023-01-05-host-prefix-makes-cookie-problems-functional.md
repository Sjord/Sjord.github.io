---
layout: post
title: "Cookie prefixes turn security problems into functional problems"
thumbnail: monkey-with-cookie-480.jpg
date: 2023-03-01
---

Adding a prefix to a cookie enables security measures in such a way that the cookie doesn't work anymore in an insecure setting. This makes the problem easier to notice by the developers. A working but insecure cookie wouldn't be noticed until the next pentest, but a cookie that doesn't work at all will quickly be investigated.

<!-- photo source: https://pixabay.com/nl/photos/eekhoorn-aap-aap-%c3%a4ffchen-exotisch-1438533/ -->

## Functional cookie prefixes

If you give your cookie a special name, it gains certain security properties. I wrote about this earlier in [Securing cookies with cookie prefixes](/2017/02/09/cookie-prefixes/). Cookies that start with `__Host-`, must conform to the following rules:

* Set by a HTTPS site.
* Marked as secure, so that they can only be read by a HTTPS site.
* Not be attached to a specific domain name. Confusingly, omitting a domain is more secure than specifying a domain.
* Have the path set to `/`, so that there's only one canonical cookie for the site.

If the application tries to set a `__Host-` cookie, but the cookie does not conform to these rules, the cookie is not set.

Cookie prefixes make the cookie more secure in several ways, but there's another indirect consequence of this: it turns a misconfiguration into an obvious functional problem.

Consider what happens when a developer accidentally removes the "Secure" attribute from the session cookie. Without cookie prefixes, the application keeps working as intended. The cookie is less secure, but no alarms go off. It will probably only be noticed at the next pentest.

With cookie prefixes, the application stops working immediately. Users can't log in anymore, since their browsers won't store the session cookie anymore. The bug that removed the "Secure" attribute from the cookie won't even make it to production.

This is an interesting secure-by-default feature, and makes it easy for developers to configure cookies securely. If it works at all, it is configured correctly.

## Extending secure by default

Can we use the same property in other cases? Is it possible to prevent security bugs by presenting security bugs as functional bugs?

### CSRF token verification

If forms use random tokens to protect against [CSRF](/2019/01/09/csrf/), the token should be verified when the form is posted. If this isn't done, everything keeps working but the application is not vulnerable to CSRF.

The framework could mark the form as dirty and prevent processing it until the CSRF verification marks it as clean. However, I don't think this is a big advantage over the framework just checking the CSRF token.

### Authorization checks

If you forgot the `[Authorize]` attribute on some method, it's publicly accessible. It keeps working functionally, so the developer won't notice the security problem.

The better solution is that it's not accessible at all. I think deny-by-default is the correct behavior in many cases, and it's a shame that not many frameworks come with it. It would make sense to have the authorization layer grant access instead of deny access. When that's misconfigured, the page isn't accessible to anyone and the developer will quicly notice.

### Disable MIME type sniffing

The [`X-Content-Type-Options: nosniff` header](/2023/03/29/mime-type-sniffing-x-content-type-options-nosniff/) makes the browser takes `Content-Type` headers more serious. This also functionally breaks pages that have an incorrect content type, or are missing a content type. Such bugs are pretty rare, but enabling this header is basically free and makes sure that these bugs are noticed early.

## Conclusion

Breaking application functionality when a security measure is missing causes security issues to be noticed quickly. It may be counterintuitive to turn a small problem into a large problem. But if you have any basic functionality testing in place, this will prevent the problem from reaching production without being noticed.
