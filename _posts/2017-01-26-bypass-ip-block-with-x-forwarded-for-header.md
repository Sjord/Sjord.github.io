---
layout: post
title: "Bypass IP blocks with the X-Forwarded-For header"
thumbnail: road-block-240.jpg
date: 2017-03-01
---

Sometimes the IP address is used for access control or rate limiting. If the client is behind a proxy, the proxy forwards the IP address of the client to the server in a specific header, `X-Forwarded-For`. In some cases, a client can use this header to spoof his IP address.

## IP blocks

Some web applications make it possible to restrict access based on IP address of the visitor. This is particularly common for administrator interfaces. It is a good idea to restrict this interface to the IP addresses that are known to be used by actual administrators.

To implement this, the web application will check the `REMOTE_ADDR` value that the web server passes through to the application.

## Proxies

If the visitor is using a proxy, the `REMOTE_ADDR` field will contain the address of the proxy instead of the visitor. To be able to see the address of the visitor, many proxies add a header to the request with this address. This header is names `X-Forwarded-For` and contains the IP address of the client that connected to the proxy. The web application can now check the `X-Forwarded-For` header to determine the IP address of the client.

## Bypassing the IP block

The `X-Forwarded-For` header is usually set by a proxy, but it can also be added by an attacker. By adding his own `X-Forwarded-For` header, the attacker can spoof his IP address. If the IP block is implemented incorrectly, it can be bypassed by putting an allowed IP address in the header, even if the connection actually originated from a blocked IP address.

Below are some examples of projects that trust the `X-Forwarded-For` header. Note that this not always indicates a vulnerability. There are some configurations where the `X-Forwarded-For` header can be trusted, for example if it is set by a reverse proxy on the same host as the web application.

* [Moodle](https://github.com/moodle/moodle/blob/master/lib/moodlelib.php#L8818)
* [JSPWiki](https://github.com/apache/jspwiki/blob/master/jspwiki-war/src/main/java/org/apache/wiki/util/HttpUtil.java#L54)
* [ultra-throttle](https://github.com/atsid/ultra-throttle/blob/master/src/getIpAddress.js#L5)
* [linkto](https://github.com/nindalf/linkto/blob/master/middleware.go#L84)
* [EpochTalk](https://github.com/epochtalk/epochtalk/blob/master/server/plugins/blacklist/index.js#L14)

## Solution

When using an IP block, a good approach is to check *all* given IP addresses against the block. Deny access if either `REMOTE_ADDR` or `X-Forwarded-For` matches the IP block. This also makes it harder for somebody to circumvent the block by using a proxy.

If this is not possible, the application should be configured to either trust or ignore the `X-Forwarded-For` header. For example, Etherpad [has an option](https://github.com/ether/etherpad-lite/blob/master/src/node/handler/SocketIORouter.js#L64) that enables usage of the header.

## Conclusion

When testing an application it is worth the try to pass an `X-Forwarded-For` header to block IP blocks or rate limiting. Applications should only trust this header in specific situations.
