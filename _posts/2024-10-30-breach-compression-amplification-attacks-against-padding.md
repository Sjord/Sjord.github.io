---
layout: post
title: "Amplification for compression attacks"
thumbnail: megaphone-480.jpg
date: 2024-11-13
---

With [compression side channel attacks](/2016/08/23/compression-side-channel-attacks/) such as [BREACH](/2016/11/07/current-state-of-breach-attack/), an attacker can perform guesses for some sensitive content, and see from the response size whether their guess is correct. In the most straightforward attack, the response size decreases by one byte if their guess is correct. However, by using a more advanced and longer payload, the size difference can be increased. Small randomization of the response length is defeated by this attack.

<!-- Photo source: https://pixabay.com/photos/talk-shout-megaphone-tell-say-man-457540/ -->

## Recap

In this attack, the attacker can guess some secret on the page by observing the response size. The attacker's input is reflected on the page, and the page compresses better if the attacker's input already is present on the page. So the page size is reduced if the attacker guesses correctly.

[![Part of the reflected input and the secret are the same](/images/compression-demo-page2.png)](https://demo.sjoerdlangkemper.nl/compression.php)

## Attacks only get better

In the original attacks, the size difference for a correct guess was one byte, and each guess could disclose at most one bit of information. However, [a recent paper by Yuanming Song](https://ethz.ch/content/dam/ethz/special-interest/infk/inst-infsec/appliedcrypto/education/theses/masters-thesis_yuanming-song.pdf) shows that both these limits can be improved: the size difference can be increased, and multiple guesses can  be performed within one request.

## Amplification by telescoping

If we reflect "Your secret code is: 8", we get one byte difference, depending on whether 8 is in the original text or not. What if we want multiple bytes of difference?

The payload "Your secret code is: 8~Your secret code is: 8" won't work. The second part will always be the same as the first part, so it will compress efficiently. Instead, we can use "ur secret code is: 8~Your secret code is: 8". By omitting the first couple of letters, the second part is not identical to the first part. This creates a size difference of two bytes.

Of course, we can repeat this trick multiple times to increase the difference in length.

## Multiple queries

In the previous paragraph we created a payload that creates a size difference of two bytes when the guess is correct. We can combine this with a payload that gives a difference of one byte. For example, the following payload gives a one-byte difference when the secret starts with 7, and a two-byte difference when the secret starts with 8:

> Your secret code is: 7/ur secret code is: 8~Your secret code is: 8

This makes it possible to query multiple values in a single request.

## Defeating padding

Randomized padding is sometimes suggested to mitigate compression attacks. The [Heal-the-Breach](https://ieeexplore.ieee.org/abstract/document/9754554) paper suggests adding a fake filename to the gzip response, to increase the size randomly up to 10 bytes. However, with amplification attacks it is feasible to create a size difference of more than 10 bytes, defeating this mitigation. It is possible to increase the size of the padding, but it is also possible to create a payload with a larger amplification.

## Read more

- [Refined Techniques for Compression Side-Channel Attacks](https://ethz.ch/content/dam/ethz/special-interest/infk/inst-infsec/appliedcrypto/education/theses/masters-thesis_yuanming-song.pdf)
- [Compression side channel attacks](/2016/08/23/compression-side-channel-attacks/)
- [The current state of the BREACH attack](/2016/11/07/current-state-of-breach-attack/)
- [Compression side-channel attack demonstration](https://demo.sjoerdlangkemper.nl/compression.php)