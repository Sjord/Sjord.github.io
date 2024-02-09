---
layout: post
title: "curl facilitates header injection"
thumbnail: curl-man-480.jpg
date: 2024-02-28
---

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

In HTTP/1, headers are sent just as shown above, each header on a new line. In this protocol, it is not possible for a header to contain a newline character. Instead, a new line would start a new header. This can make an application vulnerable to header injection. Consider the following example, where an application performs a request to another system.

```php
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Request-ID: " . $_POST['id']);
```

If `$_POST['id']` contains multiple lines, these will be added as additional request headers. This makes it possible to manipulate the request the application sends.

## HTTP/2

This all makes sense for HTTP/1, because it is a text-based protocol where headers are separated by newlines. However, HTTP/2 and 3 are binary protocols, and headers are no longer delimited by newlines. In HTTP/2, it is technically possible to include a newline in a header. However, [RFC 9113](https://www.rfc-editor.org/rfc/rfc9113.html#name-http-fields) says that servers shouldn't allow this. Indeed, the Apache web server will terminate the connection if you try this.

So if the web server doesn't allow newlines in headers, adding a header with user content won't be vulnerable to header injection, right? Wrong.

## Curl splits headers

If a header contains a newline, curl already splits it into multiple headers before sending. This formatting that curl performs facilitates the header injection, even when using newer protocols such as HTTP/2.



## Summary:

Using `\r\n` in a request header causes curl to sent two request headers. This can lead to a security issue if an application uses user input in a request header.

## Steps To Reproduce:

test.php contains `var_dump($_SERVER);`.

```
$ curl --insecure --header $'Hello: world\r\nAnother: header' https://localhost:8424/test.php
...
  ["SERVER_PROTOCOL"]=>
  string(8) "HTTP/2.0"
  ["HTTP_ANOTHER"]=>
  string(6) "header"
  ["HTTP_HELLO"]=>
  string(5) "world"
```

## Supporting Material/References:

With HTTP/1 this type of injection makes sense, but this also works for HTTP/2. I would expect curl to either raise an error, or literally sent `world\r\nAnother: header` as the content of the `Hello` header. Sending newlines in HTTP/2 headers is possible, but not allowed according to RFC 9113.

## Impact

If an application uses user input in a request header, this behavior makes it possible for the user to inject an additional request header.
