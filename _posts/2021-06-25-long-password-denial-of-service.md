---
layout: post
title: "Long passwords don't cause denial of service when using proper hash functions"
thumbnail: scrap-iron-480.jpg
date: 2021-07-02
---

ASVS [states](https://github.com/OWASP/ASVS/blob/v4.0.3/4.0/en/0x11-V2-Authentication.md#:~:text=no%20longer%20than%20128%20characters) that passwords should be at most 128 characters. This originates from the idea that longer passwords take longer to hash, which can lead to a denial of service when an attacker performs login attempts with very long passwords. However, this is not generally true. With a proper hash function, longer passwords do not take a significantly longer time to hash.

## How PBKDF2 works

Hashing passwords should be done using a password hash, such as bcrypt, scrypt, PBKDF2, or Argon2. PBKDF2 hashes the password and the salt, and then hashes that result again and again:

$$\operatorname{PBKDF2}(Password, Salt, c) = U_1 \oplus U_2 \oplus \cdots \oplus {U_c}$$

where 

$$
\begin{align}
&U_1 = \operatorname{HMAC}(Password, Salt) \\
&U_2 = \operatorname{HMAC}(Password, U_1) \\
&\vdots\\
&U_c = \operatorname{HMAC}(Password, U_c-1)
\end{align}
$$    

and

$$\operatorname{HMAC}(K, m) = \operatorname{SHA2}((\operatorname{SHA2}(K) ⊕ opad) || \operatorname{SHA2}((\operatorname{SHA2}(K) ⊕ ipad) || m))$$

Now, even though it seems like we need `Password` in each loop iteration, the actual calculation is only performed on `SHA2(Password)`. So an optimization is to hash the password only once; calculate `SHA2(Password)` at the start of the loop, and then pass that to each iteration of `HMAC`.

With this optimization, the password is only hashed once. This means that the length of the password is not of great influence on the total execution time.

## Length dependent hash functions

If PBKDF2 is naively implemented without this optimization, the password is hashed on each iteration. Since there are typically hunders of thousands of iterations, this can have a big result on performance. It may take minutes to calculate the hash of a password of millions of characters.

PBKDF2's algorithm does not specify exactly when and how often the password is hashed. It depends on the implementation whether the execution time blows up with password length. Scrypt builds on top of PBKDF2, and execution time is also implementation dependent. Bcrypt only uses the first 72 bytes of the password, so is not affected by long passwords. The first thing Argon2 does is to hash the password, and works on the hash from then on, so it is not vulnerable to long passwords.

Of course, people don't always use proper implementations, or even approved password hash functions. In those cases, it may be possible to cause a denial of service attack by performing a login attempt with a password of several megabytes.

### Django's PBKDF2

Django had a naive implementation of PBKDF2, without the above-mentioned optimization. This led to [a vulnerability (CVE-2013-1443)](https://www.djangoproject.com/weblog/2013/sep/15/security/), where a long password can create a denial-of-service.

> A password one megabyte in size, for example, will require roughly one minute of computation to check when using the PBKDF2 hasher.

At this point, the Django developers were not aware of their inefficient PBKDF2 implementation, and solved the problem by limiting password length:

> To remedy this, Django's authentication framework will now automatically fail authentication for any password exceeding 4096 bytes.

A couple of weeks later, they [fixed their PBKDF2 function](https://github.com/django/django/commit/68540fe4df44492571bc610a0a043d3d02b3d320) and [removed the password length limit](https://github.com/django/django/commit/5d74853e156105ea02a41f4731346dbe272c2412). Nowadays, Python has a [built-in PBKDF2 function](https://docs.python.org/3.9/library/hashlib.html#hashlib.pbkdf2_hmac), but that didn't exist yet when Django was created.

### phpass

Phpass is a PHP password library, from before PHP had built-in [`password_hash`](https://www.php.net/manual/en/function.password-hash.php) and [`password_verify`](https://www.php.net/manual/en/function.password-verify.php). It has several ways to hash passwords. The "portable" method does something like this:

```php
    do {
        $checksum = md5($checksum . $password, true);
    } while (--$count);
```

As you can see, it hashes the password in every iteration, and is thus vulnerable to denial-of-service in long passwords. Any application that uses this and doesn't limit password length is vulnerable, and two applications received CVEs for this specifically:

* Drupal, [CVE-2014-9016](https://nvd.nist.gov/vuln/detail/CVE-2014-9016), limited the password to [512 characters](https://github.com/drupal/drupal/blob/515d10367bbe5cc158153a90e7960f92c2862745/core/lib/Drupal/Core/Password/PasswordInterface.php#L13). 
* WordPress, [CVE-2014-9034](https://nvd.nist.gov/vuln/detail/CVE-2014-9034), limited the password to [4096 characters](https://github.com/WordPress/WordPress/blob/e6ea7172774a71264968dd29b2830a7c21729b7f/wp-includes/class-phpass.php#L206).

### crypt

The `crypt` function built-in to libc originally supported hashing passwords of at most 8 characters using [DES](https://en.wikipedia.org/wiki/Data_Encryption_Standard). This was not very secure, and in 1994 Poul-Henning Kamp (PHK) came up with [md5crypt](http://phk.freebsd.dk/sagas/md5crypt/) to solve this. In turn, md5crypt became insecure and in 2007 Ulrich Drepper came up with [SHA256-crypt and SHA512-crypt](https://akkadia.org/drepper/SHA-crypt.txt). For all these hash functions, the execution time is dependent on the length of the password, which means it's possible to trigger a denial of service by using a very long password.

I think this one is especially unfortunate. Being the standard password hashing function in a widely used standard library, these algorithms received respect that turned out to be undeserved.

## Conclusion

Denial of service through long passwords is a vulnerability that affected at least Django, Drupal, and WordPress. The underlying cause of this was a hash function with execution time dependent on input length, either through faulty implementation or because of an improper hashing algorithm. Applications that use platform or framework implementations for recommended hash functions are unlikely to be vulnerable.

To defend against this vulnerability, we should recommend application developers to use proper hash functions. Recommending a maximum length for the password only hides underlying problems, and is unnecessary when using a proper hash function.

## Read more

* [Aaron Toponce : Do Not Use sha256crypt / sha512crypt - They're Dangerous](https://pthree.org/2018/05/23/do-not-use-sha256crypt-sha512crypt-theyre-dangerous/)
* [Requirements for iterative password hashing](/2016/05/25/iterative-password-hashing/)
* [V2.1.2 - No Password Upper Bound · Issue #756 · OWASP/ASVS](https://github.com/OWASP/ASVS/issues/756)
* [Unix crypt using SHA-256 and SHA-512](https://akkadia.org/drepper/SHA-crypt.txt)

<script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3.1.2/es5/tex-mml-chtml.js" integrity="sha384-fNl9rj/eK1wEYfKc26CbPM6qkVQ+9MvYaoAFNql4ulbjBEWV2XLNP1UB8jQTtSe3" crossorigin="anonymous"></script>
