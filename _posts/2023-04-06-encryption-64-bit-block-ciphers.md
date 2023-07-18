---
layout: post
title: "64-bit block ciphers"
thumbnail: blowfish-480.jpg
date: 2023-04-26
---

Use Blowfish.

<!-- Photo source: https://pixabay.com/nl/photos/blowfish-zee-oceaan-onderwater-2335648/ -->

## Introduction

## Alphabetical list

This is supposed to be an exhaustive list of block ciphers with a 64 bit block size.

* [Blowfish](https://en.wikipedia.org/wiki/Blowfish_(cipher))★
* [CAST-128 / CAST5](https://en.wikipedia.org/wiki/CAST-128)★
* [CIPHERUNICORN-E](https://en.wikipedia.org/wiki/CIPHERUNICORN-E)
* [CS-Cipher](https://en.wikipedia.org/wiki/CS-Cipher)
* [Data Encryption Standard (DES)](https://en.wikipedia.org/wiki/Data_Encryption_Standard), [3DES](https://en.wikipedia.org/wiki/Triple_DES)★, [DES-X - Wikipedia](https://en.wikipedia.org/wiki/DES-X)
* [FEAL](https://en.wikipedia.org/wiki/FEAL)†
* [GOST (Magma)](https://en.wikipedia.org/wiki/GOST_(block_cipher))‡
* [Hierocrypt](https://en.wikipedia.org/wiki/Hierocrypt)
* [HIGHT](https://www.iacr.org/archive/ches2006/04/04.pdf)
* [ICE](https://en.wikipedia.org/wiki/ICE_(cipher))
* [ICEBERG](https://iacr.org/archive/fse2004/30170280/30170280.pdf)
* [IDEA (International Data Encryption Algorithm)](https://en.wikipedia.org/wiki/International_Data_Encryption_Algorithm)★
* [KASUMI](https://en.wikipedia.org/wiki/KASUMI)†
* [KATAN64](https://www.iacr.org/archive/ches2009/57470273/57470273.pdf) / [KTANTAN64](https://www.iacr.org/archive/ches2009/57470273/57470273.pdf)
* [KHAZAD](https://en.wikipedia.org/wiki/KHAZAD)
* [Khufu / Khafre](https://en.wikipedia.org/wiki/Khufu_and_Khafre)†
* [KLEIN](https://ris.utwente.nl/ws/portalfiles/portal/5095833/The_KLEIN_Block_Cipher.pdf)
* [LBlock](https://eprint.iacr.org/2011/345.pdf)
* [LED](https://eprint.iacr.org/2012/600.pdf)
* [Lilliput](/papers/2015/extended-generalized-feistel-networks-using-matrix-representation-to-propose-a-new-lightweight-block-cipher-lilliput.pdf)
* [mCrypton](/papers/2006/mcrypton-a-lightweight-block-cipher-for-security-of-low-cost-rfid-tags-and-sensors.pdf)
* [Midori](https://eprint.iacr.org/2015/1142.pdf)
* [MISTY1](https://en.wikipedia.org/wiki/MISTY1)‡
* [Nimbus](https://en.wikipedia.org/wiki/Nimbus_(cipher))
* [NUSH](https://en.wikipedia.org/wiki/NUSH)
* [Piccolo](https://iacr.org/workshops/ches/ches2011/presentations/Session%207/CHES2011_Session7_3.pdf)
* [PRESENT](https://en.wikipedia.org/wiki/PRESENT)
* [PRIDE](https://eprint.iacr.org/2014/453.pdf)
* [Prince](https://en.wikipedia.org/wiki/Prince_(cipher))
* [PUFFIN](https://citeseerx.ist.psu.edu/document?repid=rep1&type=pdf&doi=6c1edca0a9800edfb76aca96915ab6f8fcb80cdd)
* [RC2 / ARC2](https://en.wikipedia.org/wiki/RC2)★
* [RC5](https://en.wikipedia.org/wiki/RC5)
* [RECTANGLE](https://csrc.nist.gov/csrc/media/events/lightweight-cryptography-workshop-2015/documents/papers/session8-wentao-paper.pdf)
* [RoadRunneR](https://eprint.iacr.org/2015/906.pdf)
* [SAFER++](http://everything.explained.today/SAFER/)
* [SHARK](https://en.wikipedia.org/wiki/SHARK)
* [Simeck](https://eprint.iacr.org/2015/612.pdf)
* [SKINNY / MANTIS](https://eprint.iacr.org/2016/660.pdf)
* [Skipjack](https://en.wikipedia.org/wiki/Skipjack_(cipher))
* [SPARX](https://www.cryptolux.org/index.php/SPARX)
* [Speck](https://en.wikipedia.org/wiki/Speck_(cipher)) / [Simon](https://en.wikipedia.org/wiki/Simon_(cipher))
* [TEA (Tiny Encryption Algorithm)](https://en.wikipedia.org/wiki/Tiny_Encryption_Algorithm)‡
* [Treyfer](https://en.wikipedia.org/wiki/Treyfer)‡
* [TWINE](https://www.nec.com/en/global/rd/tg/code/symenc/pdf/twine_LC11.pdf)
* [XTEA](https://en.wikipedia.org/wiki/XTEA)‡
* [XXTEA](https://en.wikipedia.org/wiki/XXTEA)‡

Marks:

* ★: popular, widely used cipher
* †: seriously broken
* ‡: somewhat broken

## Recommendation

Use Blowfish. It's fast, well-supported, created and analyzed by experienced cryptographers.

Alternatively:

* If you trust the NSA, consider Speck or Skipjack.
* If you need NIST approval, use 3DES.

## More information

* [Format-preserving encryption](https://en.wikipedia.org/wiki/Format-preserving_encryption#The_FPE_constructions_of_Black_and_Rogaway)
* [Format-transforming encryption](https://en.wikipedia.org/wiki/Format-transforming_encryption)
* [CTR mode](https://en.wikipedia.org/wiki/Block_cipher_mode_of_operation#Counter_.28CTR.29)
* [security - Is it possible to implement AES with a 64-bit I/O block size? - Stack Overflow](https://stackoverflow.com/questions/30485373/is-it-possible-to-implement-aes-with-a-64-bit-i-o-block-size)
