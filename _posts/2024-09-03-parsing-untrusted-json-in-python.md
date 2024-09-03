---
layout: post
title: "Parsing untrusted JSON in Python is not a security problem"
thumbnail: snake-480.jpg
date: 2024-09-18
---

The Python documentation warns against parsing long untrusted JSON documents, but this does not seem to be dangerous in practice.

<!-- Photo source: https://pixabay.com/photos/red-legged-seriema-bird-snake-6655968/ -->

## Warning

The [Python JSON documentation](https://docs.python.org/3/library/json.html) features a red warning about parsing untrusted user input:

> <b>Warning:</b> Be cautious when parsing JSON data from untrusted sources. A malicious JSON string may cause the decoder to consume considerable CPU and memory resources. Limiting the size of data to be parsed is recommended.

Can parsing JSON really lead to a denial-of-service condition?

## CVE-2020-10735: DoS because of large integers

Why was this warning added? Clicking "Show Source" under "This Page" takes us to the documentation's source code. Doing a Git blame on it shows [the commit](https://github.com/python/cpython/commit/511ca94) that added the warning. This reveals it was added as a result of CVE-2020-10735.

CVE-2020-10735 is a vulnerability about how it takes a long time to parse long integers. Python supports integers of arbitrary length. When parsing JSON, the integer is supplied as a string of numbers. This string needs to be converted to an integer datatype, and that takes a long time. The CVE description gives an indication of how long parsing takes:

> a system could take 50ms to parse an int string with 100,000 digits and 5s for 1,000,000 digits

So with a few MB of JSON data you could keep a server very busy. It is not obvious to me whether this would be usable for a practical denial-of-service attack, but it would certainly be costly and annoying.

## Two solutions

Python did a few things to fix this issue:

- They added a [length limitation](https://docs.python.org/3/library/stdtypes.html#int-max-str-digits) on integers, by default set to 4300 characters, in Python 3.11.
- They improved the performance of the algorithm to convert strings to integers, in Python 3.12.

It seems like either of these correctly solve the issue. So if you have Python 3.12, this vector of attack is definitely solved, and you can safely parse any JSON.

This bug did expose an attack scenario where an overly long JSON takes up server resources when parsing, so the warning may still be valid for situations other than the long integers described in CVE-2020-10735. However, JSON can be parsed fast and I would argue that if it doesn't, it is a bug in your framework, not in your application.

## Conclusion

Long JSON input does not result in a denial-of-service situation. JSON can be parsed sufficiently quickly. If parsing is slow, that is a bug in the framework. Python did have such as bug, but it was fixed in Python 3.11. The warning in the documentation seems to be overly cautious.

## Read more

- [gh-95778: CVE-2020-10735: Prevent DoS by very large int() (#96499) · python/cpython@511ca94](https://github.com/python/cpython/commit/511ca94)
- [CVE-2020-10735: Prevent DoS by large int<->str conversions · Issue #95778 · python/cpython](https://github.com/python/cpython/issues/95778)
- [gh-95778: CVE-2020-10735: Prevent DoS by very large int() by gpshead · Pull Request #96499 · python/cpython](https://github.com/python/cpython/pull/96499)
- [Quadratic time internal base conversions · Issue #90716 · python/cpython](https://github.com/python/cpython/issues/90716)
- [FAQ for CVE-2020-10735 · Issue #96834 · python/cpython](https://github.com/python/cpython/issues/96834)
- [gh-90716: add \_pylong.py module by nascheme · Pull Request #96673 · python/cpython](https://github.com/python/cpython/pull/96673)