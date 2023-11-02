---
layout: post
title: "Securing HTML fragments"
thumbnail: html-fragment-480.jpg
date: 2023-11-08
---

<!-- Photo source: https://pixabay.com/photos/smartphone-paper-letter-write-pen-4905176/ -->

## Replacing parts of the page with HTML

Some web applications use JavaScript to replace part of the page with dynamic HTML. JavaScript performs a call to the backend, which returns a little piece of HTML. That HTML is then inserted in the correct place on the page, possibly replacing something else.

This pattern was all the rage around 2006, when it was possible to dynamically update parts of the page using [XMLHttpRequest](https://en.wikipedia.org/wiki/XMLHttpRequest). This was made easier by jQuery, with [jQuery.load](https://api.jquery.com/load/). It lost popularity for a while, but is now back as the backbone of [htmx](https://htmx.org/) and [Unpoly](https://unpoly.com/).

<img src="/images/html-fragment-pattern.svg" style="width: 100%">

## XML
