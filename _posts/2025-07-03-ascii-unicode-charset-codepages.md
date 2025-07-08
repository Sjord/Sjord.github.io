---
layout: post
title: "Unicode and endianness"
thumbnail: lookup-list-480.jpg
date: 2025-08-06
---

Computers can only store bits and bytes. If you want to store numbers or characters, you have to know how to convert these to bits.

## Character encoding

Computers can only store bits and bytes.

To store numbers, you need a mapping which bits represent which numbers. When storing 3, do we store it as 11000000 or 00000011? What about -3? This is what [two's complement](https://en.wikipedia.org/wiki/Two%27s_complement) is, a mapping between numbers and bits.

To store characters (letters), you need a mapping which bits represent which characters. To make it a bit simpler, we can use the bit-to-number mapping we already have and create a mapping between numbers and characters. This is ASCII. ASCII specifies that "a" is stored as 61, which is stored as 00111101.

ASCII only specifies characters for the numbers 0 up to 127. Some people wanted more characters than that, and they came up with Unicode. Like ASCII, Unicode also maps characters to numbers. The first 127 numbers are the same as ASCII, but after that Unicode specifies more characters. For example, the phone emoji is mapped to number 128241.

However, bytes are limited in value between 0 and 255, so storing 128241 is not that easy. One way is to use four bytes (32 bits) for every character, which is UTF-32. Another solution is to start with one byte, and indicate whether another byte follows, which is UTF-8.

So both ASCII and Unicode map numbers to characters, so that everybody agrees how text is represented in bits and bytes. UTF-8 is a way to store Unicode numbers in bytes efficiently, even when using Unicode numbers that would normally not fit in a byte.

## Endianness

Above I implied that there is consensus on how numbers are stored in bits and bytes. This is not the case. People agree on how to store numbers lower than 256 in a byte. However, when storing numbers larger than 256, people don't agree on how to combine multiple bytes. When storing a number in multiple bytes, these bytes can be ordered from small to large, or the other way around. These option are called little endian and big endian.

Big endian makes sense because that is a logical way for people to write numbers. If you write 12345, the 1 at the front stands for 10000, which is the largest amount. Big endian is similar to this.

All desktop and laptop CPUs in the world use little endian. It may not make sense for people, but it makes sense for computers. Little endian definitely won in CPU architectures. Most network traffic is big endian, which means that if an application sends numbers over the network, they often have to be swapped around.

## Know the formatting of the data

If you read data from disk, memory, or the network, you have to know the format it is in. If you don't know whether a text file is ASCII, UTF-8 or UTF-32, you cannot reliably read it. Luckily, more and more systems default to UTF-8.

## Read more

* [Character encoding - Wikipedia](https://en.wikipedia.org/wiki/Character_encoding)
* [IEEE 754 - Wikipedia](https://en.wikipedia.org/wiki/IEEE_754)
* [Endianness - Wikipedia](https://en.wikipedia.org/wiki/Endianness)
* [UTF-8 - Wikipedia](https://en.wikipedia.org/wiki/UTF-8)