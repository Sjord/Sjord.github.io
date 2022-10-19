---
layout: post
title: "Finding vulnerable code on GitHub with Google BigQuery"
thumbnail: chairs-240.jpg
date: 2017-06-07
---

Some vulnerabilities are easy to spot in code. Searching code of multiple projects can quickly reveal vulnerabilities. This post describes how to use Google BigQuery to search for vulnerabilities in GitHub.

For a while I wanted the ability to search open source web applications, in some more advanced way than offered by GitHub. 
When I write an article on my blog about a particular vulnerability, it is often nice to have an example of this vulnerability, preferably in a mature and widespread web application. Also, when picking a project to pentest you want something that is relatively well known and mature, but not so mature that it is already fully hardened. So it would be nice if we could search projects on code strings, order by popularity, and only show web applications.

## GitHub search

GitHub is the de-facto standard for hosting open source projects, so this is a good place to start searching for our projects. However. GitHub search is inadequate for our purpose. Because it searches on keywords it is hard to construct a query that matches a specific piece of code syntax.  Furthermore, GitHub has a big number of discontinued, personal or example projects. We are interested in vulnerabilities in commonly used, well-known web application projects.

For example, in [an earlier blog post](/2017/03/15/dont-use-base-convert-on-random-tokens/) I described that using the PHP function `base_convert` on secret tokens reduces their security. Let's search for `base_convert` in PHP projects to find those vulnerabilities:

![GitHub search results for base_convert](/images/github-search-results.png "GitHub search results for base_convert")

These matches are obviously test scripts to demonstrate the usage of `base_convert`, not vulnerabilities in web applications. GitHub search gives too many useless results to find vulnerabilities like this.

## GitHub API

We can also use the GitHub API to do our own search. For example, we can loop through the search results and only select the repositories with more than 20 stars. I made [a script](https://github.com/Sjord/githubsearch) that does this. However, because it processes each search result it is pretty slow and often triggers GitHub's rate limits. This option does not work very well, but can be fully customized.

## Google BigQuery

[Google BigQuery](https://cloud.google.com/bigquery/) lets you run SQL queries on big data sets. It has several public data sets, one of which is [GitHub data](https://cloud.google.com/bigquery/public-data/github). This means that we can perform SQL queries on GitHub data. For example, if we want to search for `base_convert` in PHP projects, and get the top 10 results on watch count, we can use the following query:

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
    GROUP BY
      repos.repo_name, repos.watch_count
    ORDER BY
      repos.watch_count DESC
    LIMIT
      10

This query uses the `sample_repos` table, which contains only a relatively small part of the total number of GitHub repositories. It contains only the repositories with two or more stars. This is fine for our purpose, because we are only interested in popular projects.

The query takes approximately 30 seconds to run. It produces a list of repositories with the most popular at the top.

At least one of the listed projects has incorrect use of `base_convert`: [WellCommerce](https://github.com/WellCommerce/WellCommerce) uses it in its password reset token:

    public function resetPassword(Client $client)
    {
        $hash = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36) . $client->getId();
        $client->getClientDetails()->setResetPasswordHash($hash);
        $client->getClientDetails()->setHashedPassword($this->getSecurityHelper()->generateRandomPassword());
        $this->updateResource($client);
    }

This method really works well to find vulnerable projects, and offers much flexibility. For example, we can write a query that only searches projects containing a `urls.py` file, or even look in the contents of the `requirements.txt` or `package.json` to isolate projects using a certain framework.

## Conclusion

Google BigQuery offers a great and flexible way to search open source projects on GitHub. It is much more powerful and versatile than the built-in GitHub search functionality.

## Read more

* [Operation Rosehub](https://opensource.googleblog.com/2017/03/operation-rosehub.html)
