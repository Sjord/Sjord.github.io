---
layout: post
title: "Iterative password hashing"
thumbnail: fingerprint-240.jpg
date: 2016-05-23
---

To securely store passwords they should be hashed with a slow hashing function, such as PBKDF2. PBKDF2 is a slow hash function that makes use of a fast hash function, such as HMAC-SHA1. It becomes slow by calling the fast hash function many times. This blog post explores some properties that the iterations must have to be secure.

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

* Should use HMAC to combine

### Computing many hashes in advance

In our simple example we repeatedly take a hash of a hash. It is easy to already calculate many of these values. An attacker could create a lookup table (or rainbow table) for the sha1 of sha1 outputs. This would take a very long time, but he can do it even before your database is leaked. Then when he has the password hashes, he does not need to call the sha1 function that much and can greatly speed up cracking the passwords.

To prevent this, we should use the password and/or the salt in each iteration. 
(It should really be the password, and we will see later why)

## Cycle resistance

## Work reduction for the attacker

### Optimizing for many different passwords

Attacker doet hash(salt, password) voor vele waarden van password, dus hij kan sha1(salt...) al optimaliseren.

### Optimizing iterative HMAC calls

### Choosing the fastest environment

## Examples

### Password-hash

### PBKDF2

Niet kunnen precomputen
Geen cycles in de hash
Lange msg kan misschien misbruikt worden
HMAC(salt, ...) kan voorberekend worden.
OntheweaknessesofPBKDF2
Implementatiesnelheid Javascript vs C
