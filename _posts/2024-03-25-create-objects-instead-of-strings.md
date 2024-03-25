---
layout: post
title: "Create objects instead of strings"
thumbnail: construction-blocks-480.jpg
date: 2024-03-27
---

## Introduction

Wherever a string has some structure to it, it can be vulnerable to injection. When creating CSV, JSON, HTML, URL, or some other structured text, just putting a variable in it without encoding or escaping it can break the format, and result in a security issue. The naive way to solve this is to escape the variable. The better way is to create an object, and convert that to text.

## URL example

You can create a URL like this:

```javascript
const url = 'https://example.org/?error=' + errorMsg;
```

This goes wrong if `errorMsg` contains characters that have special meaning in a URL. The most obvious example here would be an ampersand, `&`, which can be used to add another parameter.

This can be solved by using escaping:

```javascript
const url = 'https://example.org/?error=' + encodeURIComponent(errorMsg);
```

There are a few disadvantages with this:
* you have to use the correct escaping method.
* you have to remember to do it every time.

Another method is to use the URL API:

```javascript
const url = new URL('https://example.org/')
url.searchParams.append('error', errorMsg);
```

By creating an object using an API, all values are automatically encoded.

## Conclusion

Using the correct data types can help in securing both input and output values. By constructing objects instead of concatenating strings, injection is made impossible.

## Read more

* [Parse, don’t validate](https://lexi-lambda.github.io/blog/2019/11/05/parse-don-t-validate/)