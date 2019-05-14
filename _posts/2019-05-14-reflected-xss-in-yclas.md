---
layout: post
title: "Reflected XSS in Yclas"
thumbnail: building-reflection-480.jpg
date: 2019-10-09
---

Yclas is a content management system for classified marketplaces. 

<!-- photo source: https://pixabay.com/photos/architecture-building-geometric-1868547/ -->

## Reflection without tags

Yclas has functionality to search advertisements. As usual, the search input is reflected in a couple of places. The page says "1 advertisement for searchterm", and the search term is put back in the same input field. If you search on "hello", the input field still contains "hello". The HTML looks like this:

    <input type="text" id="title" name="title" class="form-control"
           value="hello" placeholder="Title">

Such reflected values are opportunities for XSS. However, when I search on `<h1>hello`, it turns out that HTML tags are stripped from the input and the page just shows the results for `hello`.

<img src="/images/yclas-reflection.png" width="100%">

## XSS without tags

Even though HTML tags are removed, it is still possible to add JavaScript to the page. Quotes are not properly escaped, making it possible to add attributes to the input field. Searching on `" autofocus onfocus="alert(1)` triggers a popup box.


Our payload, `" autofocus onfocus="alert(1)`, is used verbatim in the value attribute of the input element. This makes the HTML look like this:

    <input type="text" id="title" name="title" class="form-control"
           value="" autofocus onfocus="alert(1)" placeholder="Title">

The `autofocus` attribute brings the focus to this element when the page is loaded. This immediately triggers the onfocus event handler which runs our JavaScript payload.

## Conclusion

I found reflected cross-site scripting by adding attributes to an input field. I [reported this](https://github.com/yclas/yclas/issues/2834) to a Yclas developer and he quickly fixed it.
