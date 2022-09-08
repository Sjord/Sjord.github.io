---
layout: post
title: "Problems with pwdhash"
thumbnail: rubiks-240.jpg
date: 2018-01-17
---

You shouldn't reuse passwords across multiple websites. However, it is also hard to remember hundreds of different passwords. One solution to this is a deterministic password manager: you hash the domain name and the master password, and use that as the site password. This has some obvious problems, but in this post I would like to point out some problems that I think are less obvious.

## Introduction

[PwdHash](https://pwdhash.github.io/website/) is such a deterministic password manager. It is stateless, in that it does not save any data on your computer. When you have installed the browser plugin, you can type F2 in any password field to replace the typed password with a custom hash of the password and the current domain. This way, you can use the same master password on multiple sites, while the site doesn't see your master password.

## What is the domain?

One problem is with determining the domain. You want to use the same password for all websites of one company, and different passwords for different companies. However, it turns out to be hard to map domains to administrations. The domains wiktionary.org and wikipedia.org belong to the same organization, and you may want to use the same password for those. The domains login.salesforce.com and www.salesforce.com clearly belong to the same organization, but example.herokuapp.com and sechub.herokuapp.com should not share the same password. This is even hard to determine for people. Does login.microsoftonline.com belong to Microsoft? Does paypal-community.com belong to PayPal?

There is the [public suffix list](https://publicsuffix.org/), which tries to list all domains where different organizations can get a domain. For example, `com` is listed, since several people can obtain domains under `.com`. Similarly, `herokuapp.com` is listed, since it contains subdomains of different administrative parties. This list is regularly updated, which brings up the next problem:

## Updating a deterministic password manager

If you want to change anything about a deterministic password manager, you break existing passwords. For example, PwdHash should use a slower hash function to make brute-forcing harder. However, users depend on the existing hash function to log into sites. If you change the hash function, newly generated passwords are secure, but users can no longer log in to anything.

This works similarly for the public suffix list. Imagine there is some new hosting platform, coolhosting.com, that provides subdomains. You create a account on sjoerdsapp.coolhosting.com with your deterministic password manager. The password manager hashes your password with `coolhosting.com` and you use that output as the password for your account. After some time, the administrators of coolhosting.com add their application to the public suffix list. Now your password is hashed against `sjoerdsapp.coolhosting.com`, and you can no longer log in.

The implementation for deterministic password managers can never change, which is a serious problem.

## Conclusion

Using a deterministic password manager may seem like a good idea at first, but has some serious limitations.

## Read more

* [Secure Hashed Passwords Using Dwb and Pwdhash](https://milesalan.com/notes/secure-hashed-passwords-using-dwb-and-pwdhash/s)
* [4 fatal flaws in deterministic password managers](https://tonyarcieri.com/4-fatal-flaws-in-deterministic-password-managers), [HN thread](https://news.ycombinator.com/item?id=13016132)
* [In defense of deterministic password managers](https://medium.com/@mahdix/in-defense-of-deterministic-password-managers-67b5a549681e)
* [Security considerations for password generators](https://palant.de/2016/04/20/security-considerations-for-password-generators)

