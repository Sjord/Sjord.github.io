---
layout: post
title: "Datatypes for pycrypto in Python 3"
thumbnail: hieroglyph-240.jpg
date: 2016-03-17
---

Say you want to encode some things in Python 3 using pycrypto, and you want full control over what is encrypted or decrypted. In that case you should use the `bytes` and `bytearray` types, not strings.

## Don't use strings

In Python 3 the string type natively supports unicode. The string stores characters, not bytes. Pycrypto, on the other hand, only works with bytes. You can pass strings to pycrypto, and it will convert them to bytes internally by encoding them as UTF-8.

    AES.new(key, AES.MODE_ECB).encrypt("hello world 1234")  # kinda works

There are two problems with this. First, `decrypt` does not return a string, making the `encrypt` and `decrypt` functions no longer inverses of each other. Secondly, the length in bytes is important for these functions:

    >>> AES.new(key, AES.MODE_ECB).encrypt("héllo world 1234")
    ValueError: Input strings must be a multiple of 16 in length

As you can see the string to encrypt is still 16 characters in length, but when UTF-8 encoded it is now 17 bytes.

This especially becomes a problem if you want to use pycrypto to [break crypto](https://cryptopals.com/sets/2/challenges/12) and manipulate the ciphertext or plaintext by trying out all possible combinations:

    for i in range(256):
        cipher = aes.encrypt(text + chr(i))  # Breaks when i == 128

## Use bytes

The `bytes` type is a lot like the string type:

* You can use byte literals like `b'hello world'` to create a bytes from a text.
* Many string functions work on bytes (`startswith`, `upper`, `index`, etc.)
* Bytes are immutable, just like strings:

~~~
>>> my_bytes[1] = b'X'  # error
TypeError: 'bytes' object does not support item assignment
~~~

They differ on some points:

* Bytes can only contain ASCII literal characters. `b'héllo'` will give a SyntaxError.
* Indexing works, but returns a number: 

~~~
>>> my_bytes[1]
108
~~~

You can convert between strings and bytes using the `encode` and `decode` functions.

## Mutable bytes with bytearray

The `bytes` array is immutable. This can be a problem when you want to tinker with the ciphertext to decrypt, for example in a padding oracle attack. There are two ways to work around this. First, you could create new bytes instances with the changed content. If we want to change the last byte:

    my_bytes = my_bytes[0:-1] + b'x'

Second, you could create a bytearray. A bytearray is a mutable version of the bytes type, and can be changed using indexing:

    my_bytearray = bytearray(my_bytes)
    my_bytearray[-1] = b'x'
    my_bytes = bytes(my_bytearray)

As you can see this takes some converting back and forth. As of yet the pycrypto library does not support bytearrays, so you have to convert them to bytes or you get this error:

    TypeError: argument must be read-only pinned buffer, not bytearray

## Conclusion

Using the `bytes` type gives full control over what is encrypted or decrypted when using pycrypto. A `bytearray` can be used to get an mutable version of `bytes`, but you still need to convert to `bytes` before passing it to pycrypto.
