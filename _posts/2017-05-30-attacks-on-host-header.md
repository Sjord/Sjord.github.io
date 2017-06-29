---
layout: post
title: "Attacks on the Host header"
thumbnail: mailboxes-240.jpg
date: 2017-09-13
---

If a website works even if the host header is missing or incorrect in the request, it may be vulnerable to several kinds of attacks. This post explains the implications of ignoring the host header.

## Host header introduction

When a browser performs a request to a web server, it sends along a Host header with the requested domain as its value. This makes it possible to run multiple websites, or "virtual hosts", on one machine, by serving different content dependent on the value of the host header.

Normally the host header contains the domain name of the requested website. But what if it is missing, or has a value that does not correspond to a site hosted on the server? Then the server can either return an error message, or serve the default website. In the last case, if the website works correctly even if the host header is missing or incorrect, the server exposes itself to several attacks.

## Trusting the server name

If the website works with an arbitrary host header, the client can modify the host header to contain anything. This can introduce a security issue if the host header is then used within the application.

Recently such a vulnerability was found in Wordpress. When a user forgets his password, he can request a password reset email. This email would be sent from the address 'wordpress@hostname.com'. The domain part of this, `hostname.com` in this example, would be determined from the `SERVER_NAME` variable, which contained the value of the host header. An attacker could send a request for a password reset with a host header containing a domain under his control. If a reply was sent to the password reset email, for example because it bounced, this would end up in the attacker's mailbox, and he would have the password reset token.

This example shows that it can be dangerous to trust the contents of the host header.

## DNS rebinding attack

Websites can't read each other's data, because of the same origin policy. A site on domain attacker.com can trigger requests to bank.com, but not read the responses. Much of the security of the web is based on this same origin policy. However, it can be bypassed by temporarily directing the attacker.com domain to the bank.com IP address. This DNS rebinding attack works as follows:

* The victim visits attacker.com. The DNS record for attacker.com points to the IP address of attacker.com, but with a short TTL timeout. The page is loaded in the browser of the client.
* The attacker quickly changes his DNS record to point to the site under attack, bank.com.
* The page in the browser does a request to attacker.com, which now points to bank.com.

This way, the page on attacker.com can perform a request to the bank.com IP address and read the response. Note that the cookies for bank.com are not sent with the request, as the browser still thinks it is talking to attacker.com. This type of CSRF is effective on hosts behind firewalls, where the client has access and the attacker has not.

Strict implementation of HTTPS would prevent this type of attack, since the domain name of the certificate will not match. 

<img src="/images/dns-rebinding.svg">

## Conclusion

The two attacks described above depend on the website working with an arbitrary host header. While not the optimal solution for these vulnerabilities, redirecting the client to the canonical domain name makes these attacks impossible, thus improves security a bit.

## Read more

* [WordPress Potential Unauthorized Password Reset](https://exploitbox.io/vuln/WordPress-Exploit-4-7-Unauth-Password-Reset-0day-CVE-2017-8295.html)
* [DNS rebinding](https://en.wikipedia.org/wiki/DNS_rebinding)
* [Protecting Browsers from DNS Rebinding Attacks](https://crypto.stanford.edu/dns/dns-rebinding.pdf)
