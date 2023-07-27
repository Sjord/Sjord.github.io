---
layout: post
title: "Encrypting small things"
thumbnail: tree-tag-480.jpg
date: 2023-05-10
---

In the previous post, I suggested encrypting identifiers in URLs. However, it turns out there is no straightforward way to do this. The best practice is to don't do it at all. So in this post we explore the problems and what it would take to do it anyway.

## Introduction

URLs can contain identifiers. In the following example, it identifies user 123:

```
https://example.org/user/123/profile
```

Usually, the identifier is simply the primary key of the database table. However, if you want to defend against insecure direct object references, it may be useful to use a different identifier. This can be done by encrypting the identifier when constructing the URL, and decrypting the identifier from the URL to obtain the database key.

It turns out we don't really know how to do this, or at least there is no canonical way to securely encrypt identifiers into something short.

## Identifier length

We want URLs that are somewhat manageable. Encrypting and authenticating a number is easy to do securely, but typically you end up with a long ciphertext. One ciphertext block of AES128-GCM contains:

* 96 bit nonce
* 128 bit ciphertext
* 128 bit authentication tag

A 352 bit identifier results in a 59 character base64-encoded string, which is a bit much for a URL.

It would be nice if we can encrypt a small identifier into a small encrypted ciphertext. For example, encrypt a 32 bit integer into a 6-character identifier.

## Encryption for data integrity?

We actually want to protect the integrity of the identifier more than the confidentiality. If a user knows they are accessing user 123, that's not necessarily a problem; the problem is they can create the identifier for user 124 themselves.

To protect integrity, we would normally add a HMAC to the identifier. However, I would argue that in this case encryption works better to protect the integrity of the identifier.

Suppose we have an output identifier of 32 bits. We need all those bits to store the input identifier, as it's also 32 bits. There's no room anymore for the HMAC.

Even with a slightly bigger output identifier, encryption has advantages above a HMAC. Suppose we use 32 bits for the identifier, and 32 bits for the HMAC. Our final identifier would look something like `123-MGVlNz`. If an attacker wants to access record 124, they only need to brute-force 32 bits. Whereas if we encrypted the identifier using a 64-bit block cipher, they would need to brute-force all 64 bits.

## Small block ciphers

AES has a block size of 128 bits, which means it outputs at least 128 bits, even without authentication or nonces. This is too much for our use case, but luckily some ciphers have a smaller block size. Here are some examples:

* [Blowfish](https://en.wikipedia.org/wiki/Blowfish_(cipher)), with a 64 bit block size
* [Speck](https://en.wikipedia.org/wiki/Speck_%28cipher%29), with block sizes of 32, 48, 64, 96 or 128 bits
* [Hasty Pudding cipher](https://en.wikipedia.org/wiki/Hasty_Pudding_cipher), with a variable block size
* [Format-preserving encryption](https://en.wikipedia.org/wiki/Format-preserving_encryption) ciphers, also with variable block size

Smaller block size means new security risks. For a 128 bit cipher, it's impossible for an attacker to query a significant portion of all possible blocks. But for a 32 bit cipher this is entirely possible.

Of course, brute forcing also becomes easier when using smaller block sizes.

## Stream ciphers

Can we use a stream cipher instead of a block cipher? No. A stream cipher works by creating a pseudorandom stream based on the key and IV and XORing it with the plaintext.

* If we use a fixed IV, the pseudorandom stream is always the same and the encryption is reduced to XOR with a fixed value, which is easily breakable.
* If we use a random IV, we have to include it in the identifier, making the identifier both longer and random.

The power of a stream cipher is expanding a small key and IV into a huge stream of pseudorandom data. Since we only want to encrypt approximately 64 bits, we don't need that.

## Tweakable ciphers

Maybe we want the encryption for *Customer* objects to work differently than that for *Invoice* objects. The identifier for invoice 123 could encrypt to something different than the identifier for customer 123. This way, if someone receives invoice 123 they can still not use the identifier from the url to retrieve customer 123.

So besides passing the identifier to the encryption function, we want to pass the object class too. We don't need to pass the class along in the identifier, because when we decrypt it we supposedly know what we expect.

```
// Different classes result in different identifiers
$customer_id = encrypt('customer', 123);    // 6OtPaG
$invoice_id = encrypt('invoice', 123);      // baiQSA

// When decrypting, we specify what class we expect
$invoice_id = decrypt('invoice', $_GET['invoice_id']);
```

In [AEAD ciphers](https://en.wikipedia.org/wiki/Authenticated_encryption), this would be the associated data, AD. In tweakable block ciphers, this would be the tweak.

## Key rotation

What happens when we change the encryption key? All identifiers necessarily change. This can result in a bad user experience. All links to your website that contain an old identifier are now broken.

## OWASP's solution

## Paragon's solution

## Hashids
