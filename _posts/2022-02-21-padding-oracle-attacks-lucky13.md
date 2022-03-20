---
layout: post
title: "Lucky 13 and other padding oracle attacks on CBC ciphers"
thumbnail: roulette-13-480.webp
date: 2022-03-20
---

Lucky 13 is a padding oracle timing attack on CBC ciphers, which required multiple patches to solve. Does this mean that this vulnerability is now solved for good, or that it is the vulnerability that keeps on giving?

<!-- photo source: https://pixabay.com/photos/happiness-lucky-number-roulette-839035/ -->

## Padding

Block ciphers encrypt data in blocks of 16 bytes. The message is separated into blocks of 16 bytes, and each block is fed through the encryption function. But sometimes you want to encrypt a message that is not a multiple of 16. If your message is 10 bytes, you need to fill up the last six bytes with something before you can feed it into the block cipher. This is called *padding*.

A naive approach would be to just pad the message with some special character, such as nul-bytes or `x`s. However, that will interfere with messages ending in such a character. Since we want to be able to send any character through AES, we need to encode the length of the message or the length of the padding somehow in the message. The standard way to do this ([PKCS #7](https://datatracker.ietf.org/doc/html/rfc2315#section-10.3)) is to use the length of the padding as the padding byte. If the padding is 7 bytes long, it is padded with seven `0x07` bytes.

<img src="/images/padding-normal.svg" width="100%">

## Padding oracle

The padding algorithm specifies that a certain number of bytes should have a certain value. This also makes it possible for the padding to be incorrect. If the decrypted message ends with `7`, it must end with `7777777`. Anything else is invalid padding. If an attacker can notice whether the padding is valid or invalid during decryption, then this provides him with a little bit information. A server that leaks this information is called a [padding oracle](https://en.wikipedia.org/wiki/Padding_oracle_attack).

The attacker can send manipulated messages to the server. Most of these will have invalid padding. If a message has valid padding, it probably ends with `1`. A one repeating once is the easiest padding to get right. The attacker learns one byte of plaintext, and can continue to try to get the padding `22`. In the end, they can decrypt the whole message this way, just by asking the server whether the padding is valid.

<img src="/images/padding-attacker.svg" width="100%">

## Solution

The structural solution is to avoid CBC ciphers and PKCS #7 padding, and these are indeed no longer supported with TLS 1.3.

However, many attempts have been made to fix padded CBC ciphers. To avoid becoming a padding oracle, the apparent behaviour must be the same whether the padding is correct or incorrect. The TLS implementation must check the padding and reject the message if invalid, but it must not behave differently to the client when it has done so. This turns out to be pretty hard to implement.

In the [original attack](https://link.springer.com/content/pdf/10.1007/3-540-46035-7_35.pdf), the TLS implementation simply returned a different error message when the padding was incorrect. That was quickly fixed, but that did not entirely solve the problem. Even though the error message was the same, the time it took to return a result varied. When the padding is incorrect, the message is quickly discarded instead of decrypted, which naturally takes a shorter time. This time difference reveals information to the client about whether the padding was correct. This new timing attack on padding validity was called Lucky 13.

## Side-channel threat model

The difference between a correct and incorrect padding results in microseconds of difference. This is difficult to measure over the internet. However, even when attacking a remote target, it's often possible to get on the local network or even the same computer. If the target site is hosted on a Platform-as-a-Service (PAAS) provider, the adversary can start their own VM inside the same datacenter as the victim. In some cases, the adversary's VM can be running on the same physical host. This makes it much more feasible to perform timing attacks, and may even enable other side channels such as [cache timing attacks](https://dl.acm.org/doi/pdf/10.1145/2660267.2660356).

## Timing solutions

To avoid timing differences and thus being vulnerable to Lucky 13, operations should take the same time, whether the padding is valid or not. There are two possible solutions for this:

* pseudo constant time implementations: if the padding is invalid, perform some computations similar to what an actual decryption would do. Also, sleep for a random time, so that attackers have more difficulty to measure the time of operations.
* constant-time, constant memory-access: the code performs the exact same operations, whether the padding is valid or not.

Both solutions are hard to get correct.

### Pseudo constant time implementations

In pseudo constant time implementations, the TLS library attempts to perform a similar amount of operations when the padding is incorrect, as would be done by an actual encryption when the padding is correct. How much operations to perform depends on the decryption algorithm, and getting these numbers right for every decryption algorithm is not that easy. Several [bugs were found](https://eprint.iacr.org/2018/747.pdf) in multiple TLS libraries, where the amount of fake work did not match the work of an actual decryption.

Another countermeasure is to randomize the time a decryption takes, so it is harder for an attacker to measure the time. This has also been [proven to contain bugs](https://www.iacr.org/archive/eurocrypt2016/96650136/96650136.pdf), at least in Amazon's s2n. That TLS library passed a random number to usleep, to introduce a random time delay. The bug was that this sleeps for an integer amount of microseconds. If the attacker detects that something took 8.7 microseconds, the 8 part was random, but the .7 part still conveyed sufficient information.

### Constant-time, constant memory-access

OpenSSL rewrote their CBC implementation to be completely constant-time. This is hard to do and makes the code large and messy.

> its complexity is such that around 500 lines
of new code were required to implement it, and it is arguable whether
the code would be understandable by all but a few crypto-expert developers.

Of course, writing so many complex code has the potential to introduce [new bugs](https://nds.ruhr-uni-bochum.de/media/nds/veroeffentlichungen/2016/10/19/tls-attacker-ccs16.pdf). And it did:

> The padding oracle vulnerability we discovered in OpenSSL ([CVE-2016-2107](https://nvd.nist.gov/vuln/detail/CVE-2016-2107)) was introduced by
writing a constant-time patch that should have mitigated
the Lucky 13 attack

The patch that should have solved Lucky 13 introduced an even worse security vulnerability. A similar bug was identified in MatrixSSL, showing that it is not easy to solve Lucky 13 using constant time code.

## Conclusion

All TLS libraries have been patched against Lucky 13, multiple times. Patches have been implemented by various TLS libraries for attack variants released in 1998, 2002, 2013, 2014, 2015, 2016 and 2018. Whether CBC ciphers are now secure depends on your view of whether the seventh time is the charm, or that this is apparently an unsolvable problem.

## Read more

### Papers

* [Bleichenbacher, Daniel. "Chosen ciphertext attacks against protocols based on the RSA encryption standard PKCS# 1.", 1998](https://link.springer.com/content/pdf/10.1007%252FBFb0055716.pdf)
* [Vaudenay, Serge. "Security flaws induced by CBC padding" 2002](https://link.springer.com/content/pdf/10.1007/3-540-46035-7_35.pdf)
* [Al Fardan, Nadhem J., and Kenneth G. Paterson. "Lucky thirteen: Breaking the TLS and DTLS record protocols." 2013](https://cve.report/CVE-2013-1618/f2cdb3c3.pdf)
* [Meyer, Christopher, et al. "Revisiting SSL/TLS Implementations: New Bleichenbacher Side Channels and Attacks." 2014](https://www.usenix.org/system/files/conference/usenixsecurity14/sec14-paper-meyer.pdf)
* [Irazoqui, Gorka, et al. "Lucky 13 strikes back." 2015](https://citeseerx.ist.psu.edu/viewdoc/download?doi=10.1.1.700.1952&rep=rep1&type=pdf)
* [Albrecht, Martin R., and Kenneth G. Paterson. "Lucky microseconds: A timing attack on amazon’s s2n implementation of TLS." 2016](https://link.springer.com/chapter/10.1007/978-3-662-49890-3_24)
* [Somorovsky, Juraj. "Systematic fuzzing and testing of TLS libraries." 2016](https://www.nds.ruhr-uni-bochum.de/media/nds/veroeffentlichungen/2016/10/19/tls-attacker-ccs16.pdf)
* [Ronen, Eyal, Kenneth G. Paterson, and Adi Shamir. "Pseudo constant time implementations of TLS are only pseudo secure." 2018](https://eprint.iacr.org/2018/747.pdf)
* [Böck, Hanno, Juraj Somorovsky, and Craig Young. "Return Of Bleichenbacher’s Oracle Threat (ROBOT)." 2018](https://www.usenix.org/system/files/conference/usenixsecurity18/sec18-bock.pdf)
* [Drees, Jan Peter, et al. "Automated Detection of Side Channels in Cryptographic Protocols: DROWN the ROBOTs!." 2021](https://eprint.iacr.org/2021/591.pdf)

### Other

* [NVD - CVE-2016-2107](https://nvd.nist.gov/vuln/detail/CVE-2016-2107)
* [Cryptopals: Exploiting CBC Padding Oracles – NCC Group Research](https://research.nccgroup.com/2021/02/17/cryptopals-exploiting-cbc-padding-oracles/)
* [s2n and Lucky 13 - AWS Security Blog](https://aws.amazon.com/blogs/security/s2n-and-lucky-13/)
* [Padding oracles and the decline of CBC-mode cipher suites](https://blog.cloudflare.com/padding-oracles-and-the-decline-of-cbc-mode-ciphersuites/)
