---
layout: post
title: "Adding a HTTP header to Nikto requests"
thumbnail: radio-dish-240.jpg
date: 2016-11-28
---

[Nikto](https://github.com/sullo/nikto) is a tool to scan websites for misconfigurations and vulnerabilities. It does a lot of requests to the target server. Sometimes you want to add a custom HTTP header to these requests. This article explains some ways to do that.

## Header injection in the user agent

Nikto does not have an option to add an arbitrary header, but it does have an option to set the user agent. This user agent will end up in each request in the `User-Agent` header. We can append any header to this user agent option by separating it with a carriage return, newline pair.

In the shell, this can be done like this:

    nl=$'\n'
    cr=$'\r'
    nikto -host http://example.com/ -useragent "nikto${cr}${nl}Some: header"

This results in a request like this:

    HEAD / HTTP/1.1
    User-Agent: nikto
    Some: header
    Host: example.com
    Connection: Keep-Alive

The user agent can be set in nikto.conf from Nikto version 2.1.5, and on the command line from Nikto 2.1.6.

## Pass requests through a proxy

Nikto can pass all its requests through a proxy. If you have a proxy that supports adding headers to outgoing requests, you can let your proxy do the work for you. Brian Cardinale describes a way to [add headers to Nikto using Burp](http://www.cardinaleconcepts.com/add-custom-header-to-nikto-scan/).

## Modify the code

Adding a header to the code is also pretty simple.


* Open [`plugins/nikto_core.plugin`](https://github.com/sullo/nikto/blob/master/program/plugins/nikto_core.plugin#L2272).
* Search for the sub `setup_hash`.
* Add a line like this:

```
    $reqhash->{'Some-key'} = "header value";
```

## Conclusion

Even though Nikto does not have an option to pass custom headers, there are several methods to add HTTP headers to each request.
