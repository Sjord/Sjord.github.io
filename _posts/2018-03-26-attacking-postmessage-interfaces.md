---
layout: post
title: "Detecting and attacking postmessage interfaces"
thumbnail: postes-240.jpg
date: 2018-05-09
---

TODO intro

<!-- photo source https://pxhere.com/en/photo/814182 -->

## A new interface to web pages

> Any window may access this method on any other window, at any time, regardless of the location of the document in the window, to send it a message. Consequently, any event listener used to receive messages **must** first check the identity of the sender of the message, using the `origin` and possibly `source` properties. This cannot be overstated: **Failure to check the `origin` and possibly `source` properties enables cross-site scripting attacks.**

Chrome Devtools, under Sources -> Global Listeners

* [Window.postMessage() on MDN](https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage)
* [The pitfalls of postMessage](https://labs.detectify.com/2016/12/08/the-pitfalls-of-postmessage/), [postMessage XSS on a million sites](https://labs.detectify.com/2016/12/15/postmessage-xss-on-a-million-sites/)
* [Hunting postMessage Vulnerabilities (PDF)](https://www.sec-1.com/blog/wp-content/uploads/2016/08/Hunting-postMessage-Vulnerabilities.pdf)
* [Hacking Slack using postMessage and WebSocket-reconnect to steal your precious token](https://labs.detectify.com/2017/02/28/hacking-slack-using-postmessage-and-websocket-reconnect-to-steal-your-precious-token/)
* [Grammarly: auth tokens are accessible to all websites](https://bugs.chromium.org/p/project-zero/issues/detail?id=1527&desc=3)
* [LastPass: websiteConnector.js content script allows proxying internal RPC commands ](https://bugs.chromium.org/p/project-zero/issues/detail?id=1209)
* [Security Risks Arise From Insecure Implementations of HTML5 postMessage() API](https://securingtomorrow.mcafee.com/technical-how-to/security-risks-arise-insecure-implementations-html5-postmessageapi/)
* [MessPostage browser extension on GitHub](https://github.com/Sjord/messpostage)
