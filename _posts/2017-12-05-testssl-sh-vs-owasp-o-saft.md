---
layout: post
title: "Testssl.sh vs. OWASP O-saft"
thumbnail: testssl-osaft-240.jpg
date: 2018-01-03
---

An endpoint that supports SSL can support several versions, ciphers and algorithms, all of which have different security properties. There are several tools available to test whether a SSL-supporting server is configured securely. In this post we compare two of them, [testssl.sh](https://testssl.sh/) and [OWASP O-saft](https://www.owasp.org/index.php/O-Saft).

## Testssl.sh

Testssl.sh is a bash script that uses the `openssl` command to set up SSL connections and test which ciphers are supported. It ships with a version of OpenSSL that supports many deprecated ciphers, so that it is possible to test whether a server supports those.

It supports many checks and feels like a solid product, which is somewhat surprising for a bash script of 13,000 lines of code.

## OWASP O-saft

O-saft is written in Perl, and uses a Perl library, Net::SSLeay, to set up the SSL connection. If that library doesn't support the SSL version or cipher, O-saft can't test for it and it tells you so on startup:

    **WARNING: SSL version 'SSLv2': not supported by Net::SSLeay; not checked
    **WARNING: SSL version 'SSLv3': not supported by Net::SSLeay; not checked
    **WARNING: SSL version 'TLSv13': not supported by Net::SSLeay; not checked
    **WARNING: 7 data and check outputs are disbaled due to use of '--no-out':

It has a great number of checks, which output yes or no. Here, "yes" is always good and "no" is always bad, which means that there are some confusing double negations in the output:

    Certificate Fingerprint is not MD5: 	yes
    Certificate is not expired:         	yes
    Certificate is not root CA:         	yes
    Certificate is not self-signed:     	no (num=20:unable to get local issuer certificate)
    Certificate does not contain wildcards:	no (<<CN:>>*.badssl.com *.badssl.com)

## RC4 warnings

The service [BadSSL](https://badssl.com/) offers several insecurely configured servers, such as [rc4-md5.badssl.com](https://rc4-md5.badssl.com/), that are useful for testing tools like these. How do testssl.sh and O-saft compare when scanning this?

Testssl.sh warns about this four times, with red text and capitals:

    Weak 128 Bit ciphers (SEED, IDEA, RC[2,4])    offered (NOT ok)
    Negotiated cipher            RC4-MD5
    BREACH (CVE-2013-3587)                    potentially NOT ok, uses gzip HTTP compression. - only supplied "/" tested
    RC4 (CVE-2013-2566, CVE-2015-2808)        VULNERABLE (NOT ok): RC4-MD5 

O-saft also checks against unsafe RC4 ciphers:

    RC4-MD5                     	yes	weak
    RC4-MD5                     	yes	weak
    RC4-MD5                     	yes	weak
    Selected Cipher:                    	RC4-MD5 weak
    Target does not accept RC4 ciphers: 	no ( TLSv1:RC4-MD5 TLSv11:RC4-MD5 TLSv12:RC4-MD5)
    Connection is safe against RC4 attack:	no ( TLSv1:RC4-MD5 TLSv11:RC4-MD5 TLSv12:RC4-MD5)

It calls it a weak cipher, and several checks result in "no", which is bad. However, it doesn't properly indicate how bad. Other checks that result in no are completely benign, for example if you use a wildcard certificate:

    Certificate is valid according given hostname:	no (*.badssl.com <> rc4-md5.badssl.com)

## Conclusion

Use [testssl.sh](https://testssl.sh/). It does more checks and has better output.
