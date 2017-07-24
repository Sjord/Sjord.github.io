---
layout: post
title: "Some libraries evaluate remote JavaScript"
thumbnail: todo-240.jpg
date: 2017-12-20
---

## Introduction

Within JavaScript it is possible to do a HTTP request to retrieve a resource from an URL. Before the modern [fetch API](https://developer.mozilla.org/en/docs/Web/API/Fetch_API) existed this happened with [XMLHttpRequest](https://developer.mozilla.org/en/docs/Web/API/XMLHttpRequest), which is difficult to work with because of its cumbersome API that differs between browsers. Several JavaScript libraries offers convenience functions to make AJAX requests easier. If the response to the AJAX request contains JavaScript, some libraries will automatically run that JavaScript. This is a mistake, as it may introduce a security vulnerability if the URL is under control of an attacker.

## Attack scenario

Consider a site where it is possible to configure a profile picture. The URL of this picture can be provided by the user, and is then fetched using a AJAX request. The AJAX request is done using a JavaScript library that by default evaluates all responses with an `application/json` content type.

In this situation it is possible to exploit cross-site scripting (XSS). By supplying an URL to a JavaScript file, the code in this file is run in the context of the site.



* [CVE-2008-7220: Unspecified vulnerability in Prototype JavaScript framework](http://cve.mitre.org/cgi-bin/cvename.cgi?name=cve-2008-7220)
* [prototype: Prevent a potential security issue for cross-site ajax requests](https://github.com/sstephenson/prototype/commit/02cc9992e915c024650ddc77a91064f7a4252914)
* [#2432: Inadequate/dangerous jQuery behavior for 3rd party text/javascript responses](https://github.com/jquery/jquery/issues/2432)
* [mootools class Request.HTML](https://mootools.net/core/docs/1.6.0/Request/Request.HTML)
