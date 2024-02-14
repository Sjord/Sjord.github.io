---
layout: post
title: "Curl facilitates header injection"
thumbnail: curl-man-480.jpg
date: 2024-02-28
---

Header injection is possible by adding newlines in a header. This makes sense for HTTP/1, but HTTP/2 headers can technically contain newlines. When using curl, however, header injection is still possible even with HTTP/2.

<!-- Photo source: https://pixabay.com/photos/man-portrait-homeless-poverty-male-1870016/ -->

## HTTP headers

A typical HTTP request looks as follows:

```
GET / HTTP/1.1
Host: localhost
Accept: text/html
```

Besides sending a method, path and version, the request contains several HTTP headers; key-value pairs that specify additional properties of a request.

## Header injection

In HTTP/1, headers are sent just as shown above, each header on a new line, separated by `\r\n` (carriage return and linefeed). In this protocol, it is not possible for a header to contain a newline character. Instead, a new line would start a new header. This can make an application vulnerable to header injection. Consider the following example, where an application performs a request to another system.

```php
$ch = curl_init($url);
curl_setopt($ch,
    CURLOPT_HTTPHEADER,
    ["Request-ID: " . $_POST['id']);
```

If `$_POST['id']` contains multiple lines, these will be added as additional request headers. This makes it possible to manipulate the request the application sends.

## HTTP/2

This all makes sense for HTTP/1, because it is a text-based protocol where headers are separated by newlines. However, HTTP/2 and 3 are binary protocols, and headers are no longer delimited by newlines. In HTTP/2, it is technically possible to include a newline in a header. However, [RFC 9113](https://www.rfc-editor.org/rfc/rfc9113.html#name-http-fields) says that servers shouldn't allow this. Indeed, the Apache web server will terminate the connection if you try this.

So if the web server doesn't allow newlines in headers, adding a header with user content won't be vulnerable to header injection, right? Wrong.

## Curl splits headers

If a header contains a newline, curl already splits it into multiple headers before sending. Even when sending a HTTP/2 request, curl first constructs a HTTP/1 request. This is then parsed again and converted into a HTTP/2 request. Because the request is formatted in HTTP/1 first, header injection is possible, even when it is later converted to HTTP/2.

## API abstraction

Libraries and APIs should abstract away implementation details, both to make programming easier and for security. As a programmer, you want to be able to call `Memcache::add($foo, $bar)` no matter what `$foo` and `$bar` contain. You don't want to worry about the protocol that memcache uses internally, and that they key cannot contain a newline but the value can. The API abstracts that away.

I feel like curl fails to abstract the protocol details and even the implementation details of curl in this case. I want to set a header with some content, and I shouldn't have to worry about whether a newline in there interferes with the underlying protocol or intermediate representation.

## Conclusion

HTTP/2 headers can technically contain newlines, but they shouldn't. Curl cannot send a header with a newline in it. Instead, it will split it into separate headers.

So having user input in a request header makes it possible for an attacker to add additional headers to a request, and using HTTP/2 does not offer protection against that.
