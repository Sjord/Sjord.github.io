---
layout: post
title: "Tweakable block ciphers"
thumbnail: adjusting-480.jpg
date: 2023-09-27
---

With block ciphers, using a different key results in a different encryption. Sometimes it is also useful to have different encryption without changing the key. That's were tweakable block ciphers come in.

<!-- Photo source: https://pixabay.com/photos/adjusting-berlin-club-disco-dj-1836839/ -->

## Introduction

Block ciphers encrypt data one chunk at a time. Besides the plaintext, a secret key is used as input. The key changes the encryption, so you would have to know the key to decrypt it again. However, it turns out that there are other situations in which it is useful to change the encryption, even when using the same key. Tweakable block ciphers have an additional input, the tweak, that change the encryption in a similar way that using another key would do. Unlike the key, the tweak is not necessarily secret. Encrypting the same message with the same key but another tweak results in a different ciphertext, and this property is useful in some cases.

## Motivation

Tweakable block ciphers can be used to avoid interoperability between different encryptions that use the same key. Consider a web application that encrypts its session tokens, and also its password reset tokens. It should not be possible to use a session token as a password reset, or the other way around. Therefore, the session tokens should be encrypted differently from the password reset tokens. A different tweak can help with this. Similarly, when encrypting multiple blocks it should not be possible to change the ordering of the ciphertext blocks.

Furthermore, tweakable block ciphers can help with confidentiality when encrypting the same thing multiple times. If we naively encrypt the same data twice with the same key, the ciphertext would look the same. An attacker that has access to the ciphertext could learn something from this. Even if they can't decrypt the data, they can see that the same data was used twice. If we want to avoid this, we have to encrypt the value differently the next time it comes along. A tweak can help with this.

## Comparison with block cipher mode of operation

Block ciphers are almost always used in a [mode of operation](https://en.wikipedia.org/wiki/Block_cipher_mode_of_operation), such as CBC or GCM. Here, a nonce or initialization vector (IV) is used to randomize encryption, so that two identical messages still result in a different plaintext. This is very similar to the tweak used in tweakable block ciphers.

<img src="/images/cbc-encryption.svg" style="width: 100%">

In CBC, the IV or the ciphertext of the previous block is XOR-ed with the plaintext of the next block. The entire purpose of this is to change (or "tweak") the encryption. However, it's [imperfect](/2018/04/25/bitflip-effect-on-encryption-operation-modes/) and hard to reason about. By including the tweaking functionality in the cipher, it's easier to create secure modes of operation, and easier to show that these are secure.

## Disk encryption

When [encrypting blocks on the disk](https://en.wikipedia.org/wiki/Disk_encryption_theory), we also want to add a random element to the encryption so that two blocks that have the same content still end up with a different ciphertext. However, storing IVs or nonces is problematic, since that would make the data larger, making it hard to encrypt blocks inline. Also, we want to be able to seek within the data. If we encrypt the whole disk using CBC, we can only decrypt data starting from the beginning and working towards the end. It's not possible to only encrypt one file in the middle of the disk. Tweakable encryption is a solution here. The block index is used for the tweak. Blocks remain the same size, and blocks can be decrypted independently from each other, while still having encryption varying between blocks.

## Why not use a different key?

To vary the encryption algorithm, we could just modify the key. However, for most algorithms the key setup takes quite a lot of computing power. The idea behind the tweak is that it is cheaper, performance wise, to change the tweak than it is to change the key.

Also, because of [related-key attacks](https://en.wikipedia.org/wiki/Related-key_attack) it would be risky to combine the key and tweak in a simple manner.

## Converting a block cipher to tweakable

It's possible to use a conventional block cipher as building block for a tweakable block cipher. Often, additional encryption steps and some XORing is required. For example, to create a tweakable block cipher *Ẽ* that takes a tweak *T* and a key *K* to encrypt a message *M* from an ordinary block cipher *E*: 

$$\operatorname{\tilde{E}_K}(T, M) = \operatorname{E_K}(T \oplus \operatorname{E_K}(M))$$

This method is reasonably secure under some assumptions, which is of course not sufficient for cryptographers. Particularly, the security is limited by the birthday bound, named after the birthday paradox. For a *n*-bit cipher that outputs 2<sup>n</sup> possible values, a collision can often be found within 2<sup>n/2</sup> queries. If a cipher can withstand more than 2<sup>n/2</sup> queries, it's considered *beyond-birthday-bound secure*.

Another disadvantage of the method above is that the tweak needs to be exactly the same size as what *E* outputs. For a 64-bit block cipher, the tweak can't be more than 64 bits.

Instead of using existing block ciphers as black box components, it is also to create a new cipher that takes a tweak, or modify an existing cipher to take a tweak. [Threefish](https://en.wikipedia.org/wiki/Threefish) is an example of a tweakable block cipher.

Constructing efficient, optimally secure tweakable block ciphers with tweaks of arbitrary size is still ongoing research.

## Conclusion

Tweakable block ciphers are useful in many situations, and make it easier to prove security. However, they are rarely used outside disk encryption, and research is ongoing as to how to create an efficient and secure tweakable block cipher.

## Read more

* [Tweakable block ciphers](https://escholarship.org/content/qt311931t6/qt311931t6.pdf)
* [Efficient Instantiations of Tweakable Blockciphers and Refinements to Modes OCB and PMAC](https://www.cs.ucdavis.edu/~rogaway/papers/offsets.pdf)
* [How to Build Fully Secure Tweakable Blockciphers from Classical Blockciphers](https://eprint.iacr.org/2016/876.pdf)

<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3.1.2/es5/tex-mml-chtml.js" integrity="sha384-fNl9rj/eK1wEYfKc26CbPM6qkVQ+9MvYaoAFNql4ulbjBEWV2XLNP1UB8jQTtSe3" crossorigin="anonymous"></script>
