---
layout: post
title: "The case for client-side hashing: logging passwords by mistake"
thumbnail: coffee-accident-480.jpg
date: 2020-02-12
---

Hashing passwords makes it possible to use them for authentication, while making it hard to reconstruct the original password. Hashing passwords on the client may be beneficial: even though it does not protect against attackers, it does protect against accidental mistakes.

## Introduction

Instead of storing plaintext passwords in a database, applications typically store a hash of the password. This makes it possible to verify the password, but make it hard to recover the original password. If information in the database is exposed, for example through SQL injection, hashed passwords mitigate the impact by making it harder to recover the original passwords.

## Where to hash?

When a user configures a password, the application must calculate a hash to store it in the database. This calculation can be done either on the client, using JavaScript in the browser, or on the server. There have been discussions in the past about whether to hash on the client or on the server, and the general consensus is to hash on the server.

If a hash is calculated on the client, the client authenticates to the server by submitting their hash. The server then compares the hash to the database entry. This means that if the database is exposed, attackers can authenticate as anyone by submitting the correct hash. Even though they cannot determine the original passwords, they can still use the hashes directly to break authentication. With client-side hashing, the hash effectively becomes the password.

## Why not both?

The question whether to hash on the client or the server presents a false dilemma: it's possible to do both. By hashing on the server, passwords are adequately protected even in the case of a database leak. By hashing on the client, the password doesn't leave the user's browser and even the web application doesn't learn the password. 

## Advantage of client-side hashing

Client-side hashing offers no advantage against adversarial attackers. Attackers already cannot sniff traffic, since it's encrypted using HTTPS. If they compromise the web application itself, they can still access plaintext passwords as users are typing them, or simply disable client-side hashing in the application.

However, client-side hashing protects against innocent mistakes. If the application doesn't handle the password, it can't accidentally mishandle the password.

The plaintext password may be logged to a file. Even though developers know that plaintext passwords shouldn't be logged, maybe they try to debug an issue by logging all POST parameters, thereby including a page that handles passwords. If the password is hashed on the client side, only the intermediate hash is logged. This is also not ideal, but the original password remains safe, which is especially important if the password is reused in other applications.

## Password logging accidents

Accidentally logging plaintext passwords is surprisingly common. Some big players such as Facebook, Twitter and GitHub have published incidents where plaintext passwords are logged. Here are some examples:

* [Facebook logs plaintext passwords](https://newsroom.fb.com/news/2019/03/keeping-passwords-secure/), January - March, 2019
* [Instagram put passwords as parameter in the URL](https://nakedsecurity.sophos.com/2018/11/20/instagram-accidentally-reveals-plaintext-passwords-in-urls/), April - November 2018
* [GitHub logged plaintext passwords](https://www.zdnet.com/article/github-says-bug-exposed-account-passwords/), May 2018
* [Coinbase logs passwords on error during registration](https://cointelegraph.com/news/coinbase-accidentally-saves-unencrypted-passwords-of-3-420-customers)
* [Twitter accidentally stored passwords in a log](https://blog.twitter.com/en_us/topics/company/2018/keeping-your-account-secure.html), May 2018
* [Curator exposes password in plain text in debug logs](https://github.com/elastic/curator/issues/1336)
* [Rainloop stores password in cleartext in logfile](https://github.com/RainLoop/rainloop-webmail/issues/1872)
* [Plaintext passwords in error.log in Gitea](https://github.com/go-gitea/gitea/issues/3055)
* [User credentials are logged on error in Netbox](https://github.com/netbox-community/netbox/issues/2880)
* [Plain password in owncloud.log](https://github.com/owncloud/core/issues/25895)
* [GitHub logged npm passwords, npm access tokens, and GitHub access tokens](https://github.blog/2022-05-26-npm-security-update-oauth-tokens/)
* [WordPress plugin All-In-One Security (AIOS) 5.1.9 logged plaintext passwords](https://aiosplugin.com/all-in-one-security-aios-wordpress-security-plugin-release-5-2-0/), Jully 2023

## Conclusion

Even though client-side hashing does not offer additional security against attackers, it does provide additional security against accidental mistakes. This threat model is not often considered, even though the examples above show that it is realistic. I think this warrants an evaluation of how we handle passwords, and whether client-side hashing is beneficial.