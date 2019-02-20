---
layout: post
title: "Logging DNS requests with internet sharing on macOS"
thumbnail: bookkeeping-480.jpg
date: 2019-09-11
---


    $ sudo log config --mode "private_data:on"
    $ log stream --predicate 'process == "mDNSResponder"' --info



    $ log stream --predicate 'process == "mDNSResponder"' --info | grep ": Question"
    2019-02-20 10:56:49.717962+0100 0x454      Info        0x0                  197    0    mDNSResponder: [com.apple.mDNSResponder:AllINFO] ProxyCallbackCommon: Question cps.c2dms.com. (Addr)
    2019-02-20 10:56:52.287076+0100 0x454      Info        0x0                  197    0    mDNSResponder: [com.apple.mDNSResponder:AllINFO] ProxyCallbackCommon: Question www.google.com. (Addr)
    2019-02-20 10:56:55.237421+0100 0x454      Info        0x0                  197    0    mDNSResponder: [com.apple.mDNSResponder:AllINFO] ProxyCallbackCommon: Question sjoerdlangkemper.nl. (Addr)
    2019-02-20 10:56:55.287805+0100 0x454      Info        0x0                  197    0    mDNSResponder: [com.apple.mDNSResponder:AllINFO] ProxyCallbackCommon: Question www.sjoerdlangkemper.nl. (Addr)

    man mDNSResponder

Is het nodig om logging aan te zetten? Lijkt er niet op

    $ sudo killall -USR1 mDNSResponder
    $ sudo syslog -c mDNSResponder -i
