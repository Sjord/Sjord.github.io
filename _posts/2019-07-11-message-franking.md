---
layout: post
title: "Breaking message franking"
thumbnail: seal-480.jpg
date: 2019-11-20
---

Message franking is a mechanism to facilitate abuse reporting when using end-to-end encryption. This article describes how it works and how it can fail.

## The problem

Facebook Messenger provides end-to-end encryption. This means that Facebook is not aware of the contents of messages. This introduces a problem when a user reports an abusive message to Facebook: Alice says Bob is sending dick pics to her, but Facebook can't know whether this is true since it can't view the message contents.

## Message franking

Message franking solves this by providing verifiable abuse reporting. When Bob sends a message to Alice, they first create a binding tag, which is a HMAC using a random key. The HMAC is readable by Facebook, but the HMAC key and the message are encrypted with Alice's key. Facebook stores this HMAC before sending it and the encrypted message along to Alice. 
When Alice reports the message, they provide the message and the key used for the HMAC to Facebook. Facebook can verify that it indeed seen this HMAC and that the message matches this HMAC. Bob can no longer deny that they are sending abusive messages.

## Franking on attachments

For files, an optimization is done. Instead of calculating the HMAC over the whole file, which may be slow, the file is encrypted and the HMAC is only calculated over the file's encryption key. Since they is generally much shorter than the attachment, this speeds up calculating the HMAC. Facebook remembers a hash of the ciphertext and the HMAC of the encryption key. When Alice reports the attachment, Facebook can check that the decryption key was indeed sent, and that the image decrypt to an abuse image.

## Flawed deduplication

Facebook only stored information on each ciphertext once. If Facebook saw the same ciphertext multiple times, it would assume that it was the same image sent multiple times, and only store the first instance. This deduplication behaviour can be exploited by sending the same ciphertext with different decryption keys. It turns out it is possible to find two decryption keys for the same ciphertext, where each decryption key results in a different image.

## Attack

The attack thus looks as follows: an attacker creates two images with the same ciphertext, but with a different decryption key. The attacker first sends the innocuous image. Facebook stores information on this, to make verifyable reporting possible. Then, the attacker sends the malicious image. Since it has the same ciphertext, Facebook thinks it has already seen this image and doesn't store reporting information on it. The receiver receives the malicious image, but can't report it.

## Cause

The cause of this flaw is that the encryption algorithm used is not *robust*: it is possible to have a ciphertext that decrypts to different things, depending on the key. Creating a binding tag on the key is thus not sufficient to prove particular message contents. For a secure mechanism, it's really needed to calculate the HMAC over the whole attachment file.

## Conclusion


## Read more

* [Messenger Secret Conversations](https://fbnewsroomus.files.wordpress.com/2016/07/secret_conversations_whitepaper-1.pdf)
* [Message Franking via Committing Authenticated Encryption](https://eprint.iacr.org/2017/664.pdf), [video](https://www.youtube.com/watch?v=ky9nRIl_TqY)
* [Private Message Franking with After Opening Privacy](https://eprint.iacr.org/2018/938.pdf)
* [Fast Message Franking From Invisible Salamanders to Encryptment](https://eprint.iacr.org/2019/016.pdf), [video](https://www.youtube.com/watch?v=9xePC0Tyeuc&t=2952s)
