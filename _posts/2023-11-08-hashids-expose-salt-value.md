---
layout: post
title: "HashIDs"
thumbnail: brick-wall-480.jpg
date: 2023-11-22
---

<!-- Photo source: https://pixabay.com/photos/wall-bricks-shadow-home-texture-1358958/ -->

Hashids is an algorithm that converts numbers to string tokens and back. It's similar to base64 encoding, but with extra steps. For example, it can convert `77305` to `NgDmz`.

## A history of security claims

Hashids has never been a secure algorithm, but it started out with security claims:

* 2012 - launches, claims it is suitable for "making pages private"
* 2013 - claims it is suitable for "forgotten password hashes", and that "There's not a way to guess the correct numbers behind the hash without knowing the salt value first."
* 2014 - Hashids retracts the security claims: "Do not encode sensitive data. ... This is not a true encryption algorithm."
* 2015 - [an attack](https://carnage.github.io/2015/08/cryptanalysis-of-hashids) is published to make brute forcing the salt easier. 
* 2023 - Hashids rebrands to [Sqids](https://sqids.org/), and removes the "salt" and "hash" terminology to avoid hinting at security.

## Unexplainably popular

In 2012, we already had AES, Blowfish, and [Bruce Schneier](https://www.schneier.com/blog/archives/2011/04/schneiers_law.html). "Don't roll your own crypto" was well-known advice, and back then "crypto" really did mean "cryptography". Against this background, a random developer creates Hashids, and it keeps gaining in popularity, even after the security claims are removed.

I don't understand it. Why would anyone want to use this base64 with extra steps, if it doesn't provide security? Is `NgDmz` so much better to use in a URL compared with `77305`? 

## Hashids may leak your secret key




## Read more

* [Carnage's tech talk - Cryptanalysis of hashids](https://carnage.github.io/2015/08/cryptanalysis-of-hashids)
