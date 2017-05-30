---
layout: post
title: "Attacks on the Host header"
thumbnail: todo-240.jpg
date: 2017-09-13
---

If a website works even if the host header is missing or incorrect in the request, it may be vulnerable to several kinds of attacks. This post explains the implications of ignoring the host header.

## Host header introduction

When a browser performs a request to a web server, it sends along a Host header with the requested domain as its value. This makes it possible to run multiple websites, or "virtual hosts", on one machine, by serving different content dependant on the value of the host header.

Normally the host header contains the domain name of the requested web site. But what if it is missing, or has a value that does not correspond to a site hosted on the server? Then the server can either return an error message, or serve the default web site. In the last case, if the web site works correctly even if the host header is missing or incorrect, the server exposes itself to several attacks.

## Trusting the server name

## DNS rebinding attack




## Read more

* [WordPress Potential Unauthorized Password Reset](https://exploitbox.io/vuln/WordPress-Exploit-4-7-Unauth-Password-Reset-0day-CVE-2017-8295.html)
