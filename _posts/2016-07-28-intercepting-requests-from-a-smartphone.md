---
layout: post
title: "Intercepting smartphone HTTP requests on MacOS"
thumbnail: traffic-officer-240.jpg
date: 2016-08-04
---

If you are testing a mobile app on a smartphone, you want to intercept all HTTP requests with Burp Suite. However, not all mobile apps respect the proxy settings, making it necessary to have another way to intercept all traffic. This post describes a solution using Internet Sharing on MacOS, and using PF to forward all traffic to Burp.

## Situation

You have a smartphone which does some HTTP(S) requests that you want to see in your Burp or other intercepting proxy. You also have a MacBook. We are going to configure the MacBook as a WiFi hotspot and connect the phone to it, so that all internet traffic goes through the MacBook. Then we use PF (packet filter) to send that traffic through Burp.

## Enable Internet Sharing

Under system preferences, open "Sharing". Enable "Internet Sharing". Your MacBook now becomes a WiFi hotspot. Connect with it using your phone and verify that you can browse the internet.

![Sharing dialog with Internet Sharing enabled](/images/internet-sharing.png)

## Forward all traffic to Burp using PF

Create a new file (`pf.rules`) and add the following content:

    rdr pass on bridge100 inet proto tcp from any to any -> 127.0.0.1 port 8080 

Here `bridge100` is the name of the WiFi interface. You can get a list of interfaces by running `ifconfig` in the terminal. The last part, `127.0.0.1 port 8080` is the location of your proxy.

Enable this rule using the following command:

    sudo pfctl -f pf.rules

Where `pf.rules` is the file you just created. You may get an error about ALTQ support, this is not important. Furthermore, PF and IP forwarding needs to be enabled, but this should be done automatically when you enabled Internet Sharing.

We have just overwritten all other PF rules, and it warns about that:

    pfctl: Use of -f option, could result in flushing of rules
    present in the main ruleset added by the system at startup.

Actually we should use an anchor to attach a sub-ruleset with our rules to the main ruleset, but this post will not go into detail on how to do this.

## Enable transparent mode in Burp

Normally when using Burp we configure it as a proxy. We haven't done so, but still send all our requests to it. Since proxy requests differ slightly from normal requests, we have to tell Burp to handle the normal requests by enabling the invisible proxy mode. Under proxy options, edit the listener and check the bottom checkbox in this screen:

![Support invisible proxying](/images/burp-invisible-proxy.png)

Done! Now all HTTP requests that your phone makes are intercepted in Burp.

## Redirecting traffic to another network

If you have Burp running somewhere else, for example in a virtual machine, just editing the IP address in the PF rule is not going to work. Redirecting traffic onto another network works, but the return address does not match anymore. 

Consider we have our Burp running on 172.16.122.128, and we change the destination address on our PF rule:

    rdr pass on bridge100 inet proto tcp from any to any -> 172.16.122.128 port 8080

Now all traffic is forwarded to this address, but the return address on each packet is still that of the phone, which is something like 192.168.2.4. Burp can't send any packets back because it does not know how to reach this address. For this reason, we need network address translation (NAT). The return address on each packet from the phone is changed to be from the laptop, and each packet returned by Burp is forwarded to the phone. Use these PF rules:

    nat on vmnet8 from bridge100:network to any -> (vmnet8)
    rdr pass on bridge100 inet proto tcp from any to any -> 172.16.122.128 port 8080 

Here, `vmnet8` is the interface to Burp, `bridge100` is the WiFi interface to the phone.

## Conclusion

Using Internet Sharing and some PF rules it is possible to forward all traffic to Burp without setting any proxy settings.

Some more information:

* [OSx PF Manual](http://murusfirewall.com/Documentation/OS%20X%20PF%20Manual.pdf)
* [Parsia Hakimian's Thick Client Proxying blog posts](https://parsiya.net/categories/thick-client-proxying/)
