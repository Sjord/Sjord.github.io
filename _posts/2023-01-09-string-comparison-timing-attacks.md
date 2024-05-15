---
layout: post
title: "String comparison timing attacks"
thumbnail: clocktower-480.jpg
date: 2023-03-15
---

<!-- Photo source: https://commons.wikimedia.org/wiki/File:Lier_Zimmertoren_klok.JPG -->

## Introduction

When an application compares two strings, this can take longer if the strings are more similar. The theory is that the strings are compared character by character. If the first character is different, the application can stop comparing immediately. If the first character is the same, subsequent characters are compared, and this takes longer. When comparing against a secret value, the time the comparison takes may give information on the content of the secret value.

However, this naively assumes that the comparison function compares one byte at a time, and bails out at the first difference. This is not always the case. Even if it is the case, it is not always detectable.

## Glibc strcmp

The [C implementation of strcmp in glibc](https://codebrowser.dev/glibc/glibc/string/strcmp.c.html) first compares bytes until hitting a word boundary, and then compares words. On my system, words are 8 bytes. Comparing 8 bytes at a time is much faster than comparing one byte at a time. Furthermore, it is more secure. To mount a timing attack, the attacker now has to guess all 8 bytes correctly to get a difference in timing.

For modern CPUs, glibc has strcmp variations for AVX2 and EVEX instruction sets, that can compare 32 bytes at a time.

In the below graph, you can see the time it takes strncmp and memcmp to compare two strings. The compared strings are equal for the first *x* characters, varied over the x-axis. As you can see, strncmp does have variable timing which can be used in a timing attack. However:

* The time varies in blocks of 8 bytes, so you would have to guess 8 bytes at a time.
* The time varies by less that a nanosecond, so you would have to detect less of a nanosecond in difference to exploit this.

<img src="/images/glibc-strncmp.svg" style="width: 100%">

## C#

The string comparison in C# can be smarter than in C, because it can take the current culture into account when comparing Unicode. To do a proper Unicode comparison, it has to do UTF8 parsing and Unicode normalization, which is much more expensive than just comparing bytes.

It is also possible to disable the "smart" comparison by specifying `StringComparison.Ordinal` as the comparison method. Then, the timing differences disappear.

So, a timing attack is possible, but:

* Only when doing a culture-sensitive string comparison.
* The time again varies by less than a nanosecond.

<img src="/images/csharp-stringcompare.svg" style="width: 100%">

## Python

I could show you the timing graph for Python, but it would look like a flat line. Strings that have a longer prefix in common do take longer to compare, but for the 40-byte string I used to test this with, that difference is too small to detect.

## Conclusion

Timing attacks on string comparison are sometimes possible. However, it is not as straightforward that any use of *strcmp* or == is vulnerable. Some implementations compare multiple bytes at a time, making it impossible to guess one byte at a time. The time differences for individual characters are often below one nanosecond, making it virtually impossible to detect remotely.

## Read more

* [Preventing Timing Attacks on String Comparison with a Double HMAC Strategy - Paragon Initiative Enterprises Blog](https://paragonie.com/blog/2015/11/preventing-timing-attacks-on-string-comparison-with-double-hmac-strategy)
* [It's All About Time | ircmaxell's Blog](https://blog.ircmaxell.com/2014/11/its-all-about-time.html)
* [Defeating memory comparison timing oracles - Red Hat Customer Portal](https://access.redhat.com/blogs/766093/posts/878863)
* [Timing Attack on SQL Queries Through Lobste.rs Password Reset - Dhole Moments](https://soatok.blog/2021/08/20/lobste-rs-password-reset-vulnerability/)
* [Should I use == for string comparison? (in Python)](https://stackoverflow.com/questions/67489572/should-i-use-for-string-comparison)
* [Sjord/timing-attack-benchmarks](https://github.com/Sjord/timing-attack-benchmarks)