---
layout: post
title: "Modifying HTTP requests with Tamper Chrome"
thumbnail: chrome-unresponsive-240.png
date: 2017-08-30
---


To hack a web application you need to send all kinds of HTTP requests to it. There are several tools available to intercept and tamper with HTTP requests. Two of the most popular such tools, Burp and ZAP, are intercepting proxies. You configure the browser to connect to the intercepting proxy, and there you can view and modify requests. Tamper Chrome, in contrast, is implemented as a browser plugin and works from within the browser. This is another method of implementing functionality to tamper with HTTP requests, that has some interesting consequences.

To install Tamper Chrome you need to install both an [extension](https://chrome.google.com/webstore/detail/tamper-chrome-extension/hifhgpdkfodlpnlmlnmhchnkepplebkb) and an [application](https://chrome.google.com/webstore/detail/tamper-chrome-application/odldmflbckacdofpepkdkmkccgdfaemb). Installation can be done in seconds, although it is a little bit cumbersome that two things need to be installed.

After installation, Tamper Chrome adds a tab to the developer tools. In this tab, it offers several tools that can be enabled separately:

* Block / Reroute Requests
* Request Headers
* Response Headers
* Monitor PostMessages
* Monitor Reflected XSS
* Replay Requests (Experimental)

Several of these tools make it possible to intercept and tamper requests, but there are some tools that particularly make use of the close integration with the browser.


## Monitor Reflected XSS

<img src="/images/tamperchrome-xss-detected.png">

## Monitor PostMessages


> I think Tamper Chrome is similar to Burp and ZAP. The reason I made Tamper Chrome originally was to be able to work (I work in Google's Security Team) from my Chromebook. However, I now a days use Tamper Chrome from my Linux workstation too, mostly because I personally find it more convenient than Burp since it can be enabled per-tab. Also, as part of my work, I've had to add some other features to Tamper Chrome, such as monitoring for postMessage, and adding a few features that make debugging XSS bugs easier. My coworkers usually end up using it too, because it takes 15 seconds to install (vs. Burp which you need to download, get java, configure, restart, etc..). So generally, I guess I find my work easier to do with Tamper Chrome, but that's because I've essentially built it for me :)



Web server on http://localhost:34013/
Checks the Origin header to prevent CSRF.
Only binds to localhost.


* Works in ChromeOS
* Enabled per tab

## The future of Tamper Chrome

> Generally, I'm not looking to build a tool to replace Burp or ZAP for everyone but rather to build a tool that helps me do my job doing web security pentesting, so many features that are very popular in Burp or ZAP that aren't needed for the type of work I do, are unlikely to be implemented (eg, the Repeater in Burp, I usually just implement that in JavaScript myself with a for loop in the JavaScript console :-).


* [Tamper Chrome (extension)](https://chrome.google.com/webstore/detail/tamper-chrome-extension/hifhgpdkfodlpnlmlnmhchnkepplebkb)
* [Tamper Chrome (application)](https://chrome.google.com/webstore/detail/tamper-chrome-application/odldmflbckacdofpepkdkmkccgdfaemb)
