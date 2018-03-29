---
layout: post
title: "Finding common files in the webroot"
thumbnail: files-240.jpg
date: 2018-05-23
---

A common attack on a web application is trying to retrieve common files, such as `.gitignore` or `README.md`, using a tool such as dirbuster. The success of this approach depends a great deal on the quality of the word list. In this post we will try to compile our own word list from public data.

<!-- photo source https://commons.wikimedia.org/wiki/File:Veteran_Affairs_backlog_(2012-08-09).jpg -->

## Retrieving common files

Web applications often have a webroot, that is exposed on the web server in a way that any file in the webroot can be retrieved over HTTP. This often exposes files that shouldn't be public, especially if the project root is used as the webroot.

This makes for an easy attack in which we try to retrieve all files in a certain list from a web server. Although this can be done with any web client, there are tools that specialize in finding public files given a word list. For example, [dirb](https://tools.kali.org/web-applications/dirb) and [dirsearch](https://github.com/maurosoria/dirsearch).

However, this success of this attack depends greatly on the quality of the word list. If a filename is not in the word list, it won't be found. How can we improve our word list?

## Common filenames in the webroot

We want to find filenames that are commonly used in the webroot of a project. We'll use Google BigQuery to query GitHub repositories to find these filenames.

We'll perform a query to find webroot directories in projects, and subsequently query the most common filenames in these directories.

First, find directory names for the webroot. This would typically be `www` or `public` or something like that. How can we recognize these? By the files they contain. If a directory contains `favicon.ico` or `index.html` or `robots.txt`, it is pretty likely to be a webroot. Especially for `robots.txt`, because that only works if it is served on the root of the server. We'll query paths ending on `robots.txt`, and then use a regular expression to retrieve the directory name.

    SELECT
      directory,
      COUNT(*) AS count
    FROM (
      SELECT
        REGEXP_EXTRACT(files.path, r"([^/]*)/[^/]*$") AS directory,
        files.path
      FROM
        `bigquery-public-data.github_repos.sample_files` AS files
      WHERE
        path LIKE '%/robots.txt')
    GROUP BY
      directory
    ORDER BY
      count DESC
    LIMIT
      10

This gives the following list, which seems to match our expectations:

1. public
1. web
1. static
1. app
1. assets
1. templates
1. docs
1. dist
1. www
1. src

Next, we'll query common file names in these directories:

    SELECT
      filename,
      COUNT(*) AS count
    FROM (
      SELECT
        REGEXP_EXTRACT(files.path, r"/([^/]*)$") AS filename
      FROM
        `bigquery-public-data.github_repos.sample_files` AS files
      WHERE
        REGEXP_CONTAINS(files.path, r"/(public|web|static|app|assets|templates|docs|dist|www|src)/[^/]*$"))
    GROUP BY
      filename
    ORDER BY
      count DESC
    LIMIT
      1000;

And this gives our list of files, starting with `index.html`.

## Joining in the query

We did a poor man's join in the previous example: we copy-pasted the output from one query into the other query. This results in some files that get included in the result while they shouldn't be. For example, we found robots.txt often occurs in the directory named `src`. But then we queried the filenames for all `src` directories, not just the ones that contained `robots.txt`.

The solution to this is to query the full path of all directories containing `robots.txt`, and retrieving the filenames in those directories:

    SELECT
      filename,
      COUNT(*) AS count
    FROM (
      SELECT
        REGEXP_EXTRACT(files2.path, r"/([^/]*)$") AS filename
      FROM
        `bigquery-public-data.github_repos.sample_files` AS files1
      JOIN
        `bigquery-public-data.github_repos.sample_files` AS files2
      ON
        files1.repo_name = files2.repo_name
        AND files2.path LIKE CONCAT(REGEXP_EXTRACT(files1.path, r"^(.*)/[^/]*$"), '%')
      WHERE
        files1.path LIKE '%/robots.txt' )
    GROUP BY
      filename
    ORDER BY
      count DESC
    LIMIT
      1000;

In this query we extract directories that contain `robots.txt` in table `files1`, and then select files that are contained in these directories using `files2`.

Paradoxically, this doesn't give better results for our purposes. This query returns filenames in webroots, but the earlier set of queries may give better results for filenames that aren't supposed to be in the webroot.

## Conclusion

[View or download the word list](https://gist.github.com/Sjord/5e8f06c3734c1a1129c729c4d28a07e7).

Using public GitHub data we compiled a list of common filenames in web directories, which can be used in combination with dirsearch to find files that shouldn't be in the webroot.
