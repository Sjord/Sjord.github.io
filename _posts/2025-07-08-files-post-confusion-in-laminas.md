---
layout: post
title: "File and post data confusion in Laminas"
thumbnail: phone-boy-480.jpg
date: 2025-08-20
---

PHP has several superglobal variables which contain values from the request or the environment. These differ in whether they contain trustworthy data or not:

<!-- Photo source: https://pixabay.com/photos/boy-old-phone-apparatus-speak-7701574/ -->

* $_GET, $_POST, $_COOKIE, $_REQUEST only contain user input.
* $_SERVER, $_FILES contain both user input and trusted data set by the webserver or PHP.
* $_SESSION only contains data put there by the application.

If the application mixes user input with trusted data, it is going to have a hard time to determine whether a variable can be trusted or not.

One example is when user input (from $_GET, $_POST, etc) is written directly to the session ($_SESSION). Since the application typically trusts all data in the session, being able to write directly the session can often bypass security controls. I wrote earlier about an example in [ZenCart](https://www.securify.nl/en/blog/session-poisoning-zen-cart-for-a-free-discount/).

Another example is when user input is mixed up with $_FILES. The $_FILES array contains mostly untrusted user input, but it contains one value set by PHP; `tmp_name` contains the path to the file that was just uploaded. Presumably, the application is going to read this or save it somewhere. Making it possible to write to `$_FILES` makes it possible to set `tmp_name`, and thus make the application read any file on the file system.

An example of this is CVE-2021-47667 in ZendTo. There, `$_FILES[tmp_name]` can be overwritten by `$_POST[tmp_name]`, so by including `tmp_name` as a POST parameter.

This type of vulnerability is also common in Laminas. For example, the example code on the [file upload input docs](https://docs.laminas.dev/laminas-inputfilter/v2/file-input/):

```
// Merge $_POST and $_FILES data together
$request  = new Request();
$postData = array_merge_recursive(
    $request->getPost()->toArray(),
    $request->getFiles()->toArray()
);
```

When using `$postData` after this, it is no longer clear whether `tmp_name` came from the trusted `$_FILES` or the untrusted `$_POST`.

If you don't want to use the example code, you can use the module laminas-mvc-plugin-fileprg, which does [the merging](https://github.com/laminas/laminas-mvc-plugin-fileprg/blob/2006988b16b001d0e113afa95a17f64a6e5a1f11/src/FilePostRedirectGet.php#L63-L65) for you.

## Read more

* [File Upload Input - laminas-inputfilter - Laminas Docs](https://docs.laminas.dev/laminas-inputfilter/v2/file-input/)
* [File Uploads - laminas-form - Laminas Docs](https://docs.laminas.dev/laminas-form/v3/file-upload/)
* [laminas-mvc-plugin-fileprg/src/FilePostRedirectGet.php at 2006988b16b001d0e113afa95a17f64a6e5a1f11 · laminas/laminas-mvc-plugin-fileprg](https://github.com/laminas/laminas-mvc-plugin-fileprg/blob/2006988b16b001d0e113afa95a17f64a6e5a1f11/src/FilePostRedirectGet.php#L63-L65)
* [ZendTo NDay Vulnerability Hunting - Unauthenticated RCE in v5.24-3 <= v6.10-4](https://projectblack.io/blog/zendto-nday-vulnerabilities/)
* [Session poisoning Zen Cart for a free discount](https://www.securify.nl/en/blog/session-poisoning-zen-cart-for-a-free-discount/)
