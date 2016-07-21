---
layout: post
title: "Fuzzing SAML with SAMLReQuest"
thumbnail: door-240.jpg
date: 2016-07-21
---

SAML is a single sign-on solution. It uses XML, but this is sometimes encoded in such a way to it hard to deal with in Burp. Luckily, there are some extensions that can decode these request.

## HTTP Redirect binding

SAML allows you to authenticate at one site by logging in on another site. The service provider redirects you to the identity provider. You log in there and the identity provider sends you back to the service provider with a SAML response. You have now authenticated to the service provider without giving it your credentials.

In the first step the service provider gives you a SAML request to sent to the identity provider. This is implemented using a HTTP redirect, to an URL such as this:

    https://idp.example.org/SAML2/SSO/Redirect?SAMLRequest=request

Here, the SAMLRequest get parameter contains some XML that is encoded in several ways:

* The XML is compressed using the [deflate](https://en.wikipedia.org/wiki/DEFLATE) algorithm.
* The result is base64-encoded.
* That result is URL-encoded.

Burp can URL-decode, base64-decode, but it cannot inflate the compressed string. It can decode gzip, but that uses another format and another algorithm.

## Plugins that decode SAML requests

There are several plugins that URL-decode, base64-decode and inflate the request:

* [SAML ReQuest](https://github.com/ernw/burpsuite-extensions/tree/master/SAMLReQuest)
* [SAML Editor](https://github.com/chrismsnz/burp_saml)
* [SAML Encoder](https://github.com/Meatballs1/burp_saml)

The first two add a tab to the request editor, showing the plaintext SAML request for every request that contains one. "SAML Encoder" has a separate tab to encode and decode SAML requests, similar to the decoder. The plugin didn't work for me out of the box, but after some tinkering I got it to work. One nice thing is that it indents the XML so that is easier to read.

With both "SAML ReQuest" and "SAML Editor", editing the XML in the SAML tab also changes the request. This makes it possible to change the SAML request when intercepting or in the repeater.

![The request has an extra "SAML ReQuest" tab](/images/saml-proxy-samlrequest-tab.png)

## Fuzzing SAML request using the intruder

The "SAML ReQuest" is the only plugin that also has support for the intruder, although it's a little bit hacky. Below a SAML request is a button to "Send Decoded Request to Intruder". This will insert the XML right into the request. This only works because SAML ReQuest quietly encodes every SAML request encountered in an intruder request. This means that the request the intruder *shows* is not the actual request the intruder *sends*, which is pretty confusing.

![The SAML request from the intruder is automatically encoded](/images/saml-encoded-request.png)

## Correctly format your request

The SAML ReQuest plugin encodes anything between `<samlp:AuthnRequest` and `</samlp:AuthnRequest>`. This means that if you have an XML declaration such as `<?xml version="1.0" ?>` before the first tag, that is not going to be encoded and the server won't understand your request.

## Conclusion

SAML ReQuest is the only plugin that makes it possible to use encoded SAML in the intruder, although it is not very easy or transparent. Unfortunately, it is not listed in the BApp Store, but can be downloaded from [here](https://github.com/ernw/burpsuite-extensions/tree/master/SAMLReQuest).
