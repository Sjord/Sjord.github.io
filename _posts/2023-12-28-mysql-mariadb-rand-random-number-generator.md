---
layout: post
title: "MySQL's random number generator"
thumbnail: five-dice-480.jpg
date: 2024-01-03
---

MySQL and MariaDB both have a RAND function that returns a "random" number. From the [MariaDB docs](https://mariadb.com/kb/en/rand/):

> Returns a random DOUBLE precision floating point value v in the range 0 <= v < 1.0.

It works as follows:

```
seed1 = (seed1 * 3 + seed2) % 0x3FFFFFFF
seed2 = (seed1 + seed2 + 33) % 0x3FFFFFFF
return seed1 / 0x3FFFFFFF
```

You can find the source for [MySQL](https://github.com/mysql/mysql-server/blob/trunk/mysys/my_rnd.cc#L59-L63) and [MariaDB](https://github.com/MariaDB/server/blob/11.4/mysys/my_rnd.c#L58-L65).

A global PRNG instance is first [seeded](https://github.com/MariaDB/server/blob/3fad2b115569864d8c1b7ea90ce92aa895cfef08/sql/mysqld.cc#L4890) with the current time in seconds:

```
my_rnd_init(&sql_rand,(ulong) server_start_time,(ulong) server_start_time/2);
```

Then a thread-specific PRNG is [seeded](https://github.com/MariaDB/server/blob/3fad2b115569864d8c1b7ea90ce92aa895cfef08/sql/sql_class.cc#L890) from the global PRNG, plus something:

```
tmp= (ulong) (my_rnd(&sql_rand) * 0xffffffff);
my_rnd_init(&rand, tmp + (ulong)((size_t) &rand), tmp + (ulong) ::global_query_id);
```

`::global_query_id` is a query counter, which is always 1 when the server starts.

`(ulong)((size_t) &rand` is the current address of the thread-specific PRNG structure in memory. It is different for each thread. Since structures are aligned in memory, this is always a multiple of 8. Using memory addresses like this could compromise address space layout randomization (ASLR).

The PRNG is seeded when a new thread is created, but not when a new connection is established.

## 30 bits

The operations are performed modulo 0x3FFFFFFF, or 2<sup>30</sup> - 1. So the random numbers contain at most 30 bits of entropy.

## Modulo 33

There are three operations performed in the random number generator:

- addition of 33,
- multiplication by 3,
- modulo 0x3FFFFFFF.

0x3FFFFFFF is cleanly divisable by 33. If the random number generator is seeded by values that are divisable by 33, they remain that way. None of the above operations are going to change a number that is divisible by 33 to one that is not. It doesn't even hit all multiples of 33, only 1k&times;33 and 3k&times;33, for any k.

If the seeds are not divisible by 33, the random number generator gets stuck in another group. These groups range in size from 2 modulo 99 to 23 modulo 99, losing between 5.6 and 2.1 bits of randomness.

This affects only the least significant bits, so when doing `RAND() * 10` this won't be noticable.

## Low seeds

If both seeds are low, the values don't wrap around.

```
MariaDB> set session rand_seed1 = 123;
MariaDB> set session rand_seed2 = 456;
MariaDB> select * from numbers order by rand();
+------+
| num  |
+------+
|    1 |
|    2 |
|    3 |
|    4 |
|    5 |
|    6 |
|    7 |
|    8 |
|    9 |
|   10 |
+------+
10 rows in set (0.001 sec)
```

There are more seeds that exhibit such stable behavior, such as 0x3FFFFFFF / 3.

## Repetitions

If we create two lists of *n* random numbers, what are the chances that the top entry of both list is the same? For a 30 bit PRNG, we would expect this chance to be 2<sup>-30</sup>, regardless of *n*. For MySQL's PRNG, this chance is about 2<sup>-17</sup> when *n* is 960. The chance of reptition after 960 calls to the PRNG is about 6000 times higher than expected.

## Distribution

A simple way to pick a random row from a relatively small table is as follows:

```
SELECT * FROM table ORDER BY RAND() LIMIT 1
```

This returns the row for which RAND() returns the lowest value. For some table sizes, this performs quite bad. If the table is 2400 rows, some items are 40 times as likely to be picked as some other items.

I simulated the above query 100000 times and plotted the results in a graph. The orange line shows the distribution for an actual random sample. The blue line shows how many times items are selected with simulated `ORDER BY RAND()` queries in a table of 2400 rows. At the left of the graph, we see some items get selected three times. At the right of the graph, we see other items get selected 111 times. 

<img src="/images/rand-histogram.png" width="100%">


## Conclusion

MySQL's random number generator:

- is seeded with low entropy, related values.
- returns at most 30 bits of randomness by design.
- gets stuck in groups modulo 99.
- returns predictable sequences for unlucky seeds.
- repeats itself with higher probability after specific number of calls.
- has a skewed distribution when selecting random items from tables.

