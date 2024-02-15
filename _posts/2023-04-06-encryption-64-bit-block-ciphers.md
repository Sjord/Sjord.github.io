---
layout: post
title: "Block ciphers with a 64 bit block size"
thumbnail: blowfish-480.jpg
date: 2023-08-30
---

There are many 64 bit block ciphers, but few have a good security reputation. Use Blowfish.

<!-- Photo source: https://pixabay.com/nl/photos/blowfish-zee-oceaan-onderwater-2335648/ -->

## Introduction

Generally, you should use AES when encrypting things. It has a minimum block size of 128 bits. This is fine if you want to encrypt 16 bytes or more. However, if you want your ciphertext to be smaller, you need another solution, such as a block cipher with a 64 bit block size. Encrypting a single block gives you a ciphertext of 8 bytes, which is feasible for [use in a URL](/2023/08/02/encrypting-identifiers/), for example.

## Alphabetical list

This is supposed to be an exhaustive list of block ciphers with a 64 bit block size.

| Cipher |   | Author | Year | Notes |
|--------|---|--------|------|-------|
| [ANU-II](https://www.researchgate.net/profile/Vijay-Dahiphale/publication/324487412_ANU-II_A_fast_and_efficient_lightweight_encryption_design_for_security_in_IoT/links/638acc732c563722f2333071/ANU-II-A-fast-and-efficient-lightweight-encryption-design-for-security-in-IoT.pdf) | L[†](https://journals.sagepub.com/doi/full/10.1177/15501329221119398) | Dahiphale, Bansod, Patil | 2017 | So lightweight is hardly provides any security |
| [ANU](https://www.researchgate.net/profile/Narayan-Pisharoty/publication/309467610_ANU_an_ultra_lightweight_cipher_design_for_security_in_IoT_ANU_an_ultra_lightweight_cipher_design/links/5c42b706a6fdccd6b5b7ebc8/ANU-an-ultra-lightweight-cipher-design-for-security-in-IoT-ANU-an-ultra-lightweight-cipher-design.pdf) | L | Bansod, Patil, Sutar, Pisharoty | 2016 | Predecessor to ANU-II |
| [BEST-1](https://iosrjournals.org/iosr-jce/papers/Vol16-issue2/Version-12/N0162129195.pdf) | L | Jacob John | 2014 | Better Encryption Security Technique, so maybe only better and not best? |
| [Blowfish](https://en.wikipedia.org/wiki/Blowfish_(cipher)) | ★ | Bruce Schneier | 1993 | Considered secure, wide software support, not side-channel resistant |
| [BORON](https://link.springer.com/content/pdf/10.1631/FITEE.1500415.pdf) | L | Bansod, Pisharoty, Patil | 2017 | Has withstood [some](https://www.sciencedirect.com/science/article/abs/pii/S2214212622000205) [cryptanalysis](https://link.springer.com/content/pdf/10.1631/FITEE.1500415.pdf) |
| [CAST-128 / CAST5](https://en.wikipedia.org/wiki/CAST-128)| ★ | Adams & Tavares | 1996 | Used in GPG |
| [CHAM](https://link.springer.com/chapter/10.1007/978-3-030-40921-0_1) | L | Roh et al. | 2019 | Revised after weaknesses found by cryptanalysis |
| [CIKS-1](https://en.wikipedia.org/wiki/CIKS-1) | | Moldovyan et al. | 2002 | Data-dependent permutations, fast in hardware |
| [CIPHERUNICORN-E](https://en.wikipedia.org/wiki/CIPHERUNICORN-E) | | NEC | 1998 | [CRYPTREC](https://en.wikipedia.org/wiki/CRYPTREC) candidate |
| [COCONUT98](https://en.wikipedia.org/wiki/COCONUT98) | ‡ | Vaudenay | 1998 | Uses Vaudenay's decorrelation theory. Proven secure, but broken nevertheless |
| [CRAFT](https://eprint.iacr.org/2019/210) | H[‡](https://eprint.iacr.org/2019/932.pdf) | Beierle et al. | 2019 | Protects against physical attacks, such as differential fault injection |
| [CRAX](https://sparkle-lwc.github.io/crax) | L | Beierle et al. | 2020 | Efficient in software, no key schedule |
| [Cryptomeria / C2](https://en.wikipedia.org/wiki/Cryptomeria_cipher) | ‡ | 4C Entity | 2003 | Successor to CSS for DRM on DVDs |
| [CS-Cipher](https://en.wikipedia.org/wiki/CS-Cipher) | | Stern & Vaudenay | 1998 | Uses FFT in the round function. |
| [DABC](https://itiis.org/digital-library/manuscript/file/38318/TIIS%20Vol%2017,%20No%201-9.pdf) | L | Chen, Li, Guo | 2023 | ARX based with high diffusion |
| [DES](https://en.wikipedia.org/wiki/Data_Encryption_Standard), [3DES](https://en.wikipedia.org/wiki/Triple_DES), [DES-X](https://en.wikipedia.org/wiki/DES-X) | ★ | IBM | 1975 | Outdated but still reasonably secure, as long as used with a sufficiently long key. Wide software support and often used for NIST compliance. |
| [DULBC](https://www.sciencedirect.com/science/article/abs/pii/S0167926022000931) | L | Yang, Li, Guo, Huang | 2022 | Uses one of four different round functions depending on the key |
| [FEAL](https://en.wikipedia.org/wiki/FEAL) | † | Shimizu & Miyaguchi | 1987 | Practical attacks were quickly found, even after the authors increased the number of rounds. |
| [FeW](https://dergipark.org.tr/en/download/article-file/914382) | | Kumar, Pal, Panigrahi | 2018 | Feistel-M structure, elaborate security analysis in original paper |
| [FUTURE](https://www.researchgate.net/profile/Susanta-Samanta-4/publication/364204004_FUTURE_A_Lightweight_Block_Cipher_Using_an_Optimal_Diffusion_Matrix/links/64e09fbf177c59041304d95f/FUTURE-A-Lightweight-Block-Cipher-Using-an-Optimal-Diffusion-Matrix.pdf) | L | Gupta, Pandey, Samanta | 2022 | Encrypts data in a single clock cycle by using an unrolled implementation |
| [GOST (Magma)](https://en.wikipedia.org/wiki/GOST_(block_cipher)) | ‡ | USSR | ~1970 | Declassified in 1994. |
| [Halka](https://eprint.iacr.org/2014/110.pdf) | L | Das | 2014 | 80-bit keys. Claims to be small in hardware, fast in software. Multiplicative inverse for 8-bit S-boxes. |
| [Hierocrypt-L1](https://en.wikipedia.org/wiki/Hierocrypt) | | Toshiba | 2000 | [CRYPTREC](https://en.wikipedia.org/wiki/CRYPTREC) candidate |
| [HIGHT](https://www.iacr.org/archive/ches2006/04/04.pdf) | ‡L | Hong et al. | 2006 | Has received some analysis and improvements |
| [Hisec](/papers/2014/hisec-a-new-lightweight-block-cipher-algorithm.pdf) | | AlDabbagh et al. | 2014 | Feistel-like with 80 bit key |
| [ICE](https://en.wikipedia.org/wiki/ICE_(cipher)) | | Kwan | 1997 | Similar to DES |
| [ICEBERG](https://iacr.org/archive/fse2004/30170280/30170280.pdf) | H | Standaert et al. | 2004 | Designed for FPGAs. Involutional; encryption and decryption use the same algorithm, but a different internal key |
| [IDEA NXT](https://en.wikipedia.org/wiki/IDEA_NXT) | | Junod & Vaudenay | 2003 | Successor to IDEA |
| [IDEA](https://en.wikipedia.org/wiki/International_Data_Encryption_Algorithm) | ★ | Lai and Massey | 1991 | International Data Encryption Algorithm |
| [KASUMI](https://en.wikipedia.org/wiki/KASUMI) | † | Mitsubishi | 1998 | A variation of MISTY1 modified for mobile phone networks. |
| [KATAN64](https://www.iacr.org/archive/ches2009/57470273/57470273.pdf) / [KTANTAN64](https://www.iacr.org/archive/ches2009/57470273/57470273.pdf) | ‡H | De Cannière, Dunkelman & Knežević | 2009 | Efficient hardware oriented cipher.
| [KHAZAD](https://en.wikipedia.org/wiki/KHAZAD) | | Rijmen & Barreto | 2000 | NESSIE finalist. Involutional subcomponents |
| [Khufu / Khafre](https://en.wikipedia.org/wiki/Khufu_and_Khafre) | † | Merkle | 1989 | Leaked by a reviewer after the NSA asked Xerox not to publish it |
| [KLEIN](https://ris.utwente.nl/ws/portalfiles/portal/5095833/The_KLEIN_Block_Cipher.pdf) | L | Gong et al. | 2010 | Key length at most 96 bits |
| [KN-Cipher](https://en.wikipedia.org/wiki/KN-Cipher) | † | Nyberg & Knudsen | 1995 | Prototype, provably secure against differential cryptanalysis, but evenso broken by differential cryptanalysis |
| [LBlock](https://eprint.iacr.org/2011/345.pdf) | ‡L | Wu & Zhang | 2011 | Key size of 80 bits |
| [LED](https://eprint.iacr.org/2012/600.pdf) | HL | Guo, Peyrin, Poschmann, Robshaw | 2011 | No key schedule, protects against related-key attacks |
| [LiCi](https://ieeexplore.ieee.org/abstract/document/7977007) | L | Patil, Bansod, Kant | 2017 | Feistel network with 31 rounds |
| [Lilliput](/papers/2015/extended-generalized-feistel-networks-using-matrix-representation-to-propose-a-new-lightweight-block-cipher-lilliput.pdf) | L | Berger et al. | 2015 | Explores matrix representation of Feistel networks |
| [LOKI89/91](https://en.wikipedia.org/wiki/LOKI) | ‡ | Brown, Pieprzyk & Seberry | 1990 | Similar to DES, not recommended for production use |
| [M6](https://en.wikipedia.org/wiki/M6_(cipher)) | † | Hitachi | 1997 | Designed for FireWire. Key of up to 64 bits. Algorithm not fully published. |
| [M8](https://en.wikipedia.org/wiki/M8_(cipher)) | | Hitachi | 1999 | Similar to M6, but more complicated and with longer keys |
| [MacGuffin](https://en.wikipedia.org/wiki/MacGuffin_(cipher)) | † | Schneier & Blaze | 1994 | Broken during the same workshop in which it was designed |
| [MANTIS](https://eprint.iacr.org/2016/660.pdf) | T[‡](https://tosc.iacr.org/article/view/573) | Beierle et al. | 2016 | Low latency |
| [mCrypton](/papers/2006/mcrypton-a-lightweight-block-cipher-for-security-of-low-cost-rfid-tags-and-sensors.pdf) | ‡LH | | 2006 | Designed for RFID chips |
| [MESH](https://en.wikipedia.org/wiki/MESH_(cipher)) | | Nakahara, Rijmen, Preneel, Vandewalle | 2002 | Similar to IDEA |
| [MIBS](papers/2009/mibs-a-new-lightweight-block-cipher.pdf) | ‡ | Izadi, Sadeghiyan et al. | 2009 | 80 bit keys |
| [Midori](https://eprint.iacr.org/2015/1142.pdf) | L | Banik et al. | 2015 | Designed for low energy use |
| [MISTY1](https://en.wikipedia.org/wiki/MISTY1) | ‡ | Matsui | 1997 | [NESSIE](https://en.wikipedia.org/wiki/NESSIE) selected, [CRYPTREC](https://en.wikipedia.org/wiki/CRYPTREC) candidate |
| [MULTI2](https://en.wikipedia.org/wiki/MULTI2) | ‡ | Hitachi | 1988 | Key size of 64 bits. Used for TV enryption in Japan. |
| [MultiSwap](https://en.wikipedia.org/wiki/MultiSwap) | † | Microsoft | 1999 | Designed for DRM in Windows |
| [NewDES](https://en.wikipedia.org/wiki/NewDES) | ‡ | Scott | 1985 | Author admitted later that he "did not know much about cryptography back then", and "that NEWDES is not very good" |
| [Nimbus](https://en.wikipedia.org/wiki/Nimbus_(cipher)) | † | Alexis Machado | 2000 | Simple round function. |
| [NLBSIT](https://www.researchgate.net/profile/Abdulrazzaq-Al-Ahdal/publication/345740906_NLBSIT_A_New_Lightweight_Block_Cipher_Design_for_Securing_Data_in_IoT_Devices/links/5fac333245851507810ca7e7/NLBSIT-A-New-Lightweight-Block-Cipher-Design-for-Securing-Data-in-IoT-Devices.pdf) | L | Al-Ahdal, Al-Rummana, Shinde, Deskmukh | 2020 | 64 bit key |
| [NUSH](https://en.wikipedia.org/wiki/NUSH) | ‡ | Lebedev & Volchkov | 2000 | Designed for the Russian company LAN Crypto |
| [Piccolo](https://iacr.org/workshops/ches/ches2011/presentations/Session%207/CHES2011_Session7_3.pdf) | HL | Shibutani et al. | 2011 | From Sony, protects against related-key attacks |
| [PRESENT-GRP](https://www.emerald.com/insight/content/doi/10.1016/j.aci.2018.05.001/full/pdf) | HL | Thorat & Inamdar | 2018 | Variant of PRESENT, with grouping permutations |
| [PRESENT](https://en.wikipedia.org/wiki/PRESENT) | HL | Bogdanov, Knudsen, Leander, Paar, Poschmann, Robshaw, Seurin, Vikkelsoe | 2007 | Designed by cooperation of European universities and companies, ISO-standardized. Well-studied, and often used as benchmark in cipher research |
| [PRIDE](https://eprint.iacr.org/2014/453.pdf) | L | Albrecht et al. | 2014 | Focusses on the linear layer of the cipher. Fast in software |
| [Prince](https://en.wikipedia.org/wiki/Prince_(cipher)) | HL | Borghoff et al. | 2012 | Involation, which they call alpha reflection |
| [PUFFIN](https://citeseerx.ist.psu.edu/document?repid=rep1&type=pdf&doi=6c1edca0a9800edfb76aca96915ab6f8fcb80cdd) | ‡HL | Cheng, Heys, Wang| 2008 | Involutional subcomponents |
| [QARMA](https://en.wikipedia.org/wiki/QARMA), [V2](https://eprint.iacr.org/2023/929) | HT | Avanzi | 2017 | Used in ARMv8 CPUs |
| [QTL](https://www.sciencedirect.com/science/article/abs/pii/S0141933116300151) | L[‡](https://link.springer.com/chapter/10.1007/978-3-319-55714-4_5) | Li, Liu, Wang | 2016 | No key schedule, Feistel variant |
| [RAMus](https://lirias.kuleuven.be/retrieve/692405) | LT | Posteuca & Rijmen | 2022 | Designed to encrypt RAM |
| [RC2 / ARC2](https://en.wikipedia.org/wiki/RC2) | ★‡ | Rivest | 1987 | Developed for use in Lotus Notes. |
| [RC5](https://en.wikipedia.org/wiki/RC5) | | Rivest | 1994 | Complex key schedule, simple encryption/decryption algorithm |
| [RECTANGLE](https://csrc.nist.gov/csrc/media/events/lightweight-cryptography-workshop-2015/documents/papers/session8-wentao-paper.pdf) | L | Zhang et al. | 2015 | Uses bit slicing |
| [Red Pike](https://en.wikipedia.org/wiki/Red_Pike_(cipher)) | | GCHQ | ~1990 | Classified UK cipher |
| [RoadRunneR](https://eprint.iacr.org/2015/906.pdf) | L | Baysal & Şahin | 2016 | Provable 8-bit security, efficient on ATtiny45, introduces unique ST/A metric for fair comparison. |
| [SAFER](https://en.wikipedia.org/wiki/SAFER) | | Massey et al. | 2000 | From Cylink Corporation. Various variants available. |
| [SAT_Jo](https://ieeexplore.ieee.org/abstract/document/8663068) | [‡](https://www.hindawi.com/journals/scn/2021/5310545/) | Joshitta & Arockiam | 2018 | 80 bits key. Similar to PRESENT, but less secure |
| [SHARK](https://en.wikipedia.org/wiki/SHARK) | | Rijmen et al. | 1996 | a predecessor of AES. |
| [Simeck](https://eprint.iacr.org/2015/612.pdf) | L | Yang et al. | 2015 | Based on Simon/Speck |
| [SKINNY](https://eprint.iacr.org/2016/660.pdf) | T | Beierle et al. | 2016 | Claims to be better than Simon |
| [Skipjack](https://en.wikipedia.org/wiki/Skipjack_(cipher)) | | NSA | 1998 | Small key size of 80 bits. Intended for use in the controversial Clipper chip. |
| [SPARX](https://www.cryptolux.org/index.php/SPARX) | L | Dinu et al. | 2016 | Design strategy with provable security |
| [Speck](https://en.wikipedia.org/wiki/Speck_(cipher)) / [Simon](https://en.wikipedia.org/wiki/Simon_(cipher)) | | NSA | 2013 | Promising cipher, well analyzed, but designed by the NSA |
| [Spectr-H64](https://en.wikipedia.org/wiki/Spectr-H64) | † | Moldovyan et al. | 2001 | Predecessor of CIKS-1 |
| [SPEED](http://target0.be/madchat/crypto/hash-lib-algo/speed/speed-paper.pdf) | ‡ | Yuliang Zheng | 1997 | Inspired by RC5, uses non-lineair Boolean operations |
| [SPNRX](https://www.researchsquare.com/article/rs-2033728/v1) | L | Wang, Zhao, Chen | 2022 | Mix of SPN and ARX |
| [SXAL](https://en.wikipedia.org/wiki/SXAL/MBAL) | ‡ | Laurel Intelligent Systems | 1993 | Part of MBAL, used in Japanese smart cards |
| [TEA](https://en.wikipedia.org/wiki/Tiny_Encryption_Algorithm) | ‡ | Needham & Wheeler | 1994 | Tiny Encryption Algorithm. Vulnerable to related-key attacks. Improved with XTEA and XXTEA. |
| [Treyfer](https://en.wikipedia.org/wiki/Treyfer) | ‡ | Gideon Yuval | 1997 | Key size of 64 bits, extremely simple algorithm |
| [TWINE](https://www.nec.com/en/global/rd/tg/code/symenc/pdf/twine_LC11.pdf) | L | NEC | 2011 | Tries to be fast in both hardware and software |
| [ULC](https://hal.science/hal-03453089/document) | L[‡](https://www.hindawi.com/journals/scn/2022/4291000/) | Sliman et al. | 2021 | 80 bit key |
| [XTEA](https://en.wikipedia.org/wiki/XTEA) | ‡ | Needham & Wheeler | 1997 | Based on TEA |
| [XXTEA](https://en.wikipedia.org/wiki/XXTEA) | ‡ | Needham & Wheeler | 1998 | Based on TEA |
| [µ²](https://www.researchgate.net/profile/Je-Sen-Teh/publication/335471871_2_A_Lightweight_Block_Cipher/links/5d6dcee092851c85388891a1/2-A-Lightweight-Block-Cipher.pdf) | L | Yeoh, Teh, Sazali | 2019 | 80 bit key Feistel variant |

Marks:

* ★: popular, widely used cipher
* †: seriously broken, practical attack
* ‡: somewhat broken, impractical attack
* T: tweakable
* L: claims to be lightweight
* H: meant for hardware implementation

## Honorouble mention

[Ascon](https://en.wikipedia.org/wiki/Ascon_(cipher)) is a lightweight authenticated block cipher with a block size of 64 bits. However, it is more similar to a stream cipher than a pseudorandom permutation. It's only secure when used with an IV, and its output contains an authentication tag. A great cipher, but not suitable for creating 64-bit ciphertexts.

## Discussion

Judging from the list, there are sufficient ciphers to choose from. However, few to none have the same universally acclaimed security reputation as AES. AES's rigorous evaluation and selection process have positioned it as the gold standard for 128-bit block ciphers, but there is no 64-bit block cipher with a similar prestige. MISTY1 was the NESSIE winner, but didn't hold up to further cryptanalysis since then. CRYPTREC recognized that none of these ciphers have similar security and popularity as AES, and stopped recommending 64-bit block ciphers altogether. 

Interestingly, some ciphers within the above list were initially hailed as "provably" secure solutions, yet fell victim to the evolution of cryptanalysis techniques. It shows how difficult it is to show that a certain cipher is actually secure. However, increasingly this burden is placed on the designers of the cipher. Ciphers that shuffled enough bits around would be considered secure, as long as someone analyzed them and didn't find a practical attack. Now the burden of proof is on the designer, and when a cipher is proposed it is expected that it comes with a security analysis.

## Recommendation

Use Blowfish. It's fast, well-supported, created and analyzed by experienced cryptographers. However, it is not secure against timing attacks or other side-channel attacks.

Alternatively:

* If you trust the NSA, consider Speck.
* If you need NIST approval, use 3DES.

## More information

* [A review of lightweight block ciphers](https://www.fysarakis.com/uploads/2/0/6/3/20637656/BC_survey.pdf)
* [Lightweight Cryptography Algorithms for Resource-Constrained IoT Devices: A Review, Comparison and Research Opportunities](https://ieeexplore.ieee.org/document/9328432)
* [Format-preserving encryption](https://en.wikipedia.org/wiki/Format-preserving_encryption#The_FPE_constructions_of_Black_and_Rogaway)
* [Format-transforming encryption](https://en.wikipedia.org/wiki/Format-transforming_encryption)
* [CTR mode](https://en.wikipedia.org/wiki/Block_cipher_mode_of_operation#Counter_.28CTR.29)
* [security - Is it possible to implement AES with a 64-bit I/O block size? - Stack Overflow](https://stackoverflow.com/questions/30485373/is-it-possible-to-implement-aes-with-a-64-bit-i-o-block-size)
