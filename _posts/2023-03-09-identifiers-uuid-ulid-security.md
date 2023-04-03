---
layout: post
title: "Security of identifiers"
thumbnail: tree-tag-480.jpg
date: 2023-04-26
---

*This UUID has one weird trick to remain globally unique - database indices hate him!*

## Introduction

If you've been on the internet before, you've probably seen identifiers in URLs. These generally take two forms. Either sequential numeric identifiers:

```
https://example.org/user/123/profile
```

Or random UUIDs:

```
https://example.org/user/f054b533-0828-4c4b-9a69-3f5e48101b96/profile
```

The identifier identifies which object to retrieve. In the examples above, it identifies a user, for which the profile is shown. Typically, the identifier in the URL equals the primary key of the record in the database, but this does not have to be the case.

What form of identifiers an application use can have consequences for its security.

## Risks

There are two risks when using identifiers:

* guessable - an identifier of a resource can be guessed without too much effort. In some cases, it may be possible to retrieve a resource knowing just the identifier.
* information disclosure - the identifier can expose how many items are in the database, the state of the random number generator, the network address of the machine it was generated on, or the time it was generated.

### Guessability

Sequantial numeric identifiers are easy to guess. If the user with id `123` exists, it makes sense users with id `122` and `124` may also exist. This can make it easy to exploit a vulnerability. By changing the identifier in the URL, you may gain access to resources that you weren't supposed to. This vulnerability is called *insecure direct object reference* (IDOR). The identifier in the URL references an object in the database, and by changing that identifier in the URL you can request other objects.

The underlying problem with IDOR is that no access control is performed. If you are not authorized to view user `122`, the application is supposed to give an error when you request it. Whether the identifiers are easy to guess or not has no influence on the base of problem, which is missing authorization checks.

However, in practice IDOR is much easier to exploit when the identifiers can be guessed. With sequential numeric identifiers, it is easy not only to retrieve one user, but all users in the database. When random UUIDs are used, this is practically impossible. Using an identifier that is hard to guess is thus a good defence in depth measure against insecure direct object references.

### Information disclosure

Unless an identifier is totally random, it exposes information. UUIDv1 contains the computer's MAC address as a source of uniqueness. Some identifiers contain the time a resource was created. Sequential numeric identifiers may expose how many records are already present, and at what rate they are created.

#### German tank problem

In the Second World War, the allies made a statistical estimate of the number of tanks the Germans produced. Some sources claim that tanks were simply numbered sequentially, but this was not the case. Tank parts came from different manufacturers, and these manufacturers had different numbering schemes. However, many parts of the tanks were numbered. The allies had captured two Panther tanks. Each tank had 48 bogie wheels, supporting the tank treads. These bogie wheels were numbered with the mold number from the factory. This way, the number of available molds could be estimated, which in turn could be used to estimate the rate of tank production.

## Other considerations

Besides security, there are two important differences between sequential and random idenfiers:

* Asynchronous generation - sequential identifiers need to be created atomically, whereas random UUIDs can be created anywhere and assumed to be unique.
* Database indicates - there is a rumor that random identifiers cause bad database performance.

### Asynchronous generation

When two users create a new object simultaneously, the objects should still get a unique identifier. The handling and increment of a sequential numeric identifiers should therefore be done atomically. Typically, it is handled by the database when a record is created. This is a bit of a strange pattern, where data is created and returned to the application when a record is inserted. Also, it is not possible to create a identifier ahead of time, for example to facilitate a multi-step workflow to create the record.

When using a random identifier, it can be created by the application without communicating with the database. Multiple clients can create identifiers simultaneously, before inserting the record.

### Database indices

A database index is a table with keys in the first column, and locations of database records in the second column. The table is ordered by the keys in the first column. With such an index, lookup up a key can be quite fast. The database does a binary search on the first column, looks in the second column to determine the location of the corresponding row, and retrieves the row from this location.

As I said, the index is sorted on the key. When a new record is inserted, it's key is added to the index in such a way that the keys in the index remain sorted. When using sequential identifiers, this is pretty easy. The key can just be appended to the end. When using random identifiers, the key has to be inserted in the middle somewhere, for every inserted row. This can lead to index fragmentation.

This is especially a problem when using a clustered index. In a clustered index, there is not a separate table. Instead, the rows of the actual database table are sorted on the key. When inserting a random key, the rows in the table have to be rearranged to be ordered on this key again.

Even though index fragmentation is real, it is not quite as simple to say that UUID keys always cause performance problems. The database is built on top of many layers of virtualization. Even if the database puts the rows in a particular order, the file system or storage medium can put them somewhere else. Whether it is slower to access things that are far apart depends on whether the disk is a SSD or HDD, whether the data is cached in memory, and on many more layers of abstraction. Performance may differ between different database servers. I wouldn't write off UUIDs without doing performance tests first.

## Request ID doesn't have to equal database ID

So far, we have assumed that the identifier in the URL equals the identifier in the database. Requesting `/user/123/profile` retrieves the record with primary key `123` from the database. However, this doesn't have to be the case. There are two ways around this:

* Map database identifiers to temporary identifiers. Keep a mapping in the user's current session, and perform a translation when creating or serving a URL.
* Encrypt and authenticate database identifiers. The identifiers are opaque to the user, but can be decrypted to actual database identifiers.

This way, object references become meaningless to a user or attacker. Insecure direct object references are no longer exploitable. Only the references that the application generates can be used, and the user can no longer simply modify the URL.

## Conclusion




## Read more

* [Sharding & IDs at Instagram.](https://instagram-engineering.com/sharding-ids-at-instagram-1cf5a71e5a5c)
* [PostgreSQL: Re: uuid-ossp: Performance considerations for different UUID approaches?](https://www.postgresql.org/message-id/20151222124018.bee10b60b3d9b58d7b3a1839%40potentialtech.com)
* [GUIDs as PRIMARY KEYs and/or the clustering key - Kimberly L. Tripp](https://www.sqlskills.com/blogs/kimberly/guids-as-primary-keys-andor-the-clustering-key/)
* [How to Do UUID as Primary Keys the Right Way - DZone](https://dzone.com/articles/uuid-as-primary-keys-how-to-do-it-right)
* [An Empirical Approach to Economic Intelligence in World War II](https://cms.dm.uba.ar/academico/materias/2docuat2019/probabilidades_y_estadistica_C/GTP.pdf)
