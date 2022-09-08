---
layout: post
title: "Intercepting HTTP requests with mitmproxy"
thumbnail: listening-240.jpg
date: 2018-03-28
---

Mitmproxy is a command-line intercepting proxy. Just like with Burp, you can view and modify requests. It also has some features that distinguish it from other intercepting proxies. In this post, we will look into three features unique to mitmproxy.

<!-- photo source: https://www.flickr.com/photos/dotbenjamin/2843144877 -->

## Use over SSH

Because it runs on the command line, mitmproxy can be run on a remote server over SSH. If you ever want to intercept HTTP traffic in a remote network, mitmproxy can help out. Since mitmproxy has [binaries](https://github.com/mitmproxy/mitmproxy/releases) with Python 3 and OpenSSL included, [installing](https://docs.mitmproxy.org/stable/overview-installation/) is as easy as extracting the package.

## Replaying traffic

Mitmproxy and mitmdump can be used to record and replay HTTP traffic.

### Recording traffic

From the mitmproxy interface, it is possible to save the intercepted traffic to a file. This can be streamlined using mitmdump, which purpose is to save HTTP traffic to a file.

    $ mitmdump --mode reverse:https://www.sjoerdlangkemper.nl/ -w traffic.mitm
    Proxy server listening at http://[::]:8080
    172.16.122.1:51049: clientconnect
    172.16.122.1:51049: GET https://www.sjoerdlangkemper.nl/
                     << 304 Not Modified 0b
    ...
    ^C
    $

This saves the intercepted requests to `traffic.mitm`. The file format is specific to mitmproxy, so you can't use it with other tools. You can use mitmproxy to view the requests contained in the file:

    $ mitmproxy -r traffic.mitm

### Modifying traffic files

You can edit request and responses in mitmproxy, and save the result back to a file. It is also possible to automate modification of traffic files using [filters](https://docs.mitmproxy.org/stable/concepts-filters/):

    $ mitmdump --no-server -r traffic.mitm -w out.mitm '! ~u jpg$'
    172.16.122.1:51049: GET https://www.sjoerdlangkemper.nl/
                     << 304 Not Modified 0b
    ...

This command will remove all requests where the URL ends in `jpg` and write the result to `out.mitm`.

### Replaying

The following command will replay the requests from traffic.mitm:

    $ mitmdump --client-replay traffic.mitm

It will perform requests, one by one, in order. This can be useful to create a certain test case and replay it a couple of times.

Besides replaying client requests, it is also possible to replay server responses. This could be useful when developing a client that depends on another server.
    
## Reverse proxy mode

Mitmproxy can also run as a reverse proxy, where it pretends its a website. You can start a reverse proxy with the following command:

    mitmproxy --mode reverse:https://www.sjoerdlangkemper.nl/

Now, if you browse to http://localhost:8080/, it will display this website. Any traffic is still intercepted. This could be useful for applications that have no proxy support, but where it is possible to change the URL it retrieves.

## Conclusion

I wouldn't say mitmproxy is the best intercepting proxy all around, but it has some interesting features that make it valueable in some edge cases, where other tools can't be used.
