---
layout: post
title: "A practical attack on Hashids"
thumbnail: brick-wall-480.jpg
date: 2023-11-25
---

Hashids is an algorithm that converts numbers to string tokens and back. It's similar to base64 encoding, but with extra steps. For example, it can convert `77305` to `NgDmz`. It takes a salt parameter that alters the encoding, but this salt can be recovered by looking at pairs of numbers and tokens.

<!-- Photo source: https://pixabay.com/photos/wall-bricks-shadow-home-texture-1358958/ -->

## How Hashids works

Hashids converts numbers to string tokens. These are often used in the URL as identifiers, instead of using numeric identifiers. The algorithm takes a *salt* parameter that changes the encoding algorithm.

Hashids take the following parameters:

- salt; a semi-secret key that changes the mapping between numbers and tokens
- minimum length; tokens shorter than this are padded
- alphabet; a sequence of characters, by default a-zA-Z0-9

Hashids works approximately like this:
The alphabet is shuffled several times when encoding a value. The value of salt determines the shuffle. Besides some marker and separator characters, the number input is basically base64 encoded using the shuffled alphabet.

More precisely, with the default alphabet, it works like this:

1. separators ("cfhistu") are removed from the alphabet
2. the alphabet is shuffled depending on the salt
3. the first four characters (guards) are removed from the alphabet
4. a single character, depending on the input number, is added to the output
5. the alphabet is shuffled depending on the character from the previous step, the salt and the alphabet
6. the number is base 44 encoded using the shuffled alphabet and added to the output

## A history of security claims

The Hashids algorithm encodes numbers into string tokens and back. These tokens are often used in a URL to identify resources. Hashids has never been a secure algorithm, but it started out with security claims:

* 2012 - launches, claims it is suitable for "making pages private"
* 2013 - claims it is suitable for "forgotten password hashes", and that "There's not a way to guess the correct numbers behind the hash without knowing the salt value first."
* 2014 - Hashids retracts the security claims: "Do not encode sensitive data. ... This is not a true encryption algorithm."
* 2015 - [an attack](https://carnage.github.io/2015/08/cryptanalysis-of-hashids) is published to make brute forcing the salt easier.
* 2016 - the homepage still hints at security, for example by claiming "Incremental input is mangled to stay unguessable."
* 2023 - Hashids rebrands to [Sqids](https://sqids.org/), and removes the "salt" and "hash" terminology to avoid hinting at security.

## Recovering the salt given an oracle

If the application exposes Hashid mappings, we can use that to determine the secret key. The attack is explained below. It needs less than 100 chosen pairs of numbers and string tokens. In most cases, it can reliably determine the first 17 characters of the salt, and sometimes up to the first 37 characters.

### Overview

When encoding a number with Hashids, The alphabet is shuffled twice.

* If the default settings are used, we know the alphabet it starts with.
* After the first shuffle, one character is output. We can look at that character to determine the order of the alphabet after the first shuffle.
* After the second shuffle, the number is encoded. By encoding specific numbers, we can retrieve information on the order after the second shuffle.

The shuffle uses the characters from the salt to determine which characters to swap in the alphabet. By checking which characters are swapped in both of the shuffles, we get some information about the characters in the salt. Since the alphabets are different in size, these pieces of information are different and can be combined to obtain the value of the salt character.

### Shuffle algorithm

The [shuffle algorithm](https://github.com/davidaurelio/hashids-python/blob/master/hashids.py#L65-L80) shuffles the alphabet. The permutation is dependent on the value of the salt. The algorithm walks back to front through the alphabet, and swaps the current character with a character at another position. This position is determined by the value of a character in the salt. The last character of the alphabet depends on the first character of the salt, etc.

After the shuffle is done, if we can determine the last character in the alphabet, we learn something about the first character of the salt.

### Attack example

We perform 45 queries to the oracle. Only three are shown here:

```
hashid(15) == "X0"
hashid(43) == "5e"
hashid(1891) == "xXq"
```

The first character of each of these tokens is output after one shuffle. We'll call this character the *lottery*. After that, the number is encoded after the second shuffle.

We'll first get some information from the first shuffle, which shuffles the alphabet after the separators are taken out, but before the guards are taken out. This alphabet is 48 characters in length. Shuffling works back to front, so we'll also work back to front.

The lottery is based on the input number. If we encode 43, the lottery is the character at index 43 in the alphabet. Before the guards were taken out, this character was at index 47. We get `5` as lottery character. The `5` was in position 42 before the shuffle. So when the shuffle was done, position 47 was swapped with position 42. The shuffle function uses the first character of the salt for this shuffle. We can determine that twice the value of this character modulo 47 equals 42.

Next, the second shuffle. We choose to encode 42 &times; 44 + 43 = 1891, because this encodes as the last two characters from the alphabet in base 44.
After the second shuffle, `Xq` are the last characters in the alphabet. From `hashid(15)`, we know that `X` used to be in position 15 after the first shuffle. The second shuffle works on 44 characters, and prepends the lottery character (`x`) to the salt. The first shuffle loop adds 120 (`ord('x')`) to the sum. In the second loop:

$$
\begin{align}
salt[0] + 1 + salt[0] + 120 = 15 \mod 42 \\
2 \times salt[0] = 20 \mod 42
\end{align}
$$

We now know:

$$
\begin{align}
2 \times salt[0] = 42 \mod 47 \\
2 \times salt[0] = 20 \mod 42
\end{align}
$$

Run the Chinese Remainder Theorem, and

$$
\begin{align}
2 \times salt[0] = 230 \mod 1974 \\
salt[0] = 115 = s
\end{align}
$$

The first character of the salt is "s".

## Automated attack

The attack is possible with pen and paper, but of course you can also run [this Python script](https://github.com/Sjord/crack-hashids/blob/main/crackhashids.py).

```
$ python3 crackhashids.py
Attempting to find salt[0]:
5: 42 -> 47
2 x salt[0] = 42 - 0 - 0 = 42 mod 47
1891 -> JXR
2 x salt[0] = 11 - 74 - 1 = 20 mod 42
salt: s
Attempting to find salt[1]:
...
Attempting to find salt[19]:
Y: 4 -> 28
2 x salt[19] = 4 - 2070 - 19 = 15 mod 28
1055 -> XD7
2 x salt[19] = 5 - 2158 - 20 = 12 mod 23
No single solution found. Candidates: []
salt so far: secretsaltysaltines
65 oracle queries
```

In this case, it recovers the full salt with 65 queries to the oracle in under a second.

## Conclusion

It is pretty easy to recover the salt by just observing Hashids input and output. This is a practical attack that recovers a large part of the salt, with a limited amount of chosen queries. The attack is reliable and does not depend on brute forcing the salt value.

## Read more

* [Carnage's tech talk - Cryptanalysis of hashids](https://carnage.github.io/2015/08/cryptanalysis-of-hashids)

<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3.1.2/es5/tex-mml-chtml.js" integrity="sha384-fNl9rj/eK1wEYfKc26CbPM6qkVQ+9MvYaoAFNql4ulbjBEWV2XLNP1UB8jQTtSe3" crossorigin="anonymous"></script>
