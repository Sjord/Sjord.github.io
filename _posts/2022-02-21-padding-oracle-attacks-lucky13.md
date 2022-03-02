---
layout: post
title: "Padding oracle attacks and Lucky 13"
thumbnail: roulette-13-480.webp
date: 2022-02-23
---


## Padding

Block ciphers divide data into chunks and encrypt each chunk. Such a chunk, or block, is usually 16 bytes. The message is separated into blocks of 16 bytes, and each block is fed through the encryption function.

But sometimes you want to encrypt a message that is not a multiple of 16. If your message is 10 bytes, you need to fill up the last six bytes with something before you can feed it into the block cipher.

A naive approach would be to just pad the message with some special character, such as nul-bytes or `x`s. However that will interfere with messages ending in such a character. Since we want to be able to send any character through AES, we need to encode the length of the message or the length of the padding somehow in the message. The standard way to do this ([PKCS #7](https://datatracker.ietf.org/doc/html/rfc2315#section-10.3)) is to use the length of the padding as the padding byte. If the padding is 7 bytes long, it is padded with seven `0x07` bytes.

## Padding oracle

It is also possible for a padding to be incorrect. If the decrypted message ends with `7`, it must end with `7777777`. Anything else is invalid padding. If an attacker can notice whether the padding is valid or invalid during decryption, then this provides him with a little bit information. A server that leaks this information is called a [padding oracle](https://en.wikipedia.org/wiki/Padding_oracle_attack).

## Solution

The structural solution is to avoid CBC ciphers and PKCS #7 padding, and these are indeed no longer supported with TLS 1.3.

However, many attempts have been made to fix padded CBC ciphers. To avoid becoming a padding oracle, the apparent behaviour must be the same whether the padding is correct or incorrect. The TLS implementation must check the padding and reject the message if invalid, but it must not behave differently to the client when it has done so. This turns out to be pretty hard to implement.

In the [original attack](https://link.springer.com/content/pdf/10.1007/3-540-46035-7_35.pdf), the TLS implementation simply returned a different error message when the padding was incorrect. That was quickly fixed, but that did not entirely solve the problem. Even though the error message was the same, the time it took to return a result varied. When the padding is incorrect, the message is quickly discarded instead of decrypted, which naturally takes a shorter time. This time difference reveals information to the client about whether the padding was correct. This new attack is called Lucky 13.

<aside markdown="1">
## Side-channel threat model

The difference between a correct and incorrect padding results in microseconds of difference. This is difficult to measure over the internet. However, even though [remote timing attacks are practical](https://www.usenix.org/legacy/event/sec03/tech/brumley/brumley_html/), it's often possible to get on the local network or even the same computer. If the target site is hosted on a Platform-as-a-Service (PAAS) provider, the adversary can start their own VM inside the same datacenter as the victim. In some cases, the adversary's VM can be running on the same physical host. This makes it much more feasible to perform timing attacks, and may even enable other side channels such as [cache timing attacks](https://dl.acm.org/doi/pdf/10.1145/2660267.2660356).
</aside>

## Timing solutions



<!-- photo source: https://pixabay.com/photos/happiness-lucky-number-roulette-839035/ -->

* [Bleichenbacher, Daniel. "Chosen ciphertext attacks against protocols based on the RSA encryption standard PKCS# 1.", 1998](https://link.springer.com/content/pdf/10.1007%252FBFb0055716.pdf)
* [Vaudenay, Serge. "Security flaws induced by CBC padding" 2002](https://link.springer.com/content/pdf/10.1007/3-540-46035-7_35.pdf)
* [Al Fardan, Nadhem J., and Kenneth G. Paterson. "Lucky thirteen: Breaking the TLS and DTLS record protocols." 2013](https://cve.report/CVE-2013-1618/f2cdb3c3.pdf)
* [Meyer, Christopher, et al. "Revisiting SSL/TLS Implementations: New Bleichenbacher Side Channels and Attacks." 2014](https://www.usenix.org/system/files/conference/usenixsecurity14/sec14-paper-meyer.pdf)
* [Irazoqui, Gorka, et al. "Lucky 13 strikes back." 2015](https://citeseerx.ist.psu.edu/viewdoc/download?doi=10.1.1.700.1952&rep=rep1&type=pdf)
* [NVD - CVE-2016-2107](https://nvd.nist.gov/vuln/detail/CVE-2016-2107)
* [Albrecht, Martin R., and Kenneth G. Paterson. "Lucky microseconds: A timing attack on amazon’s s2n implementation of TLS." 2016](https://link.springer.com/chapter/10.1007/978-3-662-49890-3_24)
* [Ronen, Eyal, Kenneth G. Paterson, and Adi Shamir. "Pseudo constant time implementations of TLS are only pseudo secure." 2018](https://eprint.iacr.org/2018/747.pdf)
* [Böck, Hanno, Juraj Somorovsky, and Craig Young. "Return Of Bleichenbacher’s Oracle Threat (ROBOT)." 2018](https://www.usenix.org/system/files/conference/usenixsecurity18/sec18-bock.pdf)
* [Drees, Jan Peter, et al. "Automated Detection of Side Channels in Cryptographic Protocols: DROWN the ROBOTs!." 2021](https://eprint.iacr.org/2021/591.pdf)
