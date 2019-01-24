---
layout: post
title: "A USB Wi-Fi adapter that works with MacOS Mojave"
thumbnail: tplink-adapter-480.jpg
date: 2019-02-20
---

This article describes my experiences with the TP-Link TL-WN823N USB Wi-Fi adapter under MacOS Mojave.

For an IoT assignment I wanted to intercept Wi-Fi traffic between two devices. In order to route the traffic through my laptop I wanted to connect to one device and set up an access point for another device. For this I needed an additional Wi-Fi adapter. It took some searching to find one that works with MacOS Mojave 10.14, and I ended up with the TP-Link TL-WN823N, which works perfectly.

For the adapter to work, you need to install the drivers from the TP-Link website. The driver for MacOS 10.14 is marked as beta at the moment. The adapter does not integrate with the Wi-Fi functionality of MacOS. Instead, you get an extra menu item in the top bar.

It's not possible to set up an access point with this adapter on MacOS. You can use internet sharing by sharing your connection on the TP-Link through your Mac's built-in WiFi, but not the other way around: you can't do internet sharing over the TP-Link adapter.

Connecting to Wi-Fi networks and sending and receiving data works well.

More info on the [TP-Link](https://www.tp-link.com/us/download/TL-WN823N.html) website.

<img src="/images/tplink-adapter-menu.jpg" width="396">

<img src="/images/tplink-adapter-open.jpg" width="680">
