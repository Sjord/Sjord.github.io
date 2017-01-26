---
layout: post
title: "Bypass IP block with X-Forwarded-For header"
thumbnail: road-block-240.jpg
date: 2017-03-01
---


## IP blocks

Some web applications make it possible to restrict access based on IP address of the visitor. This is particularly common for administrator interfaces. It is a good idea to restrict this interface to the IP addresses that are known to be used by actual administrators.

To implement this, the web application will check the `REMOTE_ADDR` value that the web server passes through to the application.

## Proxies

If the visitor is using a proxy, the `REMOTE_ADDR` field will contain the address of the proxy instead of the visitor. To be able to see the address of the visitor, many proxies add a header to the request with this address. This header is names `X-Forwarded-For` and contains the IP address of the client that connected to the proxy. The web application can now check the `X-Forwarded-For` header to determine the IP address of the client.

## Bypassing the IP block

The `X-Forwarded-For` header is usually set by a proxy, but it can also be added by an attacker. By adding his own `X-Forwarded-For` header, the attacker can spoof his IP address. If the IP block is implemented incorrectly, it can be bypassed by putting an allowed IP address in the header, even if the connection actually originated from a blocked IP address.

Some vulnerable projects:

* [ultra-throttle](https://github.com/atsid/ultra-throttle/blob/master/src/getIpAddress.js#L5)
* [linkto](https://github.com/nindalf/linkto/blob/master/middleware.go#L84)
* [JSPWiki](https://github.com/apache/jspwiki/blob/master/jspwiki-war/src/main/java/org/apache/wiki/util/HttpUtil.java#L54)
* [EpochTalk](https://github.com/epochtalk/epochtalk/blob/master/server/plugins/blacklist/index.js#L14)
* [Moodle](https://github.com/moodle/moodle/blob/master/lib/moodlelib.php#L8818)
