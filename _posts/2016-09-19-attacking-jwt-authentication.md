---
layout: post
title: "Attacking JWT authentication"
thumbnail: wax-seal-240.jpg
date: 2016-09-25
---

## How to recognize a JWT

* Long
* Base64-encoded
* Three parts separated by .
* Decode on JWT.io

## Check for sensitive data in the JWT

JWTs are not encrypted.

## Change the signing method

### To none

No signature needed.

### From RS256 to HS256

Use public key as signing key.

## Crack the key

If HMAC signature.


