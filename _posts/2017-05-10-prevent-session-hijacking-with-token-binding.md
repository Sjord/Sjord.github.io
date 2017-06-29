---
layout: post
title: "Prevent session hijacking with token binding"
thumbnail: coupling-240.jpg
date: 2017-07-05
---

In web applications, anyone that possesses the session token has access to the session. Token binding introduces a cryptographic token where this is no longer the case, thus making it harder to hijack sessions by obtaining the session identifier.

## Session hijacking

When you log in to a web application, you normally get a cookie with a session identifier. This random token identifies to the server that subsequent requests come from you. The server remembers you are logged in, and grants requests with that token access to your resources. Since this token is the only thing that distinguishes your requests from other requests, anyone who has this token can impersonate you. If the session identifier is compromised, someone else can take over your session.

The session identifier is known both in the browser and on the server, and is sent with every request. This is a big attack surface.

## Using a key pair as token

Token binding makes session hijacking harder by creating an identifier that is based on a private key. The client generates a public/private key pair for every site that it wants to use token binding on. When it connects to the server it signs something and sends this signature along with the public key to the server. The server verifies the signature against the public key. This way, the server knows that this client is in possession of the private key.

After this verification step, the public key is passed to the application. This public key uniquely identifies the client, just as a session cookie would. However, it is no longer possible to simply steal the identifier and impersonate someone. The private key is kept secret and the identifier is checked against it. Without access to the private key it is not possible to reproduce a valid identifier.

Even if an attacker intercepts the signature, he can't use this in another connection. The signature is over the public key and the keying material of the current TLS connection. When a new TLS connection is created, a new signature is needed. This means that if the attacker intercepts the signature, he can't reuse it in a new connection to the server.

<img src="/images/token-binding.svg" class="fullwidth">

## Using token binding

To use token binding you need a supporting client and server. Currently Edge and [Chrome](https://www.chromestatus.com/feature/5097603234529280) support token binding. Both [Apache](https://github.com/zmartzone/mod_token_binding) and [Nginx](https://github.com/google/ngx_token_binding) have token binding modules. If the connection successfully negotiated token binding, an extra header is sent with each request: Sec-Token-Binding. The value for this header contains a public key and a signature. The server module checks the signature and passes the public key to the application layer.

In the application, it is of no use to check the Sec-Token-Binding header, since any client can set this. Instead, check the value that has been passed by the server module. For example, the Apache module sets an environment variable `Token-Binding-ID-Provided`. This variable is only set if the signature is correct. It contains information on the public key, but that is not really important for the application. The application can handle it as being the opaque identifier for this client.

The most straightforward way to use this in an application is to store the token binding identifier in the session when authenticating, and check it in each subsequent request. This way, the session is only accessible by the client in possession of the private key.

## Offered protection

If someone steals the private key, he can impersonate a user. The improved security token binding offers is because the private key is easier to protect than a cookie, since it is not sent over the wire. It can not be obtained with an XSS attack or by impersonating the server.

Token binding somewhat protects against a man-in-the-middle attack. If the user visited the application once without a man-in-the-middle on the connection, the application knows the user's identifier. This identifier changes or disappears as soon as there is a man-in-the-middle. However, it is generally not detected if the man-in-the-middle is present even on the first visit.

If a company man-in-the-middles all their employees, as is sometimes done, it is still possible to use token binding. However, if an employee takes their laptop home to a network without a man-in-the-middle attacker, the identifier will change and the user will be logged out. Token binding doesn't work well with intermittent man-in-the-middle attacks.

## Privacy and DRM

With token binding it is possible to cryptographically identify clients. This improves session security, but also makes it possible to track users and even implement DRM. It is possible to put the private key used in the token binding on a [TPM](https://en.wikipedia.org/wiki/Trusted_Platform_Module), from where it can't be extracted. If a service then uses token binding based on that key, it is only possible to access the service using that device. This restricts the user to use the service with other devices, which is viewed by some to be overly restrictive. To prevent this, Chrome only implements keys that can be copied to other devices:

> Even though some implementors may wish to make Token Binding keys non-extractable by backing them with hardware, Chrome has chosen to only provide software-backed keys. This decision, along with the fact that the spec doesn’t provide any attestation mechanism, should lessen any concerns about using Token Binding for DRM.

## Federation support

OpenId connect is an example of a federated login protocol: logging in on one application gives access to another application. After you log in on the identity provider, it passes some signed data to the relying party that identifies you as a logged-in user. If you want to make use of token binding, this signed data should also contain information on the token binding identifier. Since the identity provider should put the identifier of the relying party in the signed data, there has to be some way to pass the identifier of the relying party to the identity provider. And there is.

When the relying party redirects to the login page on the identity provider, it sends the `Include-Referred-Token-Binding-ID` response header. This triggers the browser to send the relying party token to the identity provider.

## Conclusion

Token binding improves client authentication compared to bearer tokens. A private key is used to create an identifier that authenticates a client. Since the private key is not sent over the wire and not shared with the server, it is easier to handle securely.

## Read more

Specifications:

* [TLS Extension for Token Binding Protocol Negotiation](https://datatracker.ietf.org/doc/draft-ietf-tokbind-negotiation/)
* [The Token Binding Protocol Version 1.0](https://datatracker.ietf.org/doc/draft-ietf-tokbind-protocol/)
* [Token Binding over HTTP](https://datatracker.ietf.org/doc/draft-ietf-tokbind-https/)

Other:

* [Update Fetch to support Token Binding](https://github.com/whatwg/fetch/pull/325)
* [Introducing Token Binding (on Windows)](https://docs.microsoft.com/en-us/windows-server/security/token-binding/introducing-token-binding)
* [Token Binding concerns and mitigations](https://docs.google.com/document/d/11lZGt584NbaJKGPVg080UjHv0DzanlyKgfsRp933AwA/edit)
