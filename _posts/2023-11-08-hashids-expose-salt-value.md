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

## How Hashids work

Hashids take the following parameters:

- salt; a semi-secret key that changes the mapping between numbers and tokens
- minimum length; tokens shorter than this are padded
- alphabet; a sequence of characters, by default a-zA-Z0-9

Hashids works approximately like this:
The alphabet is shuffled several times when encoding a value. The value of salt determines the shuffle. Besides some marker and separator characters, the number input is basically base64 encoded using the shuffled alphabet.

More precisely, with the default alphabet, it works like this:

1. separators ("cfhistu") are removed from the alphabet, reducing the size of the alphabet to 48
2. the alphabet is reordered depending on the salt
3. the first four characters (guards) are removed from the alphabet, reducing the size to 44
4. a single character, depending on the input number, is added to the output
5. the alphabet is reordered depending on the character from the previous step, the salt and the alphabet
6. the number is base 44 encoded using the alphabet and added to the output

## Hashids may leak your secret key

Hashids are insecure. The obvious implication is that the mapping between numeric identifiers and string tokens is compromised: an attacker can determine that `NgDmz` corresponds to `77305`, and perhaps can create their own token for another number.

The more serious issue is that the salt value can be recovered, and this is often a sensitive value used for other security operations. Many frameworks have a secret key used for multiple purposes, such as signing password reset tokens or session identifiers.

* Flask has `app.secret_key`
* Django has `settings.SECRET_KEY`
* Laravel has `app.key`
* Ruby has `secret_key_base`

It is tempting to use such as secret as salt in Hashids:

```
$hashids = new Hashids(Config::get('app.key'));
```

This makes it possible to determine the salt from observing tokens that the application outputs. The salt is set to the secret key of the framework, and this compromises the security of framework functionality, such as authentication, sessions, encryption, etc.

## Recovering the salt given an oracle

## Read more

* [Carnage's tech talk - Cryptanalysis of hashids](https://carnage.github.io/2015/08/cryptanalysis-of-hashids)
