---
layout: post
title: "Working with bits and bytes in Python 2 and 3"
thumbnail: dipswitch-480.jpg
date: 2018-11-07
---

When performing a [bit flip](/2018/04/25/bitflip-effect-on-encryption-operation-modes/) attack or working with XOR encryption, you want to change the bits and bytes in a string of bytes. How this is done differs between Python 2 and 3, and this article explains how.

<!-- photo source: https://commons.wikimedia.org/wiki/File:Nedap_ESD1_-_printer_controller_-_DIP_switch-91833.jpg -->

## Introduction

In a bit flip attack, you typically want to change a single bit in a predefined message. As an example we take the message "attack at dawn". Imagine we want to change the least significant bit of a letter, resulting for example in "att`ck at dawn".

To flip a bit we will XOR it with 1. The XOR operation is done using the `^` hat.

    >>> 123 ^ 1
    122

## Common to Python 2 and 3

### Finding parts of the string

We can refer to a specific letter in the message by using string indexing. Similarly, we can refer to part of the payload by using the slice notation:

    >>> message[3]
    'a'
    >>> message[0:3]
    'att'

This is useful when we want to change only a single letter, or copy part of the string unchanged.

### Converting to binary

We can only flip bits on binary content. If we were passed a text string instead, we must first convert the text message to bytes. This is done using the `encode` function, which takes as parameter an encoding to use to convert the text to bytes:

    >>> message = u"Thanks for the tête-à-tête about our coup d'état in Zaïre"
    >>> message.encode("utf-8")
    b"Thanks for the t\xc3\xaate-\xc3\xa0-t\xc3\xaate. Let's hope that El Ni\xc3\xb1o doesn't disturb our coup d'\xc3\xa9tat in Za\xc3\xafre"

As you can see our letters have been converted to bytes according to the UTF-8 encoding.

Alternatively, we can provide the message in bytes to begin with. By putting a little `b` before our string literal we specify that it is a byte string as opposed to a text string:

    >>> message = b"Attack at dawn"

To easily get binary data in and out of Python you can use [base64.b64encode](https://docs.python.org/3/library/base64.html) to base64-encode it, or [binascii.hexlify](https://docs.python.org/3/library/binascii.html#binascii.hexlify) to convert it to hex.

### Mutable and immutable types

The string and bytes types are immutable.

    >>> message = "attack at dawn"
    >>> message[3] = "x"
    Traceback (most recent call last):
      File "<stdin>", line 1, in <module>
    TypeError: 'str' object does not support item assignment

We can't simply assign one different letter to the message, since it is immutable. Immutable objects can't be changed. There are two ways to overcome this problem:

* Create a new string containing the value we want.
* Copy the string to another mutable object and work on that.

To create a new string, we simply copy the parts we want to keep and inject our changed letter into it:

    >>> message = "attack at dawn"
    >>> message[:3] + "x" + message[4:]
    'attxck at dawn'

Alternatively we can use bytearray as our mutable object. We copy our message into a bytearray and change that:

    >>> message = "attack at dawn"
    >>> message_array = bytearray(message)
    >>> message_array[3] = "x"
    >>> str(message_array)
    'attxck at dawn'

Now, this example will only work in Python 2 and not in Python 3. Let's get into the differences.

## Python 2

The `str` type in Python 2 is a string of bytes. If you index it you get another `str` containing just one byte:

    >>> message = b"attack at dawn"
    >>> message[3]
    'a'

We can't just flip bits in this single-byte string. We need to convert it to a number and back again using [chr](https://docs.python.org/2/library/functions.html#chr) and [ord](https://docs.python.org/2/library/functions.html#ord). The `ord` (for "ordinal") function converts the letter to a number. We can modify that number as we please and convert it back using `chr`:

    >>> ord(message[3])
    97
    >>> chr(97)
    'a'
    >>> chr(ord(message[3]) ^ 1)
    '`'

## Python 3

In Python 3, `str` is the type for a string of text and `bytes` is the type for a string of bytes. If you index a `bytes` you get a number:

    >>> message = b"attack at dawn"
    >>> message[3]
    97

After we modify the number we want to put it back in our message. We have to convert it to bytes again. In Python 2 we used `chr` for this, but this won't work in Python 3: it will convert the number to a string instead of a byte. We will use the bytes constructor instead:

    >>> bytes([97])
    b'a'



## Which one to choose?

## Conclusion

## Read more

* [Bit Twiddling Hacks](https://graphics.stanford.edu/~seander/bithacks.html)
