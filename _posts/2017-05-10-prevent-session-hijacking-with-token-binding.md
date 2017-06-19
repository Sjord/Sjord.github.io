---
layout: post
title: "Prevent session hijacking with token binding"
thumbnail: coupling-240.jpg
date: 2017-07-05
---

## Session hijacking

Token binding makes it harder to take over a session by stealing the session identifier.

When you log in to a web application, you normally get a cookie with a session identifier. This random token identifies to the server that subsequent requests come from you. The server remembers you are logged in, and grants requests with that token access to your resources. Since this token is the only thing that distinguishes your requests from all other requests, anyone who has this token can impersonate you. If the session identifier is compromised, someone else can take over your session.

The session identifier is known both in the browser and on the server, and is sent with every request. This is a big attack surface. Token binding aims to reduce this attack surface.

## Token binding

Token binding makes session hijacking harder by basing the token of a private key. The client generates a public/private key pair for every site that it wants to use token binding on. When it connects to the server it signs something and sends this signature along with the public key to the server. The server verifies the signature against the public key. This way, the server knows that this client is is possession of the private key.

After this verification step, the public key is passed to the application. This public key uniquely identifies the client, just as a session cookie would. However, it is no longer possible to simply steal the identifier and impersonate someone. The private key is kept secret and the identifier is checked against it, so without access to the private key it is not possible to hijack a session identifier.

## Using token binding

To use token binding you need a supporting client and server. Currently Edge and [Chrome](https://www.chromestatus.com/feature/5097603234529280) support token binding. Both [Apache](https://github.com/zmartzone/mod_token_binding) and [Nginx](https://github.com/google/ngx_token_binding) have token binding modules. If the connection successfully negiotiated token binding, an extra header is sent with each request: Sec-Token-Binding. The value for this header contains a public key and a signature. The server module checks the signature and passes the public key to the application layer.

In the application, it is of no use to check the Sec-Token-Binding header, since any client can set this. Instead, check the value that has been passed by the server module. For example, the Apache module sets an environment variable Token-Binding-ID-Provided. This variable is only set if the signature is correct. It contains information on the public key, but that is not really important for the application. The application can handle it as being the opaque identifier for this client.

## Cryptographic protection

The Sec-Token-Binding header contains the public key and a signature of some data including the currently used TLS variables. By including the keying material of the TLS connection in the signature, a replay attack is prevented. The signature only works on the current TLS connection.

## Offered protection

* Token binding doesn't protect against a permanent MitM, and works poorly with an intermittent intentional MitM.

## Privacy and DRM

* Private keys stored in hardware makes DRM possible.

## Federation support



* Bearer tokens, possession
* Token binding is an evolution of channel ID.

Read more:
* [Update Fetch to support Token Binding](https://github.com/whatwg/fetch/pull/325)
* [Token Binding over HTTP](https://datatracker.ietf.org/doc/draft-ietf-tokbind-https/)
* [Introducing Token Binding](https://docs.microsoft.com/en-us/windows-server/security/token-binding/introducing-token-binding)
