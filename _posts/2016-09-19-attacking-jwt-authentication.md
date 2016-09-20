---
layout: post
title: "Attacking JWT authentication"
thumbnail: wax-seal-240.jpg
date: 2016-09-25
---

## About JWTs

### What is a JWT

A JWT is a string that contains a signed data structure, typically used to authenticate users. Because it is cryptographically signed, only the server can create and modify tokens. This means the server can safely put `userid=123` in the token and hand the token to the client, without having to worry that the client changes his user identifier.

### How to recognize a JWT

A JWT typically looks like this:

    eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9kZW1vLnNqb2VyZGxhbmdrZW1wZXIubmxcLyIsImlhdCI6MTQ3NDM1NzIzMywiZXhwIjoxNDc0MzU3MzUzLCJkYXRhIjp7ImhlbGxvIjoid29ybGQifX0.HAveF7AqeKj-4o-tJpYQWXKN2WgWTa8uRYViRp_3mh8

There are several properties that can help recognizing a JWT:

* JWTs are long, at least a 100 characters.
* JWTs consist of base64-encoded data: letters, digits, _ and -.
* JWTs consist of three parts, separated by dots.
* They typically occur in the `Authorization` header with the `Bearer` keyword.

JWTs can be used in any context, but are often used in a header like this:

    Authorization: Bearer eyJ0eXAiOiJKV1QiLCJh...

## Attacking JWTs

If you have an application that uses JWT for authentication, there are several things you can try to test the security of the authentication layer.

### Check for sensitive data in the JWT

JWTs may look like garbage to the naked eye, but actually they are just base64-encoded data. They are easily decoded, for example by using the website [JWT.io](https://jwt.io/). There may be sensitive information stored in the JWT, that is easily discovered this way.

### Change the signing method

JWTs are signed (or they should be) to prevent users from changing the data within. There are several algorithms that can be used for signing, for example using a HMAC or using RSA signing. The JWT header contains the algorithm used to sign the JWT, and [one flaw of some algorithms](https://auth0.com/blog/critical-vulnerabilities-in-json-web-token-libraries/) is that they trust this JWT header, even though it can be manipulated by the client.

#### To none

A JWT header looks like this:

    {
      "alg": "HS256",
      "typ": "JWT"
    }

This data is base64 encoded and is the part before the first dot of any JWT. The `alg` field here indicates the algorithm used to sign the JWT. One special "algorithm" that all JWT libraries should support is `none`, for no signature at all. If we specify `none` as algorithm in the header and leave out the signature, some implementations may accept our JWT as correctly signed.

#### From RS256 to HS256

The algorithm HS256 uses a secret key to sign and verify each message. The algorithm RS256 uses a private key to sign messages, and a public key to verify them. If we change the algorithm from RS256 to HS256, the signature is now verified using the HS256 algorithm using the public key as secret key. Since the public key is not secret at all, we can correctly sign such messages. 

### Crack the key

As previously stated, the HS256 algorithm uses a secret key so sign and verify messages. If we know this key, we can create our own signed messages. If the key is not sufficiently strong it may be possible to break it using a brute-force or dictionary attack. By trying a lot of keys on a JWT and checking whether the signature is valid we can discover the secret key. This can be done offline, without any requests to the server, once we have obtained a JWT.

## Conclusion


