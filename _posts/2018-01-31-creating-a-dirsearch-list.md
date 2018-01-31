
Extract directories with robots.txt:

    SELECT
    REGEXP_EXTRACT(files.path, r"([^/]*)/[^/]*$") AS directory,
    files.path
    FROM
    [bigquery-public-data:github_repos.sample_repos] AS repos
    LEFT JOIN
    [bigquery-public-data:github_repos.sample_files] AS files
    ON
    repos.repo_name=files.repo_name
    WHERE
    path LIKE '%/robots.txt'
    LIMIT
    10

Most popular directories:

    SELECT
    directory,
    COUNT(*) AS count
    FROM (
    SELECT
        REGEXP_EXTRACT(files.path, r"([^/]*)/[^/]*$") AS directory,
        files.path
    FROM
        [bigquery-public-data:github_repos.sample_repos] AS repos
    LEFT JOIN
        [bigquery-public-data:github_repos.sample_files] AS files
    ON
        repos.repo_name=files.repo_name
    WHERE
        path LIKE '%/robots.txt')
    GROUP BY
    directory
    ORDER BY
    count DESC
    LIMIT
    10

    directory,count
    public,3040
    web,417
    static,401
    app,313
    assets,165
    templates,95
    docs,73
    www,60
    dist,60
    src,55
    source,52

Most popular files in public (no Legacy SQL anymore, so other syntax) (no join on repos anymore):

    SELECT
        filename,
        COUNT(*) AS count
    FROM (
        SELECT
            REGEXP_EXTRACT(files.path, r"/([^/]*)$") AS filename
        FROM
            `bigquery-public-data.github_repos.sample_files` AS files
        WHERE
            REGEXP_CONTAINS(files.path, r"/(public|web|static|app)/[^/]*$"))
    GROUP BY
        filename
    ORDER BY
        count DESC
    LIMIT
        10000;
