---
layout: post
title: "Bitflip effect on encryption operation modes"
thumbnail: flipswitch-240.jpg
date: 2018-04-25
---

In a [bitflip attack](https://en.wikipedia.org/wiki/Bit-flipping_attack), the attacker modifies ciphertext in a way that predictably changes the decryption result. This way, an attacker can tamper with data even if it's encrypted. How this works depends on which encryption [mode of operation](https://en.wikipedia.org/wiki/Block_cipher_mode_of_operation) is used. In this post we'll look into what behavior each mode has.

<!-- Photo source: https://www.flickr.com/photos/nasarobonaut/5456219255 -->

## Bit flipping

Block ciphers can only encrypt fixed-size blocks, typically of 16 bytes in length. These ciphers are secure against bit flipping: if the input is changed slightly, the output [changes significantly](https://en.wikipedia.org/wiki/Avalanche_effect). Bit flipping becomes possible if several ciphertext blocks are combined in a certain way. In several modes of operation the ciphertext is XORed with plaintext from the next or previous block. This means that flipping a bit garbles one whole block, and flips one bit in another block.

### CBC example

For a practical example, consider we are served an encrypted cookie that contains the following:

    {"user": "johndoe", "nick": "john", "admin": 0}

When this is encrypted using CBC mode, it is split up in blocks of 16 bytes. Each block is XORed with the previous block (or the initialization vector) and encrypted. The message consists of the following blocks:

* `{"user": "johndo`
* `e", "nick": "joh`
* `n", "admin": 0}`

<img src="/images/bitflip-cbc-encryption.png">

#### The attack

In our bitflip attack, we flip a bit in the ciphertext of block 2. This will cause block 2 in the decrypted plaintext to become garbage, but it will also flip a bit in block 3. This happens because the decryption result is XORed with the ciphertext of the previous block, which is under our control:

<img src="/images/bitflip-cbc-decryption.png">

The result is a cookie containing valid JSON, with the admin flag set to 1:

    {"user": "johndoaRbUTPasBIrmAnO5n", "admin": 1}
  
## Block cipher modes of operation

Various modes combine the blocks in various ways. This means that the behavior of a bitflip attack differs for each mode. Here is the summary:

* EBC: garbles same block
* CBC: garbles same block, flips bit in next block
* PCBC: garbles same and all following blocks
* CFB: flips bit in same block, garbles next block
* OFB: flips bit in same block
* CTR: flips bit in same block
