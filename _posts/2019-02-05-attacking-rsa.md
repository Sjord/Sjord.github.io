---
layout: post
title: "Attacking RSA keys"
thumbnail: drilling-safe-480.jpg
date: 2019-06-19
---

RSA keys need to conform to certain mathematical properties in order to be secure. If the key is not generated carefully it can have vulnerabilities which may totally compromise the encryption algorithm. Sometimes this can be determined from the public key alone. This article describes vulnerabilities that can be tested when in possession a RSA public key.

<!-- photo source: https://www.kadena.af.mil/News/Article-Display/Article/417628/18th-ces-holds-keys-to-kadena/ -->

## Single key weaknesses

If you have a single public key, there are already several things you can test to determine whether the key is secure.

### Modulus too small

If the RSA key is too short, the modulus can be factored by just using brute force. A 256-bit modulus can be factored in a couple of minutes. A 512-bit modulus takes several weeks on modern consumer hardware. Factoring 1024-bit keys is definitely not possible in a reasonable time with reasonable means, but may be possible for well equiped attackers. 2048-bit is secure against brute force factoring.

Demo: smallkey.pem

### Low private exponent

Decrypting a message consists of calculating _c<sup>d</sup>_ mod _N_. The smaller _d_ is, the faster this operation goes. However, [Wiener](http://www.reverse-engineering.info/Cryptography/ShortSecretExponents.pdf) found a way to recover _d_ (and thus the private key) when _d_ is relatively small. [Boneh and Durfee](http://antoanthongtin.vn/Portals/0/UploadImages/kiennt2/KyYeu/DuLieuNuocNgoai/8.Advances%20in%20cryptology-Eurocrypt%201999-LNCS%201592/15920001.pdf) improved this attack to recover private exponents that are less than _N_<sup>0.292</sup>.

If the private exponent is small, the public exponent is [necessarily large](https://crypto.stackexchange.com/questions/67426/can-you-recognize-a-low-private-exponent-from-a-public-key/67432#67432). If you encounter a public key with a large public exponent, it may be worth it to run the Boneh-Durfee attack against it.

Demo: smalld.pem

### Low public exponent

Encrypting is performed by calculating _m<sup>e</sup>_ mod _N_. Here _e_ is a low number that is relative prime to _N_. A common choice is _e_ = 3. Having a low public exponent makes the system vulnerable to certain attacks if used incorrectly. If the same message is encrypted by three seperate keys, the security breaks and the message can be recovered. However, RSA should only be used with randomized padding which prevents this and related attacks. With proper padding it is totally fine to use a low public exponent, so this is not really a vulnerability.

### _p_ and _q_ close together

When creating the key, two random primes _p_ and _q_ are multiplied. Consider what happens when _p_ ≈ _q_. Then _N_ ≈ _p_<sup>2</sup> or _p_ ≈ √_N_. In that case, N can be efficiently factored using [Fermat's factorization method](https://en.wikipedia.org/wiki/Fermat%27s_factorization_method).

Fermat's algorithm searches for an a and b such that _N_ = _a_<sup>2</sup> - _b_<sup>2</sup>. In that case, _N_ is factorable as (_a_ + _b_)(_a_ - _b_). If _p_ and _q_ are close together, _a_ is close to √_N_ and _b_ is small, making them easy to find.

Demo: closepq.pem

### Unsafe primes

When multiplying two primes, the result is almost always hard to factor. However, it turns out that if at least one of the primes conforms to certain conditions there is a shortcut to factor the result.

* [Pollard's p − 1 algorithm](https://en.wikipedia.org/wiki/Pollard%27s_p_%E2%88%92_1_algorithm): _p_ - 1 is powersmooth.
* [Williams's p + 1 algorithm](https://en.wikipedia.org/wiki/Williams%27s_p_%2B_1_algorithm): _p_ + 1 is smooth.
* [Cheng's elliptic curve algorithm](https://eprint.iacr.org/2002/109.pdf): 4_p_ − 1 has the form _db_<sup>2</sup> where _d_ ∈ {3,11,19,43,67,163}

Demo: williams.pem

### May 2008 Debian OpenSSH bug

To create a random key, a good cryptographic random number generator is required. Between 2006 and 2008 Debian had a [bug](https://www.debian.org/security/2008/dsa-1571) where the random number generator was improperly seeded, resulting in predictable keys.

In the Debian branch of OpenSSL, a line was removed that seeded the random number generator, because it generated compiler warnings. This resulted in insufficiently random data to generate good keys, which means many keys are the same, even if they were generated on different systems.

Demo: debian.pub

### Example keys

What do you do when as a developer you need a key pair and don't know exactly how it works? The same way you do for all your code, you copy-paste it from StackOverflow. Example keys in software implementations, web sites or protocol specifications may end up in software. Multiple implementations then use the same key, and the private key is available for everyone in the documentation or on the internet.

Demo: example.pem

### e = 1

Encryption is done by calculating _m<sup>e</sup>_. If _e_ = 1, this operation does nothing and the message is not encrypted. The message may still be padded but otherwise is just plain text and not encrypted at all.

Demo: eone.pem

### Prime N

Normally, two primes compose the modulus. However, you could also use three primes, or one prime. In the case of one prime, however, the private key is not very secret. Normally the chosen _p_ and _q_ are secret and you publish _N_, but in single-factor RSA you choose one number that you also publish.

Demo: nprime.pem

## Relations between multiple keys

If you have multiple keys, there may be relations between the keys that are interesting to search for.

### Shared N

If two keys have the same modulus, they also have the same p and q and can calculate each other's private key. This may not be a problem if both keys belong to the same person.

Demo: sharedn1.pem, sharedn2.pem

### Shared p or q

Devices that create their private key on first boot may not have enough entropy to create a random number. What sometimes happens is that this results in keys that have a different modulus but have one factor in common. In that case the greatest common divisor of the two moduli can be efficiently calculated to factor the modulus and recover the private key.

Demo: sharedp1.pem, sharedp2.pem

## Conclusion

There is a lot that can go wrong when creating RSA keys, especially when using a non-standard RSA-like cryptosystem. When finding a RSA public key during a test, make sure to test it for the vulnerabilities listed above.

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
* [RSA and a higher degree diophantine equation](https://eprint.iacr.org/2006/093)
* [Cryptanalysis of RSA with constrained keys](https://eprint.iacr.org/2006/092)
* [New Partial Key Exposure Attacks on RSA](https://www.iacr.org/archive/crypto2003/27290027/27290027.pdf)
* [A Generalized Wiener Attack on RSA](https://link.springer.com/content/pdf/10.1007/978-3-540-24632-9_1.pdf)
* [Cryptanalysis of RSA with Small Prime Difference](http://www.enseignement.polytechnique.fr/profs/informatique/Francois.Morain/Master1/Crypto/projects/Weger02.pdf)
* [Mining Your Ps and Qs: Detection of Widespread Weak Keys in Network Devices](https://www.usenix.org/system/files/conference/usenixsecurity12/sec12-final228.pdf)
* [RSA Cryptanalysis with Increased Bounds on the Secret Exponent using Less Lattice Dimension](https://eprint.iacr.org/2008/315.pdf)
* [New Attacks on RSA with Modulus N = p<sup>2</sup>q Using Continued Fractions](https://iopscience.iop.org/article/10.1088/1742-6596/622/1/012019/pdf)
* [A New Vulnerable Class of Exponents in RSA](http://citeseerx.ist.psu.edu/viewdoc/download?doi=10.1.1.182.1949&rep=rep1&type=pdf)
* [A new attack on RSA with a composed decryption exponent](https://eprint.iacr.org/2014/035.pdf)
