---
layout: post
title: "Compression side channel attacks"
thumbnail: vice-240.jpg
date: 2016-08-22
---

## How compression works

Compression algorithms such as gzip use two tricks:

* The most often used letters get the shortest representation.
* Any phrase that is repeated only gets stored once.

We will focus on the second trick: if a certain string of characters is repeated somewhere in the text, it is only stored the first time. The second time it occurs a reference is included to the first occurrence.

Consider what happens when we compress this quote:

> Build a man a fire, and he'll be warm for a day. Set a man on fire, and he'll be warm for the rest of his life.

As you can see, there are several parts that occur more than once, particularly the part "fire, and he'll be warm for". Gzip stores this by referencing an earlier position when a repeating phrase is encountered.

![In compressed content, earlier text is referenced later on to avoid storing it again.](/images/compression.png)

This image shows the references of the compressed file. The first red dot in the sentence references an earlier use of " a ".

The key point is that when a text occurs multiple times it is very efficiently compressed. We can use that fact in a compression side channel attack.

## How a compression side channel attack works

A compression side channel attack lets us read some secret data if we only know the compressed size of that data. Furthermore, we need to be able to control some of that data.

For an example, consider a web site that uses compressed and encrypted cookies. Some of the values in the cookie are secret, and some can be set by the user. Because the cookie is encrypted, we cannot read the secret straight away. Assume the plaintext cookie looks like this:

    secret=tops3cr3t&favorite_color=red

Users can set their own favorite color. What happens if we set our favorite color equal to the secret?

    secret=tops3cr3t&favorite_color=tops3cr3t

Because the phrase `tops3cret` now occurs twice, this compresses very efficiently. By just looking at the size of the cookie we can see that our favorite color is present somewhere else in the cookie.

In some cases you can even guess one character at a time. Simply try "a", "b", etc. as your favorite color. When the size of the cookie drops by one byte, you know you have guessed the first character right.

This only works if the secret and the data controlled by the attacker are compressed together, and we can read the size after compression.

## Demonstration page

Try your hacking skills on the [compression side-channel attack demonstration page](http://demo.sjoerdlangkemper.nl/compression.php). This page satisfies the conditions needed for a side-channel attack:

* It uses gzip HTTP compression.
* It reflects part of the input. This gives an attacker control over the compressed data.
* It has a secret on the page.

Of course in this demonstration you can simply see the secret, but the point is that you could also determine it by just looking at the page size alone.

Try searching for "Your secret code is: a", "Your secret code is: b", etc.

## Practical applications on HTTPS: CRIME, BREACH

Consider a situation in where a man-in-the-middle attacker wants to perform some request on behalf of the victim, on a web site that is protected by HTTPS and also has CSRF tokens. We assume the attacker can make requests to the web site, for example by injecting HTML tags or Javascript into other (non-HTTPS) web sites. He can not read the result because it is encrypted, but he can read the size. If the web page also reflects some of his input, he can attack the web site using a compression side-channel attack.

This is the basis for the CRIME and BREACH vulnerabilities, where CRIME depends on compression on the transport layer and BREACH depends on HTTP compression.

With these vulnerabilities, a man-in-the-middle attacker can obtain a CSRF token by performing many requests and looking at the response size. Just like above, he would do requests like "csrftoken=a", "crftoken=b", etc. In order to work, the requested page need to both reflect this input and contain the CSRF token itself.

## Timing attacks: TIME, HEIST

Whereas CRIME and BREACH only work for man-in-the-middle attackers, TIME and HEIST work from the browser for any remote attacker. Instead of reading the size of the network, timing information is used to determine the response size. By carefully timing how long it takes to receive a response, the exact size in bytes can be determined. This can be done with Javascript, so this attack can now be performed from within the browser.

## Protection

The main problem with forged requests is that users are authenticated even for third-party requests. The [same-site cookie flag](/2016/04/14/preventing-csrf-with-samesite-cookie-attribute/) tries to solve this by not sending all cookies when doing a third-party request.

## Conclusion


