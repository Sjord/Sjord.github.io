---
layout: post
title: "Detecting postMessage interfaces"
thumbnail: postes-240.jpg
date: 2018-05-09
---

The [postMessage mechanism](https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage) provides a JavaScript interface to web pages. This interface is not immediately visible in an intercepting proxy when doing a security assessment. However, as any interface, it can have security issues. This post looks into possible security issues and detecting pages which use this mechanism, so that this interface is not overlooked on a security assessment.

<!-- photo source https://pxhere.com/en/photo/814182 -->

## PostMessage interface

The postMessage interface provides a communication channel between two windows or iframes. By calling the `window.postMessage` function on the target window, a message is sent. The target window can attach an event listener which is triggered whenever a message is received. The page then runs custom JavaScript code to handle the message as it sees fit.

This is typically used to communicate between two windows that belong to the same application. For example, an application may load a single sign-on page in an iframe, and send a "logout" message whenever the user clicks the logout button. It is also possible to return some data, again with a message.

When performing some action in response to a message, it is vital to check whether the message comes from a trusted source. Any site on the internet can load a page in a window or iframe and send any message to it. If the page blindly trusts the message, this could be a security risk. MDN emphasizes to check the origin of the message:

> Any window may access this method on any other window, at any time, regardless of the location of the document in the window, to send it a message. Consequently, any event listener used to receive messages **must** first check the identity of the sender of the message, using the `origin` and possibly `source` properties. This cannot be overstated: **Failure to check the `origin` and possibly `source` properties enables cross-site scripting attacks.**

## Detecting web messaging

If you are a typical web app hacker, you watch your Burp or other intercepting proxy for all HTTP requests. However, postMessage is an interface that exists solely in the browser and doesn't necessarily trigger any HTTP requests. Especially if a page adds a message handler that is unused, it can be vulnerable but remain undetected. So, when testing a web application you need to explicitly check for such message handlers.

In Chrome, this can be done simply using the developer tools.

1. Press F12 to open the developer tools.
1. Click the "Sources" tab.
1. On the right in the debugger pane, click "Global Listeners".
1. Open "message" to show message handlers.

<img src="/images/chrome-message-event-listener.png" alt="Chrome devtools shows message event listeners">

## Using a browser plugin

The method above clearly shows the message handlers, but it would require checking the event listeners on every page. Instead, it would be nice if we got some notification that a page uses messaging, so that we are reminded to test this.

That is the idea behind my [MessPostage browser extension](https://github.com/Sjord/messpostage). As soon as it detects messaging if displays a red icon in the toolbar:

<img src="/images/messpostage-toolbar.png" alt="A red envelope with a red number is shown in the browser toolbar">

The plugin is currently a bit rough, but it works in notifying the user when messages are used. You can install this plugin and then forget about messaging until the icon turns red.

## Conclusion

Web messaging is a mechanism for message passing between windows. It can introduce security vulnerabilities if it blindly trusts messages. The [MessPostage plugin](https://github.com/Sjord/messpostage) reminds you to check for such kind of vulnerabilities whenever a page uses messages.

## Read more

* [Window.postMessage() on MDN](https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage)
* [The pitfalls of postMessage](https://labs.detectify.com/2016/12/08/the-pitfalls-of-postmessage/), [postMessage XSS on a million sites](https://labs.detectify.com/2016/12/15/postmessage-xss-on-a-million-sites/)
* [Hunting postMessage Vulnerabilities (PDF)](https://www.sec-1.com/blog/wp-content/uploads/2016/08/Hunting-postMessage-Vulnerabilities.pdf)
* [Hacking Slack using postMessage and WebSocket-reconnect to steal your precious token](https://labs.detectify.com/2017/02/28/hacking-slack-using-postmessage-and-websocket-reconnect-to-steal-your-precious-token/)
* [Grammarly: auth tokens are accessible to all websites](https://bugs.chromium.org/p/project-zero/issues/detail?id=1527&desc=3)
* [LastPass: websiteConnector.js content script allows proxying internal RPC commands ](https://bugs.chromium.org/p/project-zero/issues/detail?id=1209)
* [Security Risks Arise From Insecure Implementations of HTML5 postMessage() API](https://securingtomorrow.mcafee.com/technical-how-to/security-risks-arise-insecure-implementations-html5-postmessageapi/)
* [MessPostage browser extension on GitHub](https://github.com/Sjord/messpostage)
