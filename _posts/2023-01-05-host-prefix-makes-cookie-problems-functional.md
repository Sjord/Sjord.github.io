---
layout: post
title: "Cookie prefixes turn security problems into functional problems"
thumbnail: monkey-with-cookie-480.jpg
date: 2023-03-01
---

<!-- photo source: https://pixabay.com/nl/photos/eekhoorn-aap-aap-%c3%a4ffchen-exotisch-1438533/ -->

If you give your cookie a special name, it gains certain security properties. I wrote about this earlier in [Securing cookies with cookie prefixes](/2017/02/09/cookie-prefixes/). Cookies that start with `__Host-`, must conform to the following rules:

* Set by a HTTPS site.
* Marked as secure, so that they can only be read by a HTTPS site.
* Not contain a domain. Confusingly, omitting a domain is more secure than specifying a domain.
* Have the path set to `/`, so that there's only one canonical cookie for the site.

If the application tries to set a `__Host-` cookie, but the cookie does not conform to these rules, the cookie is not set.

This is more secure in several ways, but I want to point out one specific consequence of this: it turns a misconfiguration into an obvious functional problem.

Consider what happens when a developer accidentally removes the "Secure" attribute from the session cookie. Without cookie prefixes, the application keeps working as intended. The cookie is less secure, but no alarms go off. It will probably only be noticed at the next pentest.

With cookie prefixes, the application stops working immediately. Users can't log in anymore, since their browsers won't store the session cookie anymore. The bug that removed the "Secure" attribute from the cookie won't even make it to production.


