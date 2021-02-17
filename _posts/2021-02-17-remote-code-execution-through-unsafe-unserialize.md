---
layout: post
title: "Remote code execution through unsafe unserialize in PHP"
thumbnail: cereal-480.jpg
date: 2021-03-03
---

<!-- photo source: https://pixabay.com/photos/cereal-spill-food-breakfast-3797538/ -->

* PRODUCTHISTORY cookie remembers last viewed products on webshop as a serialized PHP object. When viewing books on https://www.ebook.client.com/, the PRODUCTHISTORY cookie is unserialized to show the recently viewed books at the bottom of the page. Unserializing user input makes it possible for an attacker to construct PHP objects, call destructors with arbitrary data, and exploit vulnerabilities in PHP. This can lead to remote code execution.
* Two types of unserialize exploits: crashing the PHP executable, or running application destructor code. First tried attacking PHP. 
For example, requesting the URL https://www.ebook.client.com/product/decorating-interior gives no response at all with the following value for the PRODUCTHISTORY cookie: `O%3a8%3a"stdClass"%3a3%3a{s%3a3%3a"aaa"%3ba%3a5%3a{i%3a0%3bi%3a1%3bi%3a1%3bi%3a2%3bi%3a2%3bs%3a50%3a"AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA11111111111"%3bi%3a3%3bi%3a4%3bi%3a4%3bi%3a5%3b}s%3a3%3a"aaa"%3bi%3a1%3bs%3a3%3a"ccc"%3bR%3a5%3b}'%3b`. This possibly means that the server process has crashed, something which can be used in a denial-of-service attack, or can lead to exposing memory contents or remote code execution.
* Tried several payloads from phpggc. Monolog/RCE1 and RCE2 gives 500 internal server error.
* Errors because failing to copy/paste null characters. Created urlencode on command line to bypass this.
* Ran Monolog 1.19.0 locally and unserialized my payload to figure out what is going on.
* phpgcc creates objects that are normally hard to construct.
* Used `file_get_contents` against Burp collaborator server to prove RCE.

Original contents:

```
a%3A1%3A%7Bi%3A0%3Ba%3A6%3A%7Bs%3A5%3A%22image%22%3Bs%3A94%3A%22https%3A%2F%2Fwww.ebook.client.com%2Fcontents%2Ffullcontent%2F75103794%2Fcoverarts%2Fwizard%2Fsmall_75103794.jpg%22%3Bs%3A4%3A%22name%22%3Bs%3A27%3A%22decorating+with+interior%22%3Bs%3A3%3A%22url%22%3Bs%3A22%3A%22decorating-interior%22%3Bs%3A5%3A%22title%22%3Bs%3A27%3A%22decorating+with+interior%22%3Bs%3A8%3A%22subtitle%22%3Bs%3A0%3A%22%22%3Bs%3A9%3A%22productid%22%3Bs%3A2%3A%2217%22%3B%7D%7D
```
And decoded:

```
a:1:{i:0;a:6:{s:5:"image";s:94:"https://www.ebook.client.com/contents/fullcontent/75103794/coverarts/wizard/small_75103794.jpg";s:4:"name";s:27:"decorating with interior";s:3:"url";s:22:"decorating-interior";s:5:"title";O:8:"DateTime":3:{s:4:"date";s:26:"2020-06-30 13:21:09.871213";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";};s:8:"subtitle";s:0:"";s:9:"productid";s:2:"17";}}
```
