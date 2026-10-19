---
layout: post
title: "Passwords are always short, but bcrypt's limit can still be problematic"
thumbnail: edge-danger-480.avif
date: 2026-10-28
---

Bcrypt's 72 byte limit is a problem even though users don't typically use passwords that long. Prehashing can solve that, but it not trivial to get right.

## Introduction

Bcrypt is a password hash. By design it can only hash 72 bytes. If you pass a longer password, it hashes the first 72 bytes and silently discards the remainder, in most implementations. This is no problem if it is used how it's meant to: users normally don't use passwords longer than 72 bytes, so passing a user password to bcrypt never hits this limit. However, it can become a problem if an application passes something else. Especially if a long string is prepended to the password, the password may fall off the end and only a small part may be verified. So this is still a problem worth solving. One way to solve it is to prehash the password: first pass it through another hash that is not length-limited, and then pass that result to bcrypt. Prehashing should use a randomized hash, to prevent *password shucking* or *breach correlation attacks*.

## Users don't use long passwords

I checked the rockyou and Adobe breaches, and passwords longer than 72 characters are exceedingly rare.

```sh
$ wc -l rockyou.txt 
 14344391 rockyou.txt
$ rg --count '.{73}' rockyou.txt
772
```

The rockyou password dump contains 772 out of 14344391 passwords that are longer than 72 characters. However, many of those are obvious form spam, keyboard mashing, or just copy/pasted content, not actual passwords. I couldn't find an obvious actual password in the set. That doesn't say much, since passwords can be any string and don't have to look like passwords.

Adobe's 2013 breach contains 152989508 passwords encrypted with an 8-byte block cipher. The longest password is 11 blocks (88 bytes), but it's the same block 10 times, indicating a repetitive password. There's one password that takes up 9 blocks (72 bytes), and several that take up 7 blocks (56 bytes).

So depending on how you look at it, passwords longer than 72 characters occur between never and 0.005% of the time.

## Some applications use bcrypt incorrectly

Bcrypt is supposed to take the user's password and nothing else, but some applications use it in other ways. This can lead to severe security problems.

- FreshRSS made their own authentication mechanism on top of bcrypt. They prepended a nonce to a secret. When the nonce increased in size, the first 72 bytes of the nonce + secret combination mostly consisted of nonce, and the secret was not checked. This means that the password was no longer checked when logging in.
- Okta prepended the user ID and username. If a long username was used, the password fell off the end and only the username would be checked.

So even if passwords are shorter than 72 bytes, problems occur when applications pass not just the password to bcrypt.

## Prehashing

A solution is to pass the input through a cryptographic hash before passing it through bcrypt. The hash does not have an input length limit, and creates an output of fixed size that is always less than the 72 byte limit of bcrypt. Bcrypt is questionable with null-bytes, so usually the prehash output is either hex or base64 encoded before being passed to bcrypt.

## Password shucking / breach correlation attacks

If you just use `bcrypt(SHA256(password))` or something similar, you expose yourself to a breach correlation attack. Your site stores passwords securely with bcrypt. But what if another site, that just stores passwords using `SHA256(password)`, is also breached? Attackers can verify which of the passwords are reused on your site, even when they don't know the password. When they confirm that a password is reused, they can focus on cracking `SHA256(password)` instead of the more secure `bcrypt(SHA256(password))`.

## Keyed hashing

Therefore, the prehash should be a HMAC of the password and some random string: `bcrypt(HMAC-SHA256(pepper, password))`

Here, `pepper` is an application-specific random string. This way, the prehash is different for each site and they cannot be reused.

## Peppers in libraries

What if you want to implement this not in an application, but in a library? If you use `my-library` as pepper, every application that uses your library uses the same prehash, reintroducing the problem described above. In that case, it is better to reuse the salt used by bcrypt. This gives each password its own "pepper", thus making the prehash different for each password. The salt is already available, making it unnecessary for the application developer to pass a pepper to the library.

## Conclusion

This is the mechanism implemented in [passlib](https://pypi.org/project/passlib/), and is [proposed to implement in PHP](https://wiki.php.net/rfc/bcrypt_sha256).

## Read more

- [How "Strengthening Crypto" Broke Authentication: FreshRSS and bcrypt's 72-Byte Limit](https://pentesterlab.com/blog/freshrss-bcrypt-truncation-auth-bypass)
- [Okta vulnerability explained (bcrypt auth bypass)](https://www.invicti.com/blog/web-security/okta-vulnerability-bcrypt-auth)
- [bcrypt\_sha256 is vulnerable to breach correlation attacks (#114) · Issue · python-libs/passlib](https://foss.heptapod.net/python-libs/passlib/-/work_items/114)

## Appendix

Some of the longest passwords in rockyou dump:

```
<div><embed src="http://apps.rockyou.com/fxtext.swf?ID=27887918&nopanel=true&stage=true" quality="high"  scale="noscale" width="376.715" height="127.95" wmode="transparent" name="rockyou" type="application/x-shockwave-flash" pluginspage="http://www.macrom
[IMG]http://i226.photobucket.com/albums/dd292/mhalditha_0018/tbs.jpg[/IMG]/* {--friendster-layouts.com css code start--} */  /* FRIENDSTER-LAYOUTS.COM */ /* Pink Butterfly By socialitem0 */  /* PAGE BACKGROUND */ body {   background-image: url(http://i200
<a href="http://www.imagenesparahi5.org"><img src="http://i226.photobucket.com/albums/dd122/hi54allll/iconos/nombres/ic-41-iconos_nombres_nqjOL.gif" border=0 title="Imagenes para hi5"> <br>Imagenes para hi5* </a><br>
wesley\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\'sgirl1
http://i209.photobucket.com/albums/bb154/robin_154/Days%20of%20The%20Week%20Glitter%20Graphics/036.gif
```

Some of the longest passwords in Adobe's dump:

```
BeHjPfxaxksF4eM9/FrGSwXh4z38WsZLBeHjPfxaxksF4eM9/FrGSwXh4z38WsZLBeHjPfxaxksF4eM9/FrGSwXh4z38WsZLBeHjPfxaxkvioxG6CatHBw==
BgmzyC6IekS03rFciD82pOkyzdEcEY+ccrvdzhOR0kA0CGSPHYYkupvWVEAIKJGM5RIR3r4AufieC+J/SIATRXCsLCQdSXxU
5azXZrDDcjfhMIZkSwLcWMOfpeUbCTO8qC9/NjzZZHg+O9vxjMkijtnlIRMMpwWg4qMRugmrRwc=
ct9SDhv3UCtTTRfS3HmRsThuBnTqYhsRrkaUVs2T9z4tsz6x1NCJurYntV5x3S0QO1U+Exs+6go=
KqFvgrLsvmwu0DA+71s1dPPzXRHTirtV8KDZo4IyCyY4RDkBye+I0amwbsaZjwleis5V8PoZn7E=
pQ51Pv7PtcXJ/3rvvKDaEcG5Tf0PHMiIgDtuH5T+EzJUPYrPpOWQELdLtyDq0DzF4qMRugmrRwc=
```