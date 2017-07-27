---
layout: post
title: "Hacking from within the browser with Tamper Chrome"
thumbnail: chrome-unresponsive-240.png
date: 2017-08-30
---

Tamper Chrome is an extension for Chrome that makes it possible to modify HTTP requests in order to pentest web applications.

## Introduction

To hack a web application you need to send all kinds of HTTP requests to it. There are several tools available to intercept and tamper with HTTP requests. Most of these tools, such as Burp and ZAP, are intercepting proxies. You configure the browser to connect to the intercepting proxy, and there you can view and modify requests. Tamper Chrome, in contrast, is implemented as a browser plugin and works from within the browser. This is another method of implementing functionality to tamper with HTTP requests, that has some interesting consequences.

## Pentesting from the browser

Pentesting from within Chrome has its advantages. First, it is possible to run it in ChromeOS, so you can pentest from a Chromebook. Secondly, Tamper Chrome can be enabled per tab, so there is no need to have a separate browser for testing and normal browsing. There is also no need to configure proxy settings.

The browser is the obvious place to run a pentesting tool. It already runs the application to be tested. It already provides information on network requests, cookies, local storage, and JavaScript. The browser can become a pentesting IDE instead of just a client to the web application.

## Using Tamper Chrome 

To install Tamper Chrome you need to install both an [extension](https://chrome.google.com/webstore/detail/tamper-chrome-extension/hifhgpdkfodlpnlmlnmhchnkepplebkb) and an [application](https://chrome.google.com/webstore/detail/tamper-chrome-application/odldmflbckacdofpepkdkmkccgdfaemb). Installation can be done in seconds, although it is a bit cumbersome that two things need to be installed.

After installation, Tamper Chrome adds a tab to the developer tools. In this tab, it offers several tools that can be enabled separately:

* Block / Reroute Requests
* Request Headers
* Response Headers
* Monitor PostMessages
* Monitor Reflected XSS
* Replay Requests (Experimental)

Several of these tools make it possible to intercept and tamper requests, but there are some tools that particularly make use of the close integration with the browser.

## Monitor Reflected XSS

The tool to monitor for XSS shows something in the console every time a `<tcxss>` tag or attribute is found. This makes it easy to test for XSS. Simply insert `<tcxss>` in every input field and watch the console if this resulted in XSS. This also works with DOM XSS, where the element is created by JavaScript. In that case, the stack trace of the JavaScript that inserted the element is also shown in the console.

<img src="/images/tamperchrome-xss-detected.png" alt="Tamper Chrome shows that XSS is present on this page">

## Monitor PostMessages

With the [postMessage API](https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage) two sites can communicate cross-origin. Posting a message to another window, usually an iframe, triggers an event listener in the JavaScript of the receiver. This interface is typically ignored by intercepting proxies, since it doesn't result in a HTTP request. However, it can result in security issues since it makes it possible to perform actions within the context of the receiving site.

This is where Tamper Chrome's extension structure comes with a great advantage. Since it runs within the browser, Tamper Chrome does have access to the MessageEvent objects send by postMessage. This makes it possible to see which events are sent and how they are handled. It shows the messages that are being sent in the console, and inserts a breakpoint in the JavaScript that receives the message.


## The future of Tamper Chrome

Tamper Chrome is a little rough around the edges. It's clearly some pentester's own functional tool, and the developer has no aspirations to turn this into a general purpose pentesting product:

> Generally, I'm not looking to build a tool to replace Burp or ZAP for everyone but rather to build a tool that helps me do my job doing web security pentesting, so many features that are very popular in Burp or ZAP that aren't needed for the type of work I do, are unlikely to be implemented (e.g., the Repeater in Burp, I usually just implement that in JavaScript myself with a for loop in the JavaScript console :-).

However, the concept of a pentesting tool in the browser shows much promise. Particularly detecting XSS DOM is something that can be done much easier in the browser than with an intercepting proxy. Intercepting message events sent by postMessage is not possible in an intercepting proxy at all, and this interface often goes untested. Pentesting from the browser offers easy installation and usage and good integration with the runtime environment of the webapp. I think there can be a successful Burp alternative in the browser. However, I don't think Tamper Chrome will be it.

## Try it out

* [Tamper Chrome (extension)](https://chrome.google.com/webstore/detail/tamper-chrome-extension/hifhgpdkfodlpnlmlnmhchnkepplebkb)
* [Tamper Chrome (application)](https://chrome.google.com/webstore/detail/tamper-chrome-application/odldmflbckacdofpepkdkmkccgdfaemb)
