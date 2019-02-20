---
layout: post
title: "Attacking RSA"
thumbnail: drilling-safe-480.jpg
date: 2019-06-19
---


## Single key weaknesses

### Modulus too small

If the RSA key is too short, the modulus can be factored by just using brute force. A 256-bit modulus can be factored in a couple of minutes. A 512-bit modulus takes several weeks on modern consumer hardware. Factoring 1024-bit keys is definitely not possible in a reasonable time with reasonable means, but may be possible for well equiped attackers. 2048-bit is secure against brute force factoring.

Demo: smallkey.pem

### Low private exponent

Decrypting a message consists of calculating _c<sup>d</sup>_ mod _N_. The smaller _d_ is, the faster this operation goes. However, [Wiener](http://www.reverse-engineering.info/Cryptography/ShortSecretExponents.pdf) found a way to recover _d_ (and thus the private key). [Boneh and Durfee](http://antoanthongtin.vn/Portals/0/UploadImages/kiennt2/KyYeu/DuLieuNuocNgoai/8.Advances%20in%20cryptology-Eurocrypt%201999-LNCS%201592/15920001.pdf) improved this attack to recover private exponents that are less than _N_<sup>0.292</sup>.

Demo: smalld.pem

### Low public exponent

Encrypting is performed by calculating _m<sup>e</sup>_ mod _N_. Here _e_ is a low number that is relative prime to N. A common choice is _e_ = 3. Having a low public exponent makes the system vulnerable to certain attacks if used incorrectly. If the same message is encrypted by three seperate keys, the security breaks and the message can be recovered. However, RSA should only be used with randomized padding which prevents this and related attacks. With proper padding it is totally fine to use a low public exponent, so this is not really a vulnerability.

### _p_ and _q_ close together

When creating the key, two random primes _p_ and _q_ are multiplied. Consider what happens when _p_ ≈ _q_. Then _N_ ≈ _p_<sup>2</sup> or _p_ ≈ √_N_. In that case, N can be efficiently factored using [Fermat's factorization method](https://en.wikipedia.org/wiki/Fermat%27s_factorization_method).

Demo: closepq.pem

### Unsafe primes

When multiplying two primes, the result is almost always hard to factor. However, it turns out that if at least one of the primes conforms to certain conditions there is a shortcut to factor the result.

* Pollard's p − 1 algorithm: _p_ - 1 is powersmooth.
* Williams's p + 1 algorithm: _p_ + 1 is smooth.
* Cheng's elliptic curve algorithm: _p_ − 1 has the form db<sup>2</sup> where d ∈ {3,11,19,43,67,163}

Demo: williams.pem

### May 2008 Debian OpenSSH bug

To create a random key, a good cryptographic random number generator is required. Between 2006 and 2008 Debian had a [bug](https://www.debian.org/security/2008/dsa-1571) where the random number generator was improperly seeded, resulting in predictable keys.

Demo: debian.pub

### e = 1

Encryption is done by calculting _m<sup>e</sup>_. If _e_ = 1, this operation does nothing and the message is not encrypted.

Demo: eone.pem

### Prime N

Normally, two primes compose the modulus. However, you could also use three primes, or one prime. In the case of one prime, however, the private key is not very secret.

Demo: nprime.pem

## Relations between multiple keys

### Shared N

Demo: sharedn1.pem, sharedn2.pem

### Shared p or q

Devices that create their private key on first boot may not have enough entropy to create a random number. What sometimes happens is that this results in keys that have a different modulus but have one factor in common. In that case the greatest common divisor of the two moduli can be efficiently calculated to factor the modulus and recover the private key.

Demo: sharedp1.pem, sharedp2.pem


* Shared private key



### Read more

* [Exploring 3 insecure usage of RSA](https://www.quaxio.com/exploring_three_weaknesses_in_rsa/)
* [Possible Attacks on RSA](http://www.members.tripod.com/irish_ronan/rsa/attacks.html)
* [Understanding Common Factor Attacks: An RSA-Cracking Puzzle](http://www.loyalty.org/~schoen/rsa/)
* [Twenty Years of Attacks on the RSA Cryptosystem](http://crypto.stanford.edu/~dabo/papers/RSA-survey.pdf)
* [Finding Duplicate RSA Moduli in the Wild](http://sbudella.altervista.org/blog/20181211-duplicate-moduli.html)
* [Mathematical attack on RSA](https://www.nku.edu/~christensen/Mathematical%20attack%20on%20RSA.pdf)
* [A New Class of Unsafe Primes](https://eprint.iacr.org/2002/109.pdf)
* [RsaCtfTool](https://github.com/Ganapati/RsaCtfTool)
* [Ron was wrong, Whit is right](https://eprint.iacr.org/2012/064.pdf)
* [How I recovered your private key or why small keys are bad](https://0day.work/how-i-recovered-your-private-key-or-why-small-keys-are-bad/)