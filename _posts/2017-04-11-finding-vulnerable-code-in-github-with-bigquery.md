---
layout: post
title: "Finding vulnerable code in GitHub with Google BigQuery"
thumbnail: chairs-240.jpg
date: 2017-06-07
---

Some vulnerabilities are easy to spot in code. Searching code of multiple projects can reveal vulnerabilities or help to understand vulnerabilities by providing examples. This post describes how to use Google BigQuery to search for vulnerabilities in GitHub.

## Searching vulnerabilities



## GitHub search

GitHub has a search function that can search in code. 


## Google BigQuery

    SELECT
      repos.repo_name
    FROM
      [bigquery-public-data:github_repos.sample_repos] repos
    LEFT JOIN
      [bigquery-public-data:github_repos.languages] languages
    ON
      repos.repo_name = languages.repo_name
    LEFT JOIN
      [bigquery-public-data:github_repos.sample_files] files
    ON
      repos.repo_name = files.repo_name
    LEFT JOIN
      [bigquery-public-data:github_repos.sample_contents] contents
    ON
      files.id = contents.id
    WHERE
      languages.language.name = 'PHP'
      AND NOT contents.binary
      AND contents.content CONTAINS 'base_convert('
      AND repos.watch_count > 10
    GROUP BY
      repos.repo_name, repos.watch_count
    ORDER BY
      repos.watch_count DESC
    LIMIT
      100

