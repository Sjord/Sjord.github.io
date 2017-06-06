---
layout: post
title: "Prevent session hijacking with token binding"
thumbnail: coupling-240.jpg
date: 2017-07-05
---

## Session hijacking

When you log in to a web application, you normally get a cookie with a session identifier. This random token identifies to the server that subsequent requests come from you. The server remembers you are logged in, and grants requests with that token access to your resources. Since this token is the only thing that distinguishes your requests from all other requests, anyone who has this token can impersonate you. If the session identifier is compromised, someone else can take over your session.

The session identifier is known both in the browser and on the server, and is sent with every request. This is a big attack surface. Token binding aims to reduce this attack surface.

## Token binding

Token binding works the same as a session cookie in that it sends some token to the server with every request. This token is sent in the Sec-Token-Binding header. The difference is that the server can verify if the sender of the token is really the owner. This makes it much harder to steal a session token.

The way this works is that the browser creates a public-private key pair. It signs something (the tls\_unique value) and sends this signature along with the public key to the server. The server verifies the signature against the public key. This way, the server knows that this client is is possession of the private key.








* Bearer tokens, possession
* Private keys stored in hardware makes DRM possible.
* Token binding doesn't protect against a permanent MitM, and works poorly with an intermittent intentional MitM.
* Token binding is an evolution of channel ID.
* Federation support

Read more:
* [Update Fetch to support Token Binding](https://github.com/whatwg/fetch/pull/325)
* [Token Binding over HTTP](https://datatracker.ietf.org/doc/draft-ietf-tokbind-https/)
* [Introducing Token Binding](https://docs.microsoft.com/en-us/windows-server/security/token-binding/introducing-token-binding)
