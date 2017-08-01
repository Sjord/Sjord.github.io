---
layout: post
title: "Some libraries evaluate remote JavaScript"
thumbnail: card-puncher-240.jpg
date: 2017-09-27
---

## Introduction

Within JavaScript it is possible to do a HTTP request to retrieve a resource from an URL. Before the modern [fetch API](https://developer.mozilla.org/en/docs/Web/API/Fetch_API) existed this happened with [XMLHttpRequest](https://developer.mozilla.org/en/docs/Web/API/XMLHttpRequest), which is difficult to work with because of its cumbersome API that differs between browsers. Several JavaScript libraries offers convenience functions to make AJAX requests easier. If the response to the AJAX request contains JavaScript, some libraries will automatically run that JavaScript. This is a mistake, as it may introduce a security vulnerability if the URL is under control of an attacker.

## Attack scenario

Consider a site where it is possible to configure a profile picture. The URL of this picture can be provided by the user, and is then fetched using a AJAX request. The AJAX request is done using a JavaScript library that by default evaluates all responses with an `application/json` content type.

In this situation it is possible to exploit cross-site scripting (XSS). By supplying an URL to a JavaScript file, the code in this file is run in the context of the site.

## CORS changes same-origin policy

In order to exploit this we need to trigger a AJAX request to a JavaScript file that is under the attacker's control. The easiest way to do this is for the attacker to host this script on his own domain. However, the same-origin policy dictates that you can't fetch data from another domain, so this wasn't much of a risk.

With the introduction of [CORS](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS) it is possible to selectively disable the same-origin policy. This opt-in policy only allows whitelisted domains to do cross-origin requests. However, the attacker's site with his malicious script can opt-in and allow a cross-site request from the site doing the AJAX request. This means that the same-origin policy no longer protects against cross-site script execution.

## PrototypeJS same-origin mitigation

PrototypeJS was one of the libraries that evaluated any JavaScript returned in AJAX responses. If the content-type of the response was application/json, it would be evaluated. This changed in 2008 in Prototype 1.6.0.2:

> Among the numerous bug fixes is a change to the way Ajax.Request handles automatic JavaScript response evaluation. Previous versions of Prototype relied on the browser’s XMLHttpRequest same-origin policy to ensure that response bodies with a content type of text/javascript were safe to evaluate. Alexey Feldgendler from Opera kindly alerted us to the possibility that certain non-browser environments (like Opera’s widget system) do not enforce the same-origin policy and as such may be subject to cross-domain script exploits. To combat this we’ve added an Ajax.Request#isSameOrigin method which returns true when a request is being made to the same domain, port, and protocol as the document. Furthermore, Prototype will no longer automatically evaulate JavaScript response bodies when this method returns false.

## PrototypeJS open redirect trick

When doing an AJAX request, PrototypeJS checks the requested URL against the current URL. If they have the same scheme, domain and port it considers it as same-origin and will evaluate returned JavaScript. This introduces a vulnerability if the site has an open redirect. In that case the same-origin open redirect page can redirect the request to the attacker's script. The same-origin check will still succeed, since the open redirect is on the same domain as the application doing the request, and the attacker can host his script on his own domain.

## Read more

* [CVE-2008-7220: Unspecified vulnerability in Prototype JavaScript framework](http://cve.mitre.org/cgi-bin/cvename.cgi?name=cve-2008-7220)
* [Prototype 1.6.0.2: Bug fixes, performance improvements, and security](http://web.archive.org/web/20080128133717/http://prototypejs.org/2008/1/25/prototype-1-6-0-2-bug-fixes-performance-improvements-and-security)
* [prototype: Prevent a potential security issue for cross-site ajax requests](https://github.com/sstephenson/prototype/commit/02cc9992e915c024650ddc77a91064f7a4252914)
* [#2432: Inadequate/dangerous jQuery behavior for 3rd party text/javascript responses](https://github.com/jquery/jquery/issues/2432)
* [mootools class Request.HTML](https://mootools.net/core/docs/1.6.0/Request/Request.HTML)
