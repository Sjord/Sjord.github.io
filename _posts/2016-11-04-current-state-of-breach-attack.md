---
layout: post
title: "The current state of the BREACH attack"
thumbnail: soldiers-240.jpg
date: 2016-11-07
---

In August 2013 the BREACH attack was presented at the Black Hat conference in Las Vegas. With this attack it is possible in some cases to read parts of HTTPS traffic. A serious attack, but to this day there is no good fix for it. How come?

## Introduction

BREACH is a [compression side-channel attack](/2016/08/23/compression-side-channel-attacks/) on traffic that is compressed using HTTP gzip compression and encrypted using TLS. It requires the attacker to be able to read the size of encrypted traffic and perform CSRF requests at will. Furthermore, the site under attack needs to reflect some input in the same response that contains the secret data the attacker wants to read. Normally an intercepting attacker could not read the data because it was encrypted, but with BREACH it is possible to read a little part of the page, such as a CSRF token.

BREACH is a complicated attack, in that it exploits several properties of a connection. HTTPS, gzip compression and reflecting user input are all perfectly safe, but combined they pose a security risk.

This is also the reason that there is no obvious fix. When a security flaw is found in some software, the vendor typically fixes it and it's solved. This is not possible with BREACH, because there is not really a bug. Furthermore, there are several parties involved and  actions to protect against BREACH differ for each web site.

## Mitigations

That does not mean that nobody did anything. In fact, several fixes or mitigations have been developed to protect against BREACH. Most of these make only sense in specific cases, making it hard to provide one best practice to protect against BREACH.

### Disable compression

If you disable compression, you are no longer vulnerable to compression side-channel attacks. This is the most secure way to protect against BREACH, but it comes at a price. Compression is an easy way to improve performance and reduce bandwidth. Disabling it for everyone leads to a large waste of resources.

One possible compromise is to only enable compression on requests where the referrer header indicates that the request originates from the same site. This would disable compression only for cross-origin requests. This is a good trade-off between performance and security.

Compression can be implemented in the web server, the application, or any CDN or proxy server. How to configure compression differs for each setup.

### Hide the response length

BREACH looks at the response size to guess information on the page. By varying the response size, BREACH is no longer possible. Varying the response size is typically done by appending some garbage data of random length to the response.

There are several ways to do this. There is [an nginx module](https://github.com/nulab/nginx-length-hiding-filter-module) that puts some random data in every page. This [rails module](https://github.com/meldium/breach-mitigation-rails) also appends a HTML comment of random size to the page.

A more formal way is coming with [TLS 1.3](https://tlswg.github.io/tls13-spec/#rfc.section.5.4), which supports record padding in order to hide the size of responses. This feature was [proposed](https://tools.ietf.org/html/draft-pironti-tls-length-hiding-00) even before BREACH was presented. While it is a good idea to implement this in the protocol instead of messing with HTML comments, it will still take several years before we have widespread implementation of TLS 1.3.

Padding responses is a little bit of a hack. Appending several KB of random data to each response wastes bandwidth and breaks caching mechanisms.

### Randomize the secret

A typical use case for BREACH is that an attacker can guess a CSRF token. For this to work, the CSRF token needs to be the same for every request the attacker does. If the CSRF token is randomized for every request, it is no longer possible to guess it. Both [Django](https://code.djangoproject.com/ticket/20869) and [Rails](https://github.com/meldium/breach-mitigation-rails) have implemented this. By encoding the CSRF token with some salt, it is no longer the same on each request.

Note this only works for CSRF tokens. Any other secrets on the page need to be handled separately.

### Same-site cookies

With the [same-site cookie flag](/2016/04/14/preventing-csrf-with-samesite-cookie-attribute/), cookies are no longer sent with cross-origin requests. This effectively makes CSRF attacks, and thus also the BREACH attack, impossible. This is really a solution that solves the core of the problem. It is a good best-practice to put this flag on all your cookies, which makes it easy to implement by application developers.

One disadvantage of this is that it is currently only supported in Chrome and Opera.

## Attacker's progress

The attackers also have made some progress.

In 2016 a group of researchers shown that the same attack done by BREACH could also be done from the browser using a timing attack. They named it [HEIST](https://www.blackhat.com/docs/us-16/materials/us-16-VanGoethem-HEIST-HTTP-Encrypted-Information-Can-Be-Stolen-Through-TCP-Windows-wp.pdf). With HEIST, a man-in-the-middle position is no longer needed. Instead, an attacker just needs to be able to run Javascript in the browser of the victim.

Another nice thing for the attackers is a new toolkit that helps with exploiting compression side-channel attacks, [Rupture](https://ruptureit.com/). Two researchers described some improvements to BREACH in [their paper](file:///home/sjoerd/Downloads/asia-16-Practical-New-Developments-In-The-BREACH-Attack-wp.pdf) and developed a tool to exploit it.

## Conclusion

There was no quick response to BREACH, because it is attack that combines several properties of encrypted traffic. There are several hacks that mitigate the situation, and some real solutions. Padding in TLS 1.3 needs some more time, and same-site cookies really need [support for Firefox](https://bugzilla.mozilla.org/show_bug.cgi?id=795346) to be the solution.
