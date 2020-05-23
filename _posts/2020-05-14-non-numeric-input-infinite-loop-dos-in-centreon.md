---
layout: post
title: "Infinite loop leads to denial of service in Centreon"
thumbnail: rollercoaster-loop-480.jpg
date: 2020-07-01
---

<!-- photo source: https://commons.wikimedia.org/wiki/File:Canobie_Lake_Park_Untamed.jpg -->

Centreon is a IT infrastructure monitoring tool, similar to Nagios. While running Burp's active scanner on it, it suddenly stopped responding. 

## Investigating the problem

First, I logged into the server and ran `top`. That showed a single `php-fpm` process using almost all of the CPU.

<img src="/images/centreon-top.png" alt="top shows php-fpm uses much CPU" style="width: 100%">

There are several ways to get more information about the process that causes the issues:

* Configure the PHP-FPM status page and request it with `?full` to see process details.
* Run `strace` on the process to view system calls.
* Read information from `/proc` directly.

This process seems busy with something, so let's investigate what that is. The `top` screenshot shows that the process with PID 1929 is the culprit. We can obtain more information about this process in the `/proc/1929` directory. Normally, `/proc/1929/cmdline` or `/proc/1929/environ` would give some information, 
