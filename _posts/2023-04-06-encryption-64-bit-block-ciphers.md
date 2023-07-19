---
layout: post
title: "Block ciphers with a 64 bit block size"
thumbnail: blowfish-480.jpg
date: 2023-04-26
---

Use Blowfish.

<!-- Photo source: https://pixabay.com/nl/photos/blowfish-zee-oceaan-onderwater-2335648/ -->

## Introduction

Generally, you should use AES when encrypting things. It has a block size of 128 bits. This is fine if you want to encrypt 16 bytes or more. However, if you want your ciphertext to be smaller, you need another solution, such as a block cipher with a 64 bit block size. Encrypting a single block gives you a ciphertext of 8 bytes, which is feasible for use in a URL, for example.

## Alphabetical list

This is supposed to be an exhaustive list of block ciphers with a 64 bit block size.

| Cipher |   | Author | Year | Notes |
|--------|---|--------|------|-------|
| [Blowfish](https://en.wikipedia.org/wiki/Blowfish_(cipher)) | ★ | Bruce Schneier | 1993 | Considered secure, wide software support |
| [CAST-128 / CAST5](https://en.wikipedia.org/wiki/CAST-128)| ★ | Adams & Tavares | 1996 | Used in GPG |
| [CIKS-1](https://en.wikipedia.org/wiki/CIKS-1) | | Moldovyan et al. | 2002 | |
| [CIPHERUNICORN-E](https://en.wikipedia.org/wiki/CIPHERUNICORN-E) | | NEC | 1998 | [CRYPTREC](https://en.wikipedia.org/wiki/CRYPTREC) candidate |
| [COCONUT98](https://en.wikipedia.org/wiki/COCONUT98) | ‡ | Vaudenay | 1998 | |
| [CRAFT](https://eprint.iacr.org/2019/210) | T | Beierle et al. | 2019 | |
| [Cryptomeria / C2](https://en.wikipedia.org/wiki/Cryptomeria_cipher) | ‡ | 4C Entity | 2003 | |
| [CS-Cipher](https://en.wikipedia.org/wiki/CS-Cipher) | | Stern & Vaudenay | 1998 | |
| [DES](https://en.wikipedia.org/wiki/Data_Encryption_Standard), [3DES](https://en.wikipedia.org/wiki/Triple_DES), [DES-X](https://en.wikipedia.org/wiki/DES-X) | ★ | IBM | 1975 | Outdated but still reasonably secure, as long as used with a sufficiently long key. Wide software support and often used for NIST compliance. |
| [FEAL](https://en.wikipedia.org/wiki/FEAL) | † | Shimizu & Miyaguchi | 1987 | Practical attacks were quickly found, even after the authors increased the number of rounds. |
| [GOST (Magma)](https://en.wikipedia.org/wiki/GOST_(block_cipher)) | ‡ | USSR | ~1970 | Declassified in 1994. |
| [Hierocrypt-L1](https://en.wikipedia.org/wiki/Hierocrypt) | | Toshiba | 2000 | [CRYPTREC](https://en.wikipedia.org/wiki/CRYPTREC) candidate |
| [HIGHT](https://www.iacr.org/archive/ches2006/04/04.pdf) | | Hong et al. | 2006 | |
| [ICE](https://en.wikipedia.org/wiki/ICE_(cipher)) | | Kwan | 1997 | |
| [ICEBERG](https://iacr.org/archive/fse2004/30170280/30170280.pdf) | | | | |
| [IDEA NXT](https://en.wikipedia.org/wiki/IDEA_NXT) | | Junod & Vaudenay | 2003 | |
| [IDEA](https://en.wikipedia.org/wiki/International_Data_Encryption_Algorithm) | ★ | Lai and Massey | 1991 | International Data Encryption Algorithm |
| [KASUMI](https://en.wikipedia.org/wiki/KASUMI) | † | Mitsubishi | 1998 | A variation of MISTY1 modified for mobile phone networks. |
| [KATAN64](https://www.iacr.org/archive/ches2009/57470273/57470273.pdf) / [KTANTAN64](https://www.iacr.org/archive/ches2009/57470273/57470273.pdf) | | De Cannière, Dunkelman & Knežević | 2009 | Efficient hardware oriented cipher.
| [KHAZAD](https://en.wikipedia.org/wiki/KHAZAD) | | Rijmen & Barreto | 2000 | |
| [Khufu / Khafre](https://en.wikipedia.org/wiki/Khufu_and_Khafre) | † | Merkle | 1989 |
| [KLEIN](https://ris.utwente.nl/ws/portalfiles/portal/5095833/The_KLEIN_Block_Cipher.pdf) | | Gong et al. | 2010 | |
| [KN-Cipher](https://en.wikipedia.org/wiki/KN-Cipher) | † | Nyberg & Knudsen | 1995 | |
| [LBlock](https://eprint.iacr.org/2011/345.pdf) | | Wu & Zhang | 2011 | |
| [LED](https://eprint.iacr.org/2012/600.pdf) | | Guo, Peyrin, Poschmann, Robshaw | 2011 | |
| [Lilliput](/papers/2015/extended-generalized-feistel-networks-using-matrix-representation-to-propose-a-new-lightweight-block-cipher-lilliput.pdf) | | | 2015 | |
| [LOKI89/91](https://en.wikipedia.org/wiki/LOKI) | ‡ | Brown, Pieprzyk & Seberry | 1990 |
| [M6](https://en.wikipedia.org/wiki/M6_(cipher)) | † | Hitachi | 1997 | |
| [M8](https://en.wikipedia.org/wiki/M8_(cipher)) | | Hitachi | 1999 | |
| [MacGuffin](https://en.wikipedia.org/wiki/MacGuffin_(cipher)) | † | Schneier & Blaze | 1994 | |
| [mCrypton](/papers/2006/mcrypton-a-lightweight-block-cipher-for-security-of-low-cost-rfid-tags-and-sensors.pdf) | | | 2006 | |
| [MESH](https://en.wikipedia.org/wiki/MESH_(cipher)) | | Nakahara, Rijmen, Preneel, Vandewalle | 2002 | |
| [Midori](https://eprint.iacr.org/2015/1142.pdf) | | Banik et al. | 2015 | |
| [MISTY1](https://en.wikipedia.org/wiki/MISTY1) | ‡ | Matsui | 1997 | [NESSIE](https://en.wikipedia.org/wiki/NESSIE) selected, [CRYPTREC](https://en.wikipedia.org/wiki/CRYPTREC) candidate |
| [MULTI2](https://en.wikipedia.org/wiki/MULTI2) | ‡ | Hitachi | 1988 | |
| [MultiSwap](https://en.wikipedia.org/wiki/MultiSwap) | † | Microsoft | 1999 | |
| [NewDES](https://en.wikipedia.org/wiki/NewDES) | | Scott | 1985 | |
| [Nimbus](https://en.wikipedia.org/wiki/Nimbus_(cipher)) | † | Alexis Machado | 2000 | |
| [NUSH](https://en.wikipedia.org/wiki/NUSH) | ‡ | Lebedev & Volchkov | 2000 | |
| [Piccolo](https://iacr.org/workshops/ches/ches2011/presentations/Session%207/CHES2011_Session7_3.pdf) | | Shibutani et al. | 2011 | |
| [PRESENT](https://en.wikipedia.org/wiki/PRESENT) | | Bogdanov, Knudsen, Leander, Paar, Poschmann, Robshaw, Seurin, Vikkelsoe | 2007 | |
| [PRIDE](https://eprint.iacr.org/2014/453.pdf) | | Albrecht et al. | 2014 | |
| [Prince](https://en.wikipedia.org/wiki/Prince_(cipher)) | | Borghoff et al. | 2012 | |
| [PUFFIN](https://citeseerx.ist.psu.edu/document?repid=rep1&type=pdf&doi=6c1edca0a9800edfb76aca96915ab6f8fcb80cdd) | | | | |
| [QARMA](https://en.wikipedia.org/wiki/QARMA), [V2](https://eprint.iacr.org/2023/929) | T | Avanzi | 2017 | |
| [RC2 / ARC2](https://en.wikipedia.org/wiki/RC2) | ★ | Rivest | 1987 | |
| [RC5](https://en.wikipedia.org/wiki/RC5) | | Rivest | 1994 | |
| [RECTANGLE](https://csrc.nist.gov/csrc/media/events/lightweight-cryptography-workshop-2015/documents/papers/session8-wentao-paper.pdf) | | Zhang et al. | 2015 | |
| [Red Pike](https://en.wikipedia.org/wiki/Red_Pike_(cipher)) | | GCHQ | ~1990 | |
| [RoadRunneR](https://eprint.iacr.org/2015/906.pdf) | | | | |
| [SAFER](https://en.wikipedia.org/wiki/SAFER) | | Massey et al. | 2000 | |
| [SHARK](https://en.wikipedia.org/wiki/SHARK) | | Rijmen et al. | 1996 | a predecessor of AES. |
| [Simeck](https://eprint.iacr.org/2015/612.pdf) | | Yang et al. | 2015 | |
| [SKINNY / MANTIS](https://eprint.iacr.org/2016/660.pdf) | T | Beierle et al. | 2016 | |
| [Skipjack](https://en.wikipedia.org/wiki/Skipjack_(cipher)) | | NSA | 1998 | Small key size of 80 bits. Intended for use in the controversial Clipper chip. |
| [SPARX](https://www.cryptolux.org/index.php/SPARX) | | Dinu et al. | 2016 | |
| [Speck](https://en.wikipedia.org/wiki/Speck_(cipher)) / [Simon](https://en.wikipedia.org/wiki/Simon_(cipher)) | | NSA | 2013 | |
| [Spectr-H64](https://en.wikipedia.org/wiki/Spectr-H64) | † | Moldovyan et al. | 2001 | |
| [SPEED](http://target0.be/madchat/crypto/hash-lib-algo/speed/speed-paper.pdf) | ‡ | Yuliang Zheng | 1997 | |
| [SXAL](https://en.wikipedia.org/wiki/SXAL/MBAL) | ‡ | Laurel Intelligent Systems | 1993 | |
| [TEA](https://en.wikipedia.org/wiki/Tiny_Encryption_Algorithm) | ‡ | Needham & Wheeler | 1994 | Tiny Encryption Algorithm. Vulnerable to related-key attacks. Improved with XTEA and XXTEA. |
| [Treyfer](https://en.wikipedia.org/wiki/Treyfer) | ‡ | Gideon Yuval | 1997 | |
| [TWINE](https://www.nec.com/en/global/rd/tg/code/symenc/pdf/twine_LC11.pdf) | | NEC | 2011 | |
| [XTEA](https://en.wikipedia.org/wiki/XTEA) | ‡ | Needham & Wheeler | 1997 | |
| [XXTEA](https://en.wikipedia.org/wiki/XXTEA) | ‡ | Needham & Wheeler | 1998 | |

Marks:

* ★: popular, widely used cipher
* †: seriously broken, practical attack
* ‡: somewhat broken, impractical attack
* T: tweakable

## Recommendation

Use Blowfish. It's fast, well-supported, created and analyzed by experienced cryptographers.

Alternatively:

* If you trust the NSA, consider Speck.
* If you need NIST approval, use 3DES.

## More information

* [Format-preserving encryption](https://en.wikipedia.org/wiki/Format-preserving_encryption#The_FPE_constructions_of_Black_and_Rogaway)
* [Format-transforming encryption](https://en.wikipedia.org/wiki/Format-transforming_encryption)
* [CTR mode](https://en.wikipedia.org/wiki/Block_cipher_mode_of_operation#Counter_.28CTR.29)
* [security - Is it possible to implement AES with a 64-bit I/O block size? - Stack Overflow](https://stackoverflow.com/questions/30485373/is-it-possible-to-implement-aes-with-a-64-bit-i-o-block-size)
