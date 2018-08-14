---
layout: post
title: "Automating path traversal with protravel"
thumbnail: path-traversal-240.jpg
date: 2018-08-15
---

With a path traversal vulnerability it is possible to download files by specifying their filename. The protravel tool helps you to download files and guess file names.

<!-- photo source: https://commons.wikimedia.org/wiki/File:Roomba_time-lapse.jpg -->

## Path traversal

Directory traversal or path traversal is a vulnerability where it is possible to download arbitrary files, using `..` to break out of a directory. When a web server or application is serving files from a specific directory using a user-supplied file name, it may be possible to break out of a directory using `..`, to go one directory up. For example, consider the following URL:

    http://example.com/getfile.php?filename=export2018.csv

The code for getfile.php may look like this:

    <?php
    readfile("/var/www/example/data/exports/".$_GET['filename']);
    ?>

It simply concatenates the filename to the directory and serves the resulting file. This is vulnerable to path traversal because we can download files from outside the exports directory. The following URL will return the /etc/passwd file:

    http://example.com/getfile.php?filename=../../../../../etc/passwd

## Guessing filenames

Path traversal typically can read any file the web server user has read access on, as long as you know the file path. It can't give directory listings, so you have to specify the exact filename to download. There are a couple of things you can try to get filenames:

1. Try standard existing files, such as /etc/passwd, /var/log/messages, /etc/mysql/my.cnf, /proc/self/environ
2. Search in the retrieved files for file paths.

So maybe /var/log/messages contains a log message about a cronjob running, with a path of the cronjob script. Then you can download that script, and search for paths within it.

## Automating with the protravel tool

I made a tool to automate this: [protravel](https://github.com/Sjord/protravel). It contains a list of interesting files that it downloads, and then searching in those files for more file paths. It tries to interpret some files, such as /etc/passwd to find home directories to search for files in.

## Conclusion

Protravel is a tool to exploit path traversal vulnerabilities. It downloads files and tries to guess file paths.

## Read more

* [Protravel source](https://github.com/Sjord/protravel)
* [Directory Traversal, File Inclusion, and The Proc File System](http://example.com/getfile.php?filename=export2018.csv)
