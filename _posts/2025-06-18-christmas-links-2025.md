---
layout: post
title: "Links for Christmas 2025"
thumbnail: chain-480.jpg
date: 2025-12-25
---

- [ZendTo NDay Vulnerability Hunting - Unauthenticated RCE in v5.24-3 <= v6.10-4](https://projectblack.io/blog/zendto-nday-vulnerabilities/): Jay checked ZendTo's changelog for security fixes and rediscovered an RCE fixed in 2021. When uploading a file, PHP saves it to a temporary file and puts the filename in `$_FILES['tmp_name']`. This is the only trusted input field in what normally consists of user input. ZendTo mixes data in `$_FILES` with posted data from `$_POST`, making it possible to overwrite the temporary file name. Since this file name is used in a shell command, it results in RCE.
- [Impossible XXE in PHP](https://swarm.ptsecurity.com/impossible-xxe-in-php/): Aleksandr exploits XXE, bypassing four different security features and diving into the libxml2 source code to do so.
- [Introducing lightyear, a new way to dump PHP files](https://blog.lexfo.fr/lightyear-file-dump.html): PHP filter chains again, with blind exfiltration.
- [Getting RCE on Monero forums with wrapwrap](https://swap.gs/posts/monero-forums/): more PHP filter chains, which are now used to make a PHP file look like an image so that it will pass the server-side validation and is offered for download by the server.
- [Avoiding downtime: modern alternatives to outdated certificate pinning practices](https://blog.cloudflare.com/why-certificate-pinning-is-outdated/): certificate pinning has its problems, especially when the certificate lifetime is going to be reduced in the coming years. This post describes that pinning is no longer cool, and how to correctly secure your certificates. Certificate pinning slightly increases security while providing big availability risks. Because the security department is only judged on security and not on availability, they will argue in favor of security pinning, even though it could be a net negative. Also, companies typically misjudge how good they are in managing certicates correctly.
- [The double standard of webhook security and API security](https://www.speakeasy.com/blog/webhook-security): this post is trash. The [post it links in the first sentence](https://ngrok.com/blog-post/get-webhooks-secure-it-depends-a-field-guide-to-webhook-security) is also trash. However, it poses a good question: what are the security benifits (if any) of signing requests. If there are none, why do so many applications use signed requests? Can we confidently say that API keys are sufficiently secure?
- 