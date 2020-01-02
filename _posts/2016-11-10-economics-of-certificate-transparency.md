---
layout: post
title: "The economics of certificate transparency"
thumbnail: checking-papers-240.jpg
date: 2016-11-14
---

Traffic to any web site that uses HTTPS is encrypted, and the server's identity is verified. For this the web server needs a certificate. The certificate proves that the client is really talking to the domain it expects, and not to some man in the middle attacker. This security is broken if an attacker can obtain a certificate for a domain he wants to attack. To prevent this, we need certificate transparency.

## What is certificate transparency

The goal of the [certificate transparency project](https://www.certificate-transparency.org/) is to create a log book with issued certificates. This log can be used by domain owners to check that there are no certificates issued without their permission.

Certificates are issued by certificate authorities (CA's). A typical browser trusts hundreds of certificate authorities and any of them can sign a certificate. Normally a certificate should only be signed with the permission of the domain owner. For example, only Google should be able to obtain a certificate for www.google.com. Unfortunately, sometimes certificates are issued to other parties, either by mistake or with malicious intent. This happens fairly often:

* March 2011, a [Comodo affiliate is hacked](https://www.comodo.com/Comodo-Fraud-Incident-2011-03-23.html) and issues certificates for mail.google.com and www.google.com.
* July 2011, [DigiNotar](https://en.wikipedia.org/wiki/DigiNotar) is totally compromised and attackers issue a \*.google.com certificate.
* August 2011, [TURKTRUST accidentally issues a CA certificate](https://security.googleblog.com/2013/01/enhancing-digital-certificate-security.html) which is used to create a \*.google.com certificate.
* December 2013, [ANSSI issues a certificate](https://security.googleblog.com/2013/12/further-improving-digital-certificate.html) for several Google domains.
* July 2014, [India CCA issues certificates](https://security.googleblog.com/2014/07/maintaining-digital-certificate-security.html) for several Google domains.
* March 2015, [CNNIC issues a certificate](https://security.googleblog.com/2015/03/maintaining-digital-certificate-security.html) for several Google domains for use in a man-in-the-middle proxy.
* September 2015, [Symantec issues certificates](https://security.googleblog.com/2015/10/sustaining-digital-certificate-security.html) for google.com and www.google.com for testing purposes.

Any such certificate poses a serious security risk, since it could be used to listen in on traffic between clients and Google. When an unauthorized certificate is issued, Google wants to act as soon as possible so that the certificate is no longer trusted by the major browsers. For this, they first have to know that the fake certificate exists, and that is where certificate transparency comes in.

Imagine that every certificate issued is stored in a public log as well. Google can now keep an eye on that log, and check whether any certificates are issued for google.com. This would make detection of fake certificates much easier. This is certificate transparency: all certificates are publicly viewable and anyone can check whether certificates are issued without authorization of the domain owner.

## Creating incentives

For certificate transparency to work, everybody who creates a certificate should put the certificate into the public log book for others to check. There is a slim chance certificate authorities will do this out of the goodness of their heart, but criminal hackers certainly won't put their fake certificates up for public scrutiny. How can this system work if it requires the participation of certificate issuers?

There needs to be some incentive to put certificates in the public log. Google can create that incentive since they are also a browser vendor. Imagine Google Chrome only trusts certificates that have been added to the public log. Any certificate that has not been made public will give an error. This suddenly creates a good reason to put the certificates in the log book: if you don't, your website stops working. Any certificate that has not been made public in the log book won't work in Chrome.

Unlike other features, not all browsers have to implement certificate transparency for this to work. Customers want their certificates to work in Chrome, so they want a certificate which has been logged. Certificate authorities in turn want to log their certificates so they can offer certificates that work in Chrome. This causes most certificates to end up in a public log, whether or not other browsers implements certificate transparency or not.

## Slow transition to full certificate transparency

Implementing this all of a sudden would cause major problems with hundreds of certificate authorities and millions of existing certificates, which is why Google wants to implement this incrementally. Currently, certificate transparency is mandatory for EV certificates. There are a limited number of companies that issue EV certificates, making it somewhat easy to make them cooperate on certificate transparency.

In reaction to the [Symantec problem](http://arstechnica.com/security/2015/10/still-fuming-over-https-mishap-google-gives-symantec-an-offer-it-cant-refuse/) in 2015, Google also required all Symantec certificates to be submitted to the public log book. Because they screwed up, Symantec was put under close scrutiny. All certificates Symantec issues can be viewed in a public log. Again, Google could pressure Symantec into implementing this because Google has control over which certificates Chrome trusts.

The next step is to have certificate transparency for all certificates. This is not easy, because there are so many companies that issue certificates, and there are many existing certificates used for all kinds of things.

One way to smoothly transition to certificate transparency on all HTTPS certificates is to make it optional, but mark it if a host supports it. You could put your certificate in a log and then tell all clients that visit your site that they should only accept certificates that have been put in the log. A new [Expect-CT](https://github.com/bifurcation/expect-ct/blob/master/draft-stark-expect-ct.md) header does exactly this. If a web site responds with this header, the browser will only accept logged certificates from that time on.

Note that this is a trust-on-first-use mechanism. It only works if you have already been to the web site once. In that respect it is similar to the [Strict-Transport-Security header](https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security).

## Conclusion

Certificate transparency is cool from a technological standpoint, with Merkle hash trees that provide cryptographic proof that you have submitted your certificate. It is also cool from a political and economic standpoint. It can only work if domain owners have leverage over certificate issuers. Google has that leverage through their Chrome browser, and they also have an interest in keeping certificates for their services secure. If Google did not have a popular browser, it would be a lot harder for them to get certificate transparency off the ground.

## Read more

* [Certificate Transparency web site](https://www.certificate-transparency.org/)
* [A method to do TLS on IoT devices](/2019/07/31/a-method-for-tls-on-iot-devices/)
* [Book review: Bulletproof SSL and TLS](/2017/01/05/book-review-bulletproof-ssl-tls/)