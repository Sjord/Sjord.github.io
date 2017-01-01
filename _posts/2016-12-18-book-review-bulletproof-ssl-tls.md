---
layout: post
title: "Book review: Bulletproof SSL and TLS"
thumbnail: bulletproofssl-240.jpg
date: 2017-01-05
---

Recently I read the book [Bulletproof SSL and TLS](https://www.amazon.com/gp/product/1907117040/ref=as_li_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=1907117040&linkCode=as2&tag=sjoerdlangkem-20&linkId=979c050291676d0d04a8e0e3a4c84399). In this post I share my opinion about this book.

## About the author

[Bulletproof SSL and TLS](https://www.amazon.com/gp/product/1907117040/ref=as_li_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=1907117040&linkCode=as2&tag=sjoerdlangkem-20&linkId=979c050291676d0d04a8e0e3a4c84399) is written by Ivan Ristić. You may know him from [SSL Labs](https://www.ssllabs.com/), where you can test the TLS configuration of your site. Ivan started working at [Qualys](https://www.qualys.com/) after it acquired SSL Labs from him. This year he founded [Hardenize](https://www.hardenize.com/), a tool to monitor the security of your web site.

## About the book

[Bulletproof SSL and TLS](https://www.amazon.com/gp/product/1907117040/ref=as_li_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=1907117040&linkCode=as2&tag=sjoerdlangkem-20&linkId=979c050291676d0d04a8e0e3a4c84399) describes TLS, and specifically its usage in HTTPS. Under some conditions, TLS can provide perfect transport level security. The book describes how you should use it to obtain that, and what the limitations are. 

Although it describes the theory of TLS, it does not leave behind the real world. In chapter 4, for example, several attacks on SSL certificates are described that happened in recent years. A startling number of breaches are laid out where attackers could issue their own certificates. Some of these were hacks, as in the case of DigiNotar. Others are the result of negligence. For example, TurkTrust accidentally marked two certificates as CA certificates, which were later used to sign fraudulent certificates.

The first ten chapters explain TLS generally. They describe the workings of TLS, how it is used, and how it can be broken. The last six chapters describe how to use OpenSSL, Apache, Tomcat, IIS, and Nginx. I found the first part more interesting to read. The second part is more useful as a reference.

The theoretic parts are provided with enough practical information to keep things interesting, which is not limited to TLS. For example, the book covers a part on cookie stealing and manipulation.

The book covers a large part of TLS and HTTPS. It is pretty complete, even though some topics are not discussed. For example, the new features of TLS 1.3 are not described and certificate transparency is only briefly mentioned.

## Conclusion

A very good book, which can teach you more than just the workings of TLS. I would recommend it, even though it is a little bit pricey.
