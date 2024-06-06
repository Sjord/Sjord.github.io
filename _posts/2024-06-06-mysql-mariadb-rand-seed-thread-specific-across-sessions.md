---
layout: post
title: "Seed of random number generator carried across connections in MariaDB"
thumbnail: thread-480.jpg
date: 2024-06-12
---

I found a vulnerability in MariaDB where it is possible to influence the state of the random number generator for another user's session. This random number generator is also used for password authentication, making it possible to perform replay attacks by fixing the "random" data.

<!-- Photo source: https://pixabay.com/photos/thread-fence-wool-orange-thread-8253277/ -->

## Vulnerability

MariaDB has a random number generator that keeps two state variables, `rand_seed1` and `rand_seed2`. These can be changed by the user using the following SQL query:

```
MariaDB> SET SESSION rand_seed1 = 123, rand_seed2 = 456;
```

Even though this says `SESSION` to indicate these are session variables, these variables were actually specific to the thread and not to the session. This means that one user can set the state of the random number generator on a thread, and then the connection that subsequently uses that thread has this state of the random number generator. So it is possible to set the state of the random number generator for other connections, even when these connections are made by other users.

## Impact

This is especially interesting in shared hosting environments, where there are multiple users on the same database. If an application uses `RAND()` to determine some secret or shuffle some records, another user can influence that by setting the seeds on all threads.

Furthermore, this same random number generator is used in the authentication mechanism. When using password authentication, the password is scrambled using random data. If an attacker fixes this random data by setting the seeds, they can record and replay an actual authentication exchange to gain access to the MariaDB server.

## Timeline

I found and reported this issue in December 2023. It was fixed quickly by reinitializing the random number generator on each connection. Versions of MariaDB released in February 2024 contains this fix.

## Read more

- [MySQL's random number generator](/2024/01/17/mysql-mariadb-rand-random-number-generator/)
- MariaDB Jira: [MDEV-33148 A connection can control RAND() in following connection - Jira](https://jira.mariadb.org/browse/MDEV-33148)
- MariaDB GitHub: [MDEV-33148 A connection can control RAND() in following connection](https://github.com/MariaDB/server/commit/c6e1ffd1a07fc451e7211b0d00edbace78137276)
- MySQL GitHub: [Bug #18329560 RESETCONNECTION DOESN'T CLEAR RAND SEED](https://github.com/mysql/mysql-server/commit/917fc1b47c14e560e598bfed787e699b3096d4bf)
- [MySQL 5.7.5 release notes](https://dev.mysql.com/doc/relnotes/mysql/5.7/en/news-5-7-5.html#:~:text=Bug%20%2318329560)