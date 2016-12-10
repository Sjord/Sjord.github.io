---
layout: post
title: "Using secret salts in password hashes"
thumbnail: todo-240.jpg
date: 2016-12-12
---

## Password hashes should be slow

Programs that use password authentication need to have some way to verify whether the password the user entered is the correct one. This is typically done by storing some hash of the password. The plaintext password should be hard to recover, which means that the one way hash function needs to be slow. This is mainly done by making the hashing a lot of work, but there is another way.

## Secret salts

We can append some random data to the password and throw that data away. This means that if we want to verify whether the password is correct, we have to brute-force that random data. This will significantly slow down verification of the password, which was our goal.

## Cost asymmetry

## Attacker trade-off

## Collission resistance

## Secret number of iterations

## Read more

* [Authentication using random challenges](https://www.google.com/patents/US5872917), patent US5872917, Hellman, 1995
* [A Simple Scheme to Make Passwords Based on One-Way Functions Much Harder to Crack](http://webglimpse.net/trial/bins/TR94-34.pdf), Udi Manber, 1996
* [Strengthening Passwords](ftp://gatekeeper.dec.com/pub/DEC/SRC/technical-notes/SRC-1997-033.pdf), Abadi, 1997
* [Secure Applications of Low-Entropy Keys](https://www.schneier.com/academic/paperfiles/paper-low-entropy.pdf), Kelsey, 1997
* [Brute Force Attack on UNIX Passwords with SIMD Computer](https://www.usenix.org/legacy/events/sec99/full_papers/kedem/kedem.pdf), 1999
* [Password-protection module](https://www.google.com/patents/US7886345), patent US7886345, Kaliski, 2004
* [Halting Password Puzzles](http://crypto.stanford.edu/~xb//security07/hkdf.pdf), Boyen, 2007
* [CASH: A Cost Asymmetric Secure Hash Algorithm for Optimal Password Protection](https://arxiv.org/pdf/1509.00239.pdf), Blocki, 2016
