---
layout: post
title: "Overwriting HttpOnly cookies using cookie jar overflow"
thumbnail: cookie-coffee-splash-480.jpg
date: 2020-05-20
---

<!-- photo source: https://pixabay.com/nl/photos/koffie-cup-splash-vloeistof-1973549/ -->

## HttpOnly cookies

Cookies are small pieces of data that the browser sends with each request. To mitigate the risk of cross-site scripting (XSS), cookies can be marked as HttpOnly. This way, the cookie value cannot be read from JavaScript. HttpOnly cookies are still sent from the browser to the server, but within the browser they are not readable by JavaScript.

## Cookie jar overflow

Browsers have a limit on how many cookies they store. How many cookies a domain can store varies between browsers, but is typically limited to a several hundred cookies. When more cookies are written, the oldest cookies are removed. By setting many cookies, an application can make old cookies be removed. 

## From JavaScript

This even works from JavaScript, and it also removes HttpOnly cookies. So by setting many cookies, it is possible for a script to remove HttpOnly cookies. After that, it is possible to set a new cookie with the same name that isn't HttpOnly, effectively overwriting the HttpOnly cookie.

## Conclusion

Even though HttpOnly provide some protection from JavaScript, it does not protect against removing or overwriting the cookie.


* [Browser Cookie Limits](http://browsercookielimits.squawky.net/)
* [Racing to downgrade users to cookie-less authentication](https://kuza55.blogspot.com/2008/02/racing-to-downgrade-users-to-cookie.html)
* [Cookie forcing](https://scarybeastsecurity.blogspot.com/2008/11/cookie-forcing.html)
