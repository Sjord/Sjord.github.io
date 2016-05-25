---
layout: post
title: "Iterative password hashing"
thumbnail: fingerprint-240.jpg
date: 2016-05-23
---

To securely store passwords they should be hashed with a slow hashing function, such as PBKDF2. PBKDF2 is slow because it calls a fast hash function many times. This blog post explores some properties that the iterations must have to be secure.

## Why to use slow hash functions

Assume you have a web application that requires a username and password to log in. You wouldn't want the passwords to become known, even if your server is hacked and someone gains access to the database. That is why you don't store a plaintext password, but a hash of the password. This hash can be used to verify whether the user has entered the correct password, but it is not simply reversible to acquire the original password.

If someone has the password hash, he can try to brute-force it to acquire the original password. That is, he can hash all possible passwords until he finds one that matches the original password hash. To make this as hard as possible, the hashing function should be pretty slow.

If you use SHA1 to hash a password, an attacker can try 10,000,000,000 passwords per second on commodity hardware. If you use scrypt, you can configure it so that the attacker can try 5 passwords per second. That makes a big difference when brute-forcing a password.

## Iterative hashing

There are a couple of methods to make a slow hash function, but this blog post is about iterative hashing. The idea is simple: instead of taking just one SHA1 of the password, you take the SHA1 of the SHA1 of the SHA1 of the password. You repeatedly call SHA1 on the previous result. This way, the attacker also needs to do many SHA1 calls and brute-forcing the password will become pretty slow:

    # Simple example, don't actually use this
    hash = sha1(password)
    for (i = 0; i < iter_count; i++) {
        hash = sha1(hash)
    }
    return hash

This is approximately what PBKDF2 does. PBKDF2 has some modifications on the above simple algorithm to increase the security. Let's see what those are.

## Precomputation resistance

When the passwords hashes are leaked, we want the cracking to take a long time so we can inform users to change their password. We don't want attackers be able to do much work *before* our password hashes are leaked. If the attackers can create a rainbow table and use that to crack our hashes, they can greatly speed up the cracking if they do some work in advance. That is not good, so we want our algorithm to be resistant against precomputations.

### Using salts

With our little example, attackers can hash a whole dictionary beforehand and simply compare the hashes with the database. To prevent this, we add a salt to each password hash. A salt is a random string that is used along with the password in the hash. The salt is typically stored with the hash, so we can assume the attacker also knows the salt. Even though it is not secret anymore, it makes sure that the attacker starts his computations from the moment the hashes (and the salts) are leaked, and not before.

The password and salt should be combined into one hash. A typical way to do this is to use HMAC, which is also what PBKDF2 uses.

### Computing many hashes in advance

In our simple example we repeatedly take a hash of a hash. It is easy to already calculate many of these values. An attacker could create a lookup table (or rainbow table) for the sha1 of sha1 outputs. This would take a very long time, but he can do it even before your database is leaked. Then when he has the password hashes, he does not need to call the sha1 function that much and can greatly speed up cracking the passwords.

To prevent this, we should use the password and/or the salt in each iteration. It should really be the password, and we will see later why.

## Work reduction for the attacker

### Optimizing iterative HMAC calls

As we said earlier, PBKDF2 uses HMAC because that is a secure way to combine multiple things into a hash. HMAC works something like this:

    function simplified_hmac(key, message) {
        return sha1(key + sha1(key + message));
    }

Note that both `sha1` calls get data consisting of the key and something else. Also, with the real HMAC the key is padded to be a full block in size. This means that we can calculate `sha1(key)` outside of the loop, and update the SHA1 hash with other data. In the following loop, we calculate `sha1(password)` many times:

    hash = hmac(password, salt)
    for (i = 0; i < iter_count; i++) {
        hash = hmac(password, hash)
    }
    return hash

It would be much faster to use this:

    partial_hmac = init_hmac(password)
    hash = partial_hmac.update(salt)
    for (i = 0; i < iter_count; i++) {
        hash = partial_hmac.update(hash)
    }
    return hash

Where `init_hmac` initializes the two `SHA1` hashes with the password, and `partial_hmac.update` appends the data and returns the resulting SHA1 hash. By doing this work outside of the loop we can save 50% of the work, greatly speeding up the attack.

This optimization can and should also be used by the application itself. It can actually be performed for PBKDF2. A further explanation can be found in the paper [On the weaknesses of PBKDF2](https://eprint.iacr.org/2016/273.pdf).

### Optimizing for many different passwords

The application hashes a different password and salt every time someone logs in. The attacker on the other hand hashes the same salt and a lot of different passwords. If the attacker can put computations on the salt outside of the loop that tries all passwords, he can reduce his work.

In the previous example we saw that we can put work outside of the `iter_count` loop. In the same way we may be able to put work outside of the loop that goes through all the passwords.

Consider the following example:

    hash = hmac(salt, password)
    for (i = 0; i < iter_count; i++) {
        hash = hmac(salt, hash)
    }
    return hash

Here, the salt is used as the key for the HMAC. While we are cracking one password hash, we are trying many different passwords with the same salt. We can thus put the computation on the salt outside of the password loop, further reducing work.

### Choosing the fastest environment

While your application runs on a web server, the attacker may run his attacks on a machine optimized for hashing. The application may be written in Java, but the attacker can choose any implementation that he wants. If you compute hashes on a general purpose CPU, the attacker may use a GPU or FPGA.

Attacks can be speeded up greatly by choosing the right environment. You should use a fast hashing implementation that is hard to implement on hardware, but the attacker still has the advantage when choosing the environment.

## Cycle resistance

The hashing function may have a short cycle or fixed point. A fixed point is a value for which `hash(value) == value`. This greatly reduces the security of an iterative hash, because any iterations done after reaching the fixed point don't change anything anymore.

To solve this the hash can also use the iteration count as part of the hash:

    hash = hmac(password, salt)
    for (i = 0; i < iter_count; i++) {
        hash = hmac(password, hash + i)
    }
    return hash

Another solution, that PBKDF2 uses, is to XOR all intermediate hashes together in the result.

Hash cycles and fixed points are a bit of a theoretical risk. The chance of hitting one is very low, and no known cycles are known for hash functions such as SHA1.

## Examples

### Password-hash

The node.js password-hash module does this:

    var hash = password
    for (i = 0; i < iter_count; i++) {
        hash = hex(hmac(salt, hash))
    }
    return hash

### PBKDF2

Niet kunnen precomputen
Geen cycles in de hash
Lange msg kan misschien misbruikt worden
HMAC(salt, ...) kan voorberekend worden.
OntheweaknessesofPBKDF2
Implementatiesnelheid Javascript vs C
