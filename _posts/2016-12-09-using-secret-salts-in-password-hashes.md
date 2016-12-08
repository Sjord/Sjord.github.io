---
layout: post
title: "Using secret salts in password hashes"
thumbnail: todo-240.jpg
date: 2016-12-12
---

* [A Simple Scheme to Make Passwords Based on One-Way Functions Much Harder to Crack](http://webglimpse.net/trial/bins/TR94-34.pdf), Udi Manber, 1996
* [Strengthening Passwords](ftp://gatekeeper.dec.com/pub/DEC/SRC/technical-notes/SRC-1997-033.pdf), Abadi, 1997
* [CASH: A Cost Asymmetric Secure Hash Algorithm for Optimal Password Protection](https://arxiv.org/pdf/1509.00239.pdf), Blocki, 2016

Instead of a random salt, you can also use a random iteration count.

* [Halting Password Puzzles](http://crypto.stanford.edu/~xb//security07/hkdf.pdf), Boyen, 2007
* [Secure Applications of Low-Entropy Keys](https://www.schneier.com/academic/paperfiles/paper-low-entropy.pdf), Kelsey, 1997
