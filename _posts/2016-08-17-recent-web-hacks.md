---
layout: post
title: "Interesting recent web application hacks"
thumbnail: anonymous-hacker-240.jpg
date: 2016-08-19
---

This post looks at some interesting web application hacks that were recently published.

## The Bank Job

15 May 2016, [article](https://boris.in/blog/2016/the-bank-job/)

The Indian/Swedish security researcher Sathya "Boris" Prakash found several vulnerabilities in a mobile banking application of an Indian bank. One of the bigger vulnerabilities was caused by a missing authorization check: when transferring money, the application checked whether the customer supplied a valid PIN, but not whether the customer is the owner of the bank account. This makes it possible to transfer money from any account.

The write-up is a bit messy and some details are missing because Boris wants to protect the identity of the bank. Even so, this is an interesting article mainly because it shows that even banks have serious security vulnerabilities.

The bank does not seems to have a bug bounty or responsible disclosure program. It seems that Boris hacked them without their consent, which is legally and morally questionable. After disclosing the vulnerabilities to the bank, Boris got a thank-you email.

## Uber Hacking: How we found out who you are, where you are and where you went!

23 June 2016, [article](https://labs.integrity.pt/articles/uber-hacking-how-we-found-out-who-you-are-where-you-are-and-where-you-went/), [Portuguese article](http://futurebehind.com/a-boleia-dos-bugs-da-uber/)

Three pentesters from [Integrity](https://www.integrity.pt/) tested Uber as part of their bug bounty program, and found several vulnerabilities:

It is possible to:
* brute-force promo codes;
* retrieve other user's email address and phone number;
* use the driver section of the app even if you are not a driver;
* view the last trip of a driver;
* retrieve information on trips of other users.

Each vulnerability is clearly described in the post, with screenshots of the requests and responses where the vulnerability occurs. The researchers got approximately $18000 in bounty rewards.

## How we broke PHP, hacked Pornhub and earned $20,000

23 July 2016, [article](https://www.evonide.com/how-we-broke-php-hacked-pornhub-and-earned-20000-dollar/)

Ruslan Habalov found a vulnerability in Pornhub: the use of unserialize on user-supplied data. This is known to be unsafe and the [PHP manual](http://php.net/unserialize) even warns against it.

Ruslan probably could have reported this to Pornhub and get several hundred dollars. He put in some more effort and successfully exploited this vulnerability, gaining remote code execution on the server.

And that is what makes this hack interesting: Ruslan found a vulnerability, and then put in considerable effort to find a zero-day to exploit it. This would not be necessary to make Pornhub safer, but it was necessary to get a $20,000 bounty reward.

## How I made LastPass give me all your passwords

27 July 2016, [article](https://labs.detectify.com/2016/07/27/how-i-made-lastpass-give-me-all-your-passwords/)

Mathias Karlsson discovered a bug in the password manager LastPass. By using a specific URL you could make LastPass think that it was on another domain and it would autofill the credentials of that domain in a login form. This made it possible to steal credentials from users visiting your web page.

This vulnerability is extra painful because LastPass is a product that is supposed to make you safer. Now it turns out it could leak your passwords to any web site.

## How to steal $2,999.99 in less than 2 minutes with Venmo and Siri

1 August 2016, [article](http://www.martinvigo.com/steal-2999-99-minute-venmo-siri/)

Venmo is a payment provider that makes it possible to pay people by SMS. It is also possible to read and send SMS messages from a locked iPhone, using Siri. Martin Vigo pointed out that this combination can be used to steal money from people if you have physical access to their locked phone.

This is not really a vulnerability in a single application, but of a combination of applications. Apple thinks SMS messages do not need to be secured on your locked phone. Venmo thinks SMS messages sufficiently authenticate a user to do a payment. This combination creates a vulnerability that is hard to solve.

Vigo states that you can steal $3000 this way, but I think this would be hard in practice. You need access to your victim's iPhone, and your victim has to have a significant amount of money on her account. Furthermore, after you have transferred the money to your Venmo account you need to get it out of your Venmo account without being caught. The victim can just call the police and they can identify you by your phone number or bank account.
