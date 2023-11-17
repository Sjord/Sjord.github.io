---
layout: post
title: "HashIDs expose their secret key"
thumbnail: brick-wall-480.jpg
date: 2023-11-22
---

Hashids is an algorithm that converts numbers to string tokens and back. It's similar to base64 encoding, but with extra steps. For example, it can convert `77305` to `NgDmz`. It's sometimes used for security purposes, but it is not suitable for this.

<!-- Photo source: https://pixabay.com/photos/wall-bricks-shadow-home-texture-1358958/ -->

## A history of security claims

The Hashids algorithm encodes numbers into string tokens and back. These tokens are often used in a URL to identify resources. Hashids has never been a secure algorithm, but it started out with security claims:

* 2012 - launches, claims it is suitable for "making pages private"
* 2013 - claims it is suitable for "forgotten password hashes", and that "There's not a way to guess the correct numbers behind the hash without knowing the salt value first."
* 2014 - Hashids retracts the security claims: "Do not encode sensitive data. ... This is not a true encryption algorithm."
* 2015 - [an attack](https://carnage.github.io/2015/08/cryptanalysis-of-hashids) is published to make brute forcing the salt easier.
* 2023 - Hashids rebrands to [Sqids](https://sqids.org/), and removes the "salt" and "hash" terminology to avoid hinting at security.

## Unexplainably popular

In 2012, we already had AES, Blowfish, and [Bruce Schneier](https://www.schneier.com/blog/archives/2011/04/schneiers_law.html). "Don't roll your own crypto" was well-known advice, and back then "crypto" really did mean "cryptography". Against this background, a random developer creates Hashids, and it keeps gaining in popularity, even after the security claims were removed.

I don't understand it. Why would anyone want to use this base64 with extra steps, if it doesn't provide security? Is `NgDmz` so much better to use in a URL compared with `77305`?

On the other hand, there is not a truly cryptographically secure alternative. If you want to obfuscate numbers to use in URLs, there is no canonically recommended encryption algorithm. Apparently developers have a desire to obfuscate identifiers, which Hashids filled.

## How Hashids work

Hashids take the following parameters:

- salt; a semi-secret key that changes the mapping between numbers and tokens
- minimum length; tokens shorter than this are padded
- alphabet; a sequence of characters, by default a-zA-Z0-9

Hashids works approximately like this:
The alphabet is shuffled several times when encoding a value. The value of salt determines the shuffle. Besides some marker and separator characters, the number input is basically base64 encoded using the shuffled alphabet.

More precisely, with the default alphabet, it works like this:

1. separators ("cfhistu") are removed from the alphabet, reducing the size of the alphabet to 48
2. the alphabet is shuffled depending on the salt
3. the first four characters (guards) are removed from the alphabet, reducing the size to 44
4. a single character, depending on the input number, is added to the output
5. the alphabet is shuffled depending on the character from the previous step, the salt and the alphabet
6. the number is base 44 encoded using the alphabet and added to the output

## Hashids expose your secret key

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

The salt is set to the secret key of the framework. This makes it possible to determine the secret key from observing tokens that the application outputs, and this compromises the security of framework functionality, such as authentication, sessions, encryption, etc.

## Recovering the salt given an oracle

If the application exposes Hashid mappings, we can use that to determine the secret key.

### Overview

The alphabet is shuffled twice.

* If the default settings are used, we know the alphabet it starts with.
* After the first shuffle, one character is output. We can look at that character to determine the order of the alphabet after the first shuffle.
* After the second shuffle, the number is encoded. By encoding specific numbers, we can retrieve information on the order after the second shuffle.

The shuffle uses the characters from the salt to determine which characters to swap in the alphabet. By checking which characters are swapped in both of the shuffles, we get some information about the characters in the salt. Since the alphabets are different in size, these pieces of information are different and can be combined to obtain the value of the salt character.

### Example

We perform 45 queries to the oracle. Only three are shown here:

```
hashid(15) == "X0"
hashid(43) == "5e"
hashid(1891) == "xXq"
```

Remember, Hashids shuffles the alphabet, outputs one character, shuffles again, and outputs the encoded number. We'll call that first character the *lottery*.

We'll first get some information from the first shuffle, which shuffles the alphabet after the separators are taken out, but before the guards are taken out. This alphabet is 48 characters in length. Shuffling works back to front, so we'll also work back to front.

The lottery character is directly determined from the input number, modulo some other number. In the hashid for 15, "X" is at index 15 of the alphabet. For hashid 43, "5" is at position 43 of the alphabet.

The lottery is based on the input number. If we encode 43, the lottery is the character at index 43 in the alphabet. Before the guards were taken out, this character was at index 47. We get `5` as lottery character. The `5` was in position 42 before the shuffle. So when the shuffle was done, position 47 was swapped with position 42. The shuffle function uses the first character of the salt for this shuffle. We can determine that twice the value of this character modulo 47 equals 42.

Next, the second shuffle. After the second shuffle, `Xq` are the last characters in the alphabet. From `hashid(15)`, we know that `X` used to be in position 15. The second shuffle works on 44 characters, and prepends the lottery character (`x`) to the salt. The first shuffle loop adds 120 (`ord('x')`) to the sum. In the second loop:

```
salt[0] + 1 + salt[0] + 120 = 15 mod 42
2 * salt[0] = 20 mod 42
```

We now know:

```
2 * salt[0] = 42 mod 47
2 * salt[0] = 20 mod 42
```

Run the Chinese Remainder Theorem, and 2 * salt[0] = 230 mod 1974, which means salt[0] = 115, or "s".

## Conclusion


## Read more

* [Carnage's tech talk - Cryptanalysis of hashids](https://carnage.github.io/2015/08/cryptanalysis-of-hashids)
