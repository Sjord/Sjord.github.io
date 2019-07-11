---
layout: post
title: "Breaking message franking"
thumbnail: todo-240.jpg
date: 2019-11-20
---

Facebook Messenger provides end-to-end encryption. This means that Facebook is not aware of the contents of messages. This introduces a problem when a user reports an abusive message to Facebook: Alice says Bob is sending dick pics to her, but Facebook can't know whether this is true since it can't view the message contents.

Message franking solves this by providing verifiable abuse reporting. When Bob sends a message to Alice, he first creates a HMAC using a random key. The HMAC is readable by Facebook, but the HMAC key and the message are encrypted with Alice's key. Facebook stores this HMAC before sending it and the encrypted message along to Alice. 
When Alice reports the message, she provides the message and the key used for the HMAC to Facebook. Facebook can verify that it indeed seen this HMAC and that the message matches this HMAC. Bob can no longer deny that he is sending abusive messages.

For files, an optimization is done. Instead of calculating the HMAC over the whole file, which may be slow, the file is encrypted and the HMAC is only calculated over the file encryption key.

* [Messenger Secret Conversations](https://fbnewsroomus.files.wordpress.com/2016/07/secret_conversations_whitepaper-1.pdf)
* [Message Franking via Committing Authenticated Encryption](https://eprint.iacr.org/2017/664.pdf), [video](https://www.youtube.com/watch?v=ky9nRIl_TqY)
* [Private Message Franking with After Opening Privacy](https://eprint.iacr.org/2018/938.pdf)
* [Fast Message Franking From Invisible Salamanders to Encryptment](https://eprint.iacr.org/2019/016.pdf), [video](https://www.youtube.com/watch?v=9xePC0Tyeuc&t=2952s)