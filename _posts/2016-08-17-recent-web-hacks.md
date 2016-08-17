---
layout: post
title: "Recent web hacks"
thumbnail: anonymous-hacker-240.jpg
date: 2016-08-18
---


## The Bank Job

15 May 2016, [article](https://boris.in/blog/2016/the-bank-job/)

The Indian/Swedish security researcher Sathya "Boris" Prakash tested a mobile banking application of an Indian bank. He found several vulnerabilities. First, many of the validation and other application behavior is implemented client-side, in the phone app. Of course this can be circumvented with a debugger or by changing the requests to the server. Another vulnerability was caused by a missing authorization check: when transferring money, the application checked whether the customer supplied a valid PIN, but not whether the customer is the owner of the bank account. This makes it possible to transfer money from any account.

A serious bug that makes it possible to steal everybody's money. Boris got a thank-you email.

## Uber Hacking: How we found out who you are, where you are and where you went!

23 June 2016, [article](https://labs.integrity.pt/articles/uber-hacking-how-we-found-out-who-you-are-where-you-are-and-where-you-went/), [Portuguese article](http://futurebehind.com/a-boleia-dos-bugs-da-uber/)

Three pentesters from [Integrity](https://www.integrity.pt/) tested Uber as part of their bug bounty program, and found several vulnerabilities:

It is possible to:
* brute-force promo codes;
* retrieve other users' email address and phone number;
* use the driver section of the app even if you are not a driver;
* view the last trip of a driver;
* retrieve information on trips of other users.

Each vulnerability is clearly described in the post, with screenshots of the requests and responses where the vulnerability occurs. The researchers got approximately $18000 in bounty rewards.

## How we broke PHP, hacked Pornhub and earned $20,000

23 July 2016, [article](https://www.evonide.com/how-we-broke-php-hacked-pornhub-and-earned-20000-dollar/)

Ruslan Habalov found a vulnerability in Pornhub: the use of unserialize on user-supplied data. This is known to be unsafe and the [PHP manual](http://php.net/unserialize) even warns against it.

Ruslan probably could have reported this to Pornhub and get several hundred dollars. He put in some more effort and succesfully exploited this vulnerability, gaining remote code execution on the server.

## How I made LastPass give me all your passwords

27 July 2016, [article](https://labs.detectify.com/2016/07/27/how-i-made-lastpass-give-me-all-your-passwords/)

Mathias Karlsson discovered a bug in the password manager LastPass. By using a specific URL you could make LastPass think that it was on another domain and it would autofill the credentials of that domain in a login form. This made it possible to steal credentials from users visiting your web page.

## How to steal $2,999.99 in less than 2 minutes with Venmo and Siri

1 August 2016, [article](http://www.martinvigo.com/steal-2999-99-minute-venmo-siri/)

Venmo is a payment provider that makes it possible to pay people by SMS. It is also possible to read and send SMS messages from a locked iPhone, using Siri. Martin Vigo pointed out that this combination can be used to steal money from people if you have physical access to their locked phone.

This is not really a vulnerability in a single application, but of a combination of applications. Apple thinks SMS messages do not need to be secured on your locked phone. Venmo thinks SMS messages sufficiently authenticate a user to do a payment. This combination creates a vulnerability that is hard to solve.
