---
layout: post
title: "File and post data confusion in PHP"
thumbnail: phone-boy-480.jpg
date: 2026-08-19
---

PHP has several superglobal variables which contain values from the request or the environment. These differ in whether they contain trustworthy data or not:

<!-- Photo source: https://pixabay.com/photos/boy-old-phone-apparatus-speak-7701574/ -->

* $_GET, $_POST, $_COOKIE, $_REQUEST only contain user input.
* $_SERVER, $_FILES contain both user input and trusted data set by the webserver or PHP.
* $_SESSION only contains data put there by the application.

If the application mixes user input with trusted data, it is going to have a hard time determining whether a variable can be trusted or not.

One example is when user input (from $_GET, $_POST, etc) is written directly to the session ($_SESSION). Since the application typically trusts all data in the session, being able to write directly to the session can often bypass security controls. I wrote earlier about session poisoning in [ZenCart](/2024/02/06/session-poisoning-zen-cart/) and [ZoneMinder](/2019/09/25/second-order-sql-injection-in-zoneminder/).

Another example is when user input is mixed up with $_FILES. The $_FILES array contains mostly untrusted user input, but it contains one value set by PHP; `tmp_name` contains the path to the file that was just uploaded. Presumably, the application is going to read this or save it somewhere. Making it possible to write to `$_FILES` makes it possible to set `tmp_name`, and thus make the application read any file on the file system.

An example of this is [CVE-2021-47667 in ZendTo](https://projectblack.io/blog/zendto-nday-vulnerabilities/). There, `$_FILES[tmp_name]` can be overwritten by `$_POST[tmp_name]`. The file name is later used in a shell command, making arbitrary remote code execution possible by including the `tmp_name` variable in the POST data.

## Merging post and file data

To avoid tainting the trusted `tmp_name` file variable, it is important to obtain it only from `$_FILES` and not from any other source. It is somewhat common for applications to merge `$_FILES` and `$_POST` into a single array and do further processing on that combined array. As mentioned, this makes `tmp_name` no longer trustworthy.

This type of vulnerability is common in Laminas. The example code on the [file upload input docs](https://docs.laminas.dev/laminas-inputfilter/v2/file-input/):

```php
// Merge $_POST and $_FILES data together
$request  = new Request();
$postData = array_merge_recursive(
    $request->getPost()->toArray(),
    $request->getFiles()->toArray()
);
```

The module `laminas-mvc-plugin-fileprg` also [merges](https://github.com/laminas/laminas-mvc-plugin-fileprg/blob/2006988b16b001d0e113afa95a17f64a6e5a1f11/src/FilePostRedirectGet.php#L63-L65) the post and file data:

```php
$postFiles = $request->getFiles()->toArray();
$postOther = $request->getPost()->toArray();
$post      = ArrayUtils::merge($postOther, $postFiles, true);
```

## Omeka S

Given that this vulnerability is common in Laminas, I thought it would be easy to find a web application that is vulnerable to this. Unfortunately, it turned out harder than expected. I found one that is somewhat vulnerable: Omeka S, a web application that manages exhibit items.

Omeka S has functionality to import RFC vocabularies. The code merges the post and files input and passes the `tmp_name` as the file to import:

```php
$post = array_merge_recursive(
    $request->getPost()->toArray(),
    $request->getFiles()->toArray()
);
$form->setData($post);
if ($form->isValid()) {
    $data = $form->getData();
    ...
    $options['file'] = $data['vocabulary-file']['file']['tmp_name'];
    ...
    $response = $this->rdfImporter->import($strategy, $data['vocabulary-info'], $options);
```

Normally the browser submits a file in a POST field names `vocabulary-file[file]`. PHP then [fills](https://www.php.net/manual/en/features.file-upload.post-method.php) `$_FILES[vocabulary-file][file][name]`, `$_FILES[vocabulary-file][file][tmp_name]`, etc. To abuse this, instead of posting `vocabulary-file[file]` we post `vocabulary-file[file][tmp_name]` ourselves.

Interestingly, posting just `tmp_name` produces an error:

> Laminas\Validator\Exception\InvalidArgumentException: Value array must be in $_FILES format

The application does have validation on the file input, but that validation is already performed on the merged data. So this error disappears when also posting `name` and `error`:

```
Content-Disposition: form-data; name="vocabulary-file[file][tmp_name]"

/etc/passwd
------WebKitFormBoundaryFQ9VBSKiuQRbMpbC
Content-Disposition: form-data; name="vocabulary-file[file][name]"

passwd
------WebKitFormBoundaryFQ9VBSKiuQRbMpbC
Content-Disposition: form-data; name="vocabulary-file[file][error]"

0
```

Unfortunately, it isn't possible here to read arbitrary files; it is only possible to check whether they exist. An existing file shows 

> Could not parse vocabulary file

Whereas a non-existing file shows

> Could not read vocabulary file.

Which clearly shows that the application considers files indicated in `tmp_name`, instead of files uploaded by the user. The application checks the file name with `is_readable`, which prevents using filter chains. Otherwise, it would be possible to shape files as valid RDF data, or use an error based side channel.

## Conclusion

Merging post and file data can lead to dangerous vulnerabilities. Typically it makes it possible to read arbitrary files on the server. In ZendTo it even led to remote code execution.

## Read more

* [File Upload Input - laminas-inputfilter - Laminas Docs](https://docs.laminas.dev/laminas-inputfilter/v2/file-input/)
* [File Uploads - laminas-form - Laminas Docs](https://docs.laminas.dev/laminas-form/v3/file-upload/)
* [laminas-mvc-plugin-fileprg/src/FilePostRedirectGet.php at 2006988b16b001d0e113afa95a17f64a6e5a1f11 · laminas/laminas-mvc-plugin-fileprg](https://github.com/laminas/laminas-mvc-plugin-fileprg/blob/2006988b16b001d0e113afa95a17f64a6e5a1f11/src/FilePostRedirectGet.php#L63-L65)
* [ZendTo NDay Vulnerability Hunting - Unauthenticated RCE in v5.24-3 <= v6.10-4](https://projectblack.io/blog/zendto-nday-vulnerabilities/)
* [Session poisoning Zen Cart for a free discount](https://www.securify.nl/en/blog/session-poisoning-zen-cart-for-a-free-discount/)
* [api-tools-content-validation/src/ContentValidationListener.php at 1.13.x · laminas-api-tools/api-tools-content-validation](https://github.com/laminas-api-tools/api-tools-content-validation/blob/1.13.x/src/ContentValidationListener.php#L219)
