---
layout: post
title: "Infinite loop leads to denial of service in Centreon"
thumbnail: rollercoaster-loop-480.jpg
date: 2020-07-01
---

Centreon is a IT infrastructure monitoring tool, similar to Nagios. An infinite loop can be caused by changing a parameter that is used for the loop counter to a punctuation character, which is a denial-of-service vulnerability.

<!-- photo source: https://commons.wikimedia.org/wiki/File:Canobie_Lake_Park_Untamed.jpg -->

## Centreon hangs

While testing Centreon for security vulnerabilities, it suddenly stopped responding. If worked again after rebooting the virtual machine it was running on, but when I ran the active scanner on a particular page I could reliably cause Centreon to stop working. This may hint to a denial-of-service vulnerability, but some more investigation was required to narrow down what the problem is exactly.

## Finding the hanging script

First, I logged into the server and ran `top`. That showed a single `php-fpm` process using almost all of the CPU.

<img src="/images/centreon-top.png" alt="top shows php-fpm uses much CPU" style="width: 100%">

So this `php-fpm` process is almost certainly stuck in a loop, and blocking other requests. This process seems busy with something, so let's investigate what that is. There are several ways to get more information about the process that causes the issues:

* Configure the PHP-FPM status page and request it with `?full` to see process details.
* Run `strace` on the process to view system calls.
* Read information from `/proc`.

I started with investigating `/proc`. The `top` screenshot shows that the process with PID 1929 is the culprit. We can obtain more information about this process in the `/proc/1929` directory. First, it is a good idea to stop the process. This makes it possible to investigate the process without it using up all CPU, and it prevents the `/proc/1929` directory from disappearing when the process finishes.

    $ kill -SIGSTOP 1929

Normally, `/proc/1929/cmdline` or `/proc/1929/environ` would give some information about the process that is executed. However, in this case it just points to a PHP-FPM pool. A directory listing of the `/proc/1929` directory gives a hint on where the PHP file is located:

    $ ls -l /proc/1929
    ...
    lrwxrwxrwx 1 root   root   0 Jun  4 11:38 cwd -> /usr/share/centreon/www/include/eventLogs/xml
    lrwxrwxrwx 1 root   root   0 Jun  4 11:38 exe -> /opt/rh/rh-php72/root/usr/sbin/php-fpm

The current working directory is /usr/share/centreon/www/include/eventLogs/xml, so that is probably where the problematic script is. This directory contains just a single PHP file, `data.php`, so that is the script that triggers the loop.

Another interesting lead was found by listing the open file descriptors:

    $ ls -l /proc/1929/fd
    ...
    lrwx------ 1 root root 64 Jun  4 11:43 7 -> socket:[27526]
    l-wx------ 1 root root 64  Jun  4 11:43 8 -> /var/opt/rh/rh-php72/log/php-fpm/centreon-error.log
    lrwx------ 1 root root 64 Jun  4 11:43 9 -> socket:[22415]

The script has a file descriptor to a log file, `centreon-error.log`. Looking at the last lines of that log file gives the exact location of the problem:

    PHP Warning:  A non-numeric value encountered in 
    /usr/share/centreon/www/include/eventLogs/xml/data.php on line 657

## Finding the triggering request

So, the log file points to the following loop, in which `$iStart` and `$iEnd` are both set to the value of the `num` GET parameter.

```php
for ($i = $iStart; $i <= $iEnd; $i++) {
    $pageArr[$i] = array("url_page" => "&num=" . $i . "&limit=" . $limit, "label_page" => ($i + 1), "num" => $i);
}
```

The log file contains millions of warnings on this line, indicating that this loop continues indefinitely. But how can that be, if `$iStart` and `$iEnd` are equal?

I investigated by adding debug statements to the PHP file. I changed the code to exit as soon as the loop ran more than 10 times:

```php
$loopcounter = 0;
for ($i = $iStart; $i <= $iEnd; $i++) {
    $pageArr[$i] = array("url_page" => "&num=" . $i . "&limit=" . $limit, "label_page" => ($i + 1), "num" => $i);
    if ($loopcounter++ >= 10) die("infinite loop detected");
}
```

After editing the code, I ran Burp's active scanner again and checked Burp's log for `infinite loop detected`. And sure enough, several requests in the log show that response. One of those requests looks like this:

    GET /centreon/include/eventLogs/xml/data.php?output=&oh=true&warning=true&unknown=true&critical=true&ok=true&unreachable=true&down=true&up=true
    &num=0)waitfor%20delay'0%3a0%3a20'--&error=1&alert=true&notification=true&search_H=&search_S=&period=&StartDate=05/01/2020&EndDate=06/04/2020&StartTime=00:00&EndTime=24:00&limit=30&id=&export=1 HTTP/1.1

Here, Burp has injected in the `num` parameter. I send this request to the repeater and remove characters from the num payload, until the minimal request to trigger the infinite loop. A value of `)` for `num` is sufficient to trigger the infinite loop.

## Looking back at the loop

So, why is this an infinite loop? A little test script reproduces the issue:

```php
$iStart = $iEnd = ")";
$loopcounter = 0;
for ($i = $iStart; $i <= $iEnd; $i++) {
    if ($loopcounter++ >= 10) die("infinite loop detected");
}
```

If `$i` is a punctuation character, `$i++` does not change it. So `$i` stays `)`, and the loop keeps on going.

## Conclusion

Injecting a punctuation character into a loop variable causes an infinite loop, effectively disabling the web server. In the investigation I used the `/proc` directory and edited the PHP file. These are powerful debugging methods, but obviously only available when having access to the web server. The bug is typically one that is only possible in PHP, that accepts punctation characters as loop counter, and silently and unexpectantly fails to increment the loop variable.

I solved this by converting the parameters to integers: [enh(secu): sanitize and cast parameters to int #8702](https://github.com/centreon/centreon/pull/8702)