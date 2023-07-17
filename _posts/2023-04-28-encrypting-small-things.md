---
layout: post
title: "Encrypting small things"
thumbnail: tree-tag-480.jpg
date: 2023-05-10
---

In the previous post, I suggested encrypting identifiers in URLs. However, it turns out there is no straightforward way to do this if you want your identifiers to be small.

## Introduction

URLs can contain identifiers. In the following example, it identifies user 123:

```
https://example.org/user/123/profile
```

Usually, the identifier is simply the primary key of the database table. However, if you want to defend against insecure direct object references, it may be useful to use a different identifier. This can be done by encrypting the identifier when constructing the URL, and decrypting the identifier from the URL to obtain the database key.

At the same time, we want URLs that are somewhat manageable. Encrypting and authenticating a number is easy, but typically you end up with a 128 bit ciphertext and a 128 bit authentication tag. A 256 bit identifier results in a 43 character base64-encoded string, which is a bit much for a URL.

It would be nice if we can encrypt a small identifier into a small encrypted ciphertext. For example, encrypt a 32 bit integer into a 6-character identifier.

It turns out we don't really know how to do this, or at least there is no canonical way to securely encrypt identifiers into something short.

## Encryption for data integrity?

We actually want to protect the integrity of the identifier more than the confidentiality. If a user knows they are accessing user 123, that's not necessarily a problem; the problem is they can create the identifier for user 124 themselves.

To protect integrity, we would normally add a HMAC to the identifier. However, I would argue that in this case encryption works better to protect the integrity of the identifier.

Suppose we have an output identifier of 32 bits. We need all those bits to store the input identifier, as it's also 32 bits. There's no room anymore for the HMAC.

Even with a slightly bigger output identifier, encryption has advantages above a HMAC. Suppose we use 32 bits for the identifier, and 32 bits for the HMAC. Our final identifier would look something like `123-MGVlNz`. If an attacker wants to access record 124, they only need to brute-force 32 bits. Whereas if we encrypted the identifier using a 64-bit block cipher, they would need to brute-force all 64 bits. 

## Small block ciphers

AES has a block size of 128 bits, which means it outputs at least 128 bits. This is too much for our use case, but luckily some ciphers have a smaller block size.

Smaller block size means new security risks. For a 128 bit cipher, it's impossible for an attacker to query a significant portion of all possible blocks. But for a 32 bit cipher this is entirely possible. The holy grail is that even if the attacker queries all the possible blocks, they can't distinguish the encryption from a random permutation.

### 64 bit or less

* KATAN
* KTANTAN
* Speck
* Skip32
* Simeck32

### Variable

* Hasty pudding

### Format preserving encryption

* FPE
* FF1 = FFX
* FF2 = VAES3
* FF3 = BPS
* FFSEM
* FEA2
* Cisco FNR
* Protegrity DTP
* Thorp shuffle
* Swap or not
* Mix & cut
* Partition & mix