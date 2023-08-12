---
layout: post
title: "Encrypting identifiers"
thumbnail: tree-tag-encrypted-480.jpg
date: 2023-08-02
---

In the [previous post](/2023/04/26/identifiers-uuid-ulid-security/), I suggested encrypting identifiers in URLs. However, it turns out there is no straightforward way to do this. The current best practice is to don't do it at all, and use a database mapping instead. In this speculative post we explore the problem and what it would take to encrypt identifiers anyway.

## Introduction

URLs can contain identifiers. In the following example, the `123` in the URL identifies user 123:

```
https://example.org/user/123/profile
```

Usually, the identifier is simply the primary key of the database table. However, it may be useful to use a different identifier. This can be done by encrypting the identifier when constructing the URL, and decrypting the identifier from the URL to obtain the database key. This way, user 123 would get an encrypted identifier such as `9Y92as` in the URL. The application decrypts the identifier from the URL to obtain the identifier to search for in the database. This hides the identifier and makes it harder for anyone to guess the identifier for user 124.

Hiding the actual identifier can be useful:
* to provide additional defense against insecure direct object reference vulnerabilities, making identifiers harder to guess,
* to hide information contained in the identifier, such as the number of records or time of creation.

Currently, there is not a straightforward or recommended way to encrypt identifiers into something short.

## Identifier length

We want URLs that are somewhat manageable. Encrypting and authenticating a number is easy to do securely, but typically you end up with a long ciphertext. One ciphertext block of AES128-GCM contains:

* 96 bit nonce
* 128 bit ciphertext
* 128 bit authentication tag

A 352 bit identifier results in a 59 character base64-encoded string, which is a bit much for a URL.

It would be nice if we can encrypt a small identifier into a small encrypted ciphertext. For example, encrypt a 32 bit integer into a 6-character identifier.

## Mode of operation

What we want is a pseudorandom permutation, which maps our 2<sup>32</sup> possible numeric identifiers from the database to 2<sup>32</sup> identifiers that we can use in the URL. To get a pseudorandom permutation, we don't want to use [CBC or GCM](https://en.wikipedia.org/wiki/Block_cipher_mode_of_operation) or anything, just the block cipher itself. The closest mode of operation is ECB, which is considered insecure because it exposes data structure across multiple blocks. However, we would only encrypt a single block.

## Encryption for data integrity?

We actually want to protect the integrity of the identifier more than the confidentiality. If a user knows they are accessing user 123, that's not necessarily a problem; the problem is they can create the identifier for user 124 themselves.

To protect integrity, we would normally add a HMAC to the identifier. However, I would argue that in this case encryption works better to protect the integrity of the identifier.

Suppose we have an output identifier of 32 bits. We need all those bits to store the input identifier, as it's also 32 bits. There's no room anymore for the HMAC.

Even with a slightly bigger output identifier, encryption has advantages above a HMAC. Suppose we use 32 bits for the identifier, and 32 bits for the HMAC. Our final identifier would look something like `123-MGVlNz`. If an attacker wants to access record 124, they only need to brute-force 32 bits. Whereas if we encrypted the identifier using a [64-bit block cipher](/2023/08/30/encryption-64-bit-block-ciphers/), they would need to brute-force all 64 bits.

To use the full 64 bits and avoid creating a decryption oracle, it's important that the decrypted value is not checked and just used in the database query. Any encrypted 64-bit value is a valid identifier, and whether it is a valid database identifier depends on whether the record is found in the database.

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

## Performance

The encryption and decryption needs to be reasonably fast. Often, the performance of ciphers is measured in MB/s. But since we are just encrypting one 64-bit number, this is not relevant to us.

Most ciphers have a key setup step, which takes some time before they can start to encrypt or decrypt things. The time it takes to output the first block is called the latency, which is what we are interested in. After the encryption algorithm is initialized, we want to keep it around. For web applications that keep running this is easy. However, with PHP web applications the process starts again on each page load, and we would need to take time for the key setup on every page load.

## Alternatives

We want attackers to stop messing with our identifiers, but encrypting them is not the only solution.

### Paragon's solution

[Paragon recommends to not encrypt identifiers](https://paragonie.com/blog/2015/09/comprehensive-guide-url-parameter-encryption-in-php), and to use a database mapping instead. I agree that this is currently best practice, since there is no recommended manner to encrypt identifiers.

> There isn't enough room to both encrypt the desired information and then authenticate the encrypted output.

This is accurate. By limiting ourselves to a short identifier, we make it pretty hard on ourselves, and can't use best practice encryption algorithms.

> Encryption without message authentication is totally broken.

This is generally true, but often a result of the [block cipher mode of operation](https://en.wikipedia.org/wiki/Block_cipher_mode_of_operation). If the cipher itself offers a pseudorandom permutation and we only encrypt exactly one block, [bit flip attacks](/2018/04/25/bitflip-effect-on-encryption-operation-modes/) and [padding oracles](/2022/03/20/padding-oracle-attacks-lucky13/) do not apply.

Cryptographic best practice advice is to use authenticated encryption, and that conflicts with our requirement of short identifiers. It's possible that there will never be a best practice recommended way to encrypt identifiers, since nobody wants to recommend skipping authentication.

### Sign the whole URL

Instead of protecting the integrity of a single number, we could also protect the integrity of the whole URL, including query string parameters. By adding a HMAC to the URL, the whole URL is protected. This makes the URL longer, but now only one HMAC is ever needed, regardless of how many parameters the URL contains.

### Use GUIDs

If you use long, random identifiers to begin with, they are already hard to guess. I wrote about this before in [Security of identifiers](http://www.sjoerdlangkemper.nl/2023/04/26/identifiers-uuid-ulid-security/).

Even if you use GUIDs, you may still want to encrypt them. For example, if you are using UUIDv7 for database locality but don't want to expose the timestamp the object was created. Since UUIDs are 128 bits, it's possible to use AES-128 to encrypt them.

## Conclusion

Encrypting identifiers could be a great defense-in-depth. It's not widely used, not supported by any framework I know, and no library available that offers this. It's technically possible, and I think it could be a good idea in some circumstances, so I think this problem is worth exploring some more.

The solution could be encrypting small blocks using ECB mode without any authentication. That one sentence already disregards three best-practice rules for cryptography, so I don't think anyone knowledgable in cryptographic security would recommend doing this.
