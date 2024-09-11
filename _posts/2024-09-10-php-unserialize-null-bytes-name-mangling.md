---
layout: post
title: "Removing and encoding null bytes to exploit unserialize over SOAP"
thumbnail: nixie-zero-480.jpg
date: 2024-10-16
---

Serialized objects often contain null bytes. SOAP strings cannot contain null bytes. However, this article shows two ways to transmit serialized objects over SOAP, to be able to exploit an unserialize RCE.

## Introduction

In PHP, [serialize](https://www.php.net/serialize) converts a data structure to a string, and [unserialize](https://www.php.net/unserialize) converts a string back into a data structure. Passing untrusted user input to unserialize is a [security vulnerability](/2021/04/04/remote-code-execution-through-unsafe-unserialize/), because it makes it possible to create arbitrary objects, which can result in remote code execution when these objects are cleaned up and their destructors are executed.

A serialized object often contains null-bytes. This is unfortunate for developers, as null-bytes are hard to store and transmit. It is unfortunate for attackers for the same reason, making it harder to exploit an unserialize RCE.

Recently I came across a SOAP server that exposed a method that unserialized its input. Unfortunately, it is not possible to include a null byte inside a string in a SOAP request, so exploiting this was not as straightforward. Fortunately, I found two workarounds.

## Removing null bytes from the payload

Serialized objects often contain null bytes, because of name mangling. A private property is only visible to that class, not to its children. An inheriting class can create a property with the same name, but this is actually a different property.

In the example below, `$prop` in class A and `$prop` in class B refer to different variables:

```php
<?php
class A {
    private $prop = "A";

    public function showAProp() {
        var_dump($this->prop);
    }
}

class B extends A {
    protected $prop = "B";

    public function showBProp() {
        var_dump($this->prop);
    }
}

$b = new B();
$b->showAProp(); // string(1) "A"
$b->showBProp(); // string(1) "B"
```

To keep these apart, PHP internally prepends the class name to the property. To avoid clashing with `$Aprop`, it surrounds the class name with a null byte, so that it becomes `␀A␀prop`. Protected properties become `␀*␀prop`. Public properties are not mangled and don't contain null bytes.

So if we cannot use null bytes, we can only set public properties. It turns out this is sufficient to trigger an unserialize RCE. When serializing, we declare all properties as public. When unserializing, PHP sees that these public properties actually match with protected properties and correctly assigns to the protected properties.

So we can have an RCE payload without null bytes by simply changing "protected" to "public" in the [gadgets.php](https://github.com/ambionics/phpggc/blob/master/gadgetchains/Monolog/RCE/1/gadgets.php) file. Off course, this is a total hack. It doesn't work in PHP versions before 7.2.

## Transmitting base64-encoded payloads

It's not possible to include a null byte in a string in SOAP. Even encoding it with `&x0000;` does not work. However, SOAP also has support for binary data types: base64Binary and hexBinary. 

A string parameter looks like this:

```
<param0 xsi:type="xsd:string">foo</param0>
```

We can provide hexadecimal encoded binary data with:

```
<param0 xsi:type="xsd:hexBinary">666f6f</param0>
```

And base64 encoded binary data with:

```
<param0 xsi:type="xsd:base64Binary">Zm9v</param0>
```

In some other languages such as Java, this wouldn't work because binary data is stored in a byte array, not in a string. However, in PHP both binary data and text is stored in the same data type, so we can pass binary data to a function that takes a string as input.

## Conclusion

It's both possible to reliably encode null bytes in SOAP, and to create an unserialize RCE payload without null bytes. That SOAP does not support null bytes in string does not pose a problem to exploit an unserialize vulnerability.

## Read more

* [Remote code execution through unsafe unserialize in PHP](/2021/04/04/remote-code-execution-through-unsafe-unserialize/)
* [Serialization — PHP Internals Book](https://www.phpinternalsbook.com/php5/classes_objects/serialization.html)
* [Internal structures and implementation, property name mangling](https://www.phpinternalsbook.com/php5/classes_objects/internal_structures_and_implementation.html#property-name-mangling)
* [ambionics/phpggc: PHPGGC is a library of PHP unserialize() payloads along with a tool to generate them, from command line or programmatically.](https://github.com/ambionics/phpggc)