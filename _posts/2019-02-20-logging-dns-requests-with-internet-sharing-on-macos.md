---
layout: post
title: "Logging DNS requests with internet sharing on macOS"
thumbnail: bookkeeping-480.jpg
date: 2019-05-22
---

When using Internet Sharing on macOS, the DNS log can provide insight in the behavior of connected Wi-Fi devices. This article describes how to view the DNS log.

## Introduction

Internet Sharing on macOS is a easy way to intercept Wi-Fi traffic. It sets up an access point using the built-in Wi-Fi adapter and forwards all traffic to another network interface. Traffic can be viewed using Wireshark and HTTP requests can be [forwarded to Burp](/2016/08/04/intercepting-requests-from-a-smartphone/). Additionally, when enabling Internet Sharing a DNS server is started using mDNSResponder. The logs of this DNS server can help in analyzing traffic by showing which hosts are requested by the device on the Wi-Fi.

## View DNS logging

The logs for mDNSResponder can be viewed by executing the following commands:

    $ sudo log config --mode "private_data:on"
    $ log stream --predicate 'process == "mDNSResponder"' --info

The first command enables the showing of private data. Since DNS queries may be sensitive, these are hidden by default. The second command streams the log of the mDNSResponder process. This can be filtered using `grep` to get only the DNS queries:

    $ log stream --predicate 'process == "mDNSResponder"' --info | grep ": Question"
    2019-02-20 ... Question cps.c2dms.com. (Addr)
    2019-02-20 ... Question www.google.com. (Addr)
    2019-02-20 ... Question sjoerdlangkemper.nl. (Addr)
    2019-02-20 ... Question www.sjoerdlangkemper.nl. (Addr)

## Tweaking logging

The manual of mDNSResponder (`man mDNSResponder`) gives some additional commands to tweak the logging output:

    $ sudo killall -USR1 mDNSResponder
    $ sudo syslog -c mDNSResponder -i

However, these commands were not necessary for me. The above `log stream` command worked right away.

## Conclusion

With a simple command the log of mDNSResponder can be viewed, which may provide an easier way to view DNS queries than with Wireshark.
