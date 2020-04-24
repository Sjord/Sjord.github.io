---
layout: post
title: "Combine two word lists for cracking passwords"
thumbnail: joined-ropes-480.jpg
date: 2020-04-29
---

To crack passwords, it is sometimes useful to combine word lists in a way that concatenates words from multiple lists. This article shows three ways to accomplish this.

<!-- photo source: https://pixabay.com/photos/rope-sea-barcelona-port-haven-1314964/ -->

## Problem description

Cracking passwords consists of performing many attempts to guess the correct password. The success of this greatly depends on the quality of the dictionary to use as guesses. Sometimes it is helpful to combine two word lists, in such a way that passwords consist of concatenations of words from one list with words from a second list.

For example, one word list may contain seasons:

* spring
* summer
* autumn
* fall
* winter

The second word list may contain year numbers:

* 2018
* 2019
* 2020

By combining the two, we can get common passwords:

* spring2018
* summer2018
* ...
* fall2020
* winter2020

## Hashcat's combinator

Hashcat has a [combinator](https://hashcat.net/wiki/doku.php?id=hashcat_utils#combinator) [utility](https://github.com/hashcat/hashcat-utils), which does what we want:

>  Each word from file2 is appended to each word from file1 and then printed to STDOUT. 

Instead of reading both files completely into memory, it reads one of the files multiple times. The advantage is that this works with very large files that wouldn't fit in memory. However, it is questionable whether this is a realistic scenario; combining two files of 1 GB would result in a file of approximately 200 petabytes, so you would run out of disk space before you run out of memory.

Combinator.bin works well. It does what it is supposed to do and works on multiple platforms.

## join shell utility

The shell command `join` can also combine word lists. However, it is meant for joining files on a certain key. If you have two files with tab-separated tabular data, it can perform something similar to a SQL join. However, we don't have a key, and several tricks are needed to perform the simpler combination we are after.

First, we want to join all lines, no matter what the key value is. To do this, we tell `join` to use a non-existing key; since the value of a non-existing key is always empty, all lines match. The option `-j 2` uses the second column, which presumably doesn't exist.

However, some lines may have a second column, if the line contains whitespace. To make sure there is no second column, we tell join to split lines not on whitespace, but on some character that is unlikely to occur in our word lists, such as the null byte. The option `-t $'\0'` tells join to split lines into fields using the null byte.

When joining, `join` doesn't concatenate the words, but puts them in separate fields. So between each two combined words is the field separator, which in our case is the null byte. To solve this, we simply use `tr` to remove all null bytes. The complete command becomes:

    join -j 2 -t $'\0' file1.txt file2.txt | tr -d '\000'

This works. It is more complicated than needs to be, but the advantage is that these tools are already present on most systems.

## Using Python's itertools.product

Of course this simple task can also be performed by a little script in Python, or any other language suitable for quick small scripts. Python already has functionality to combine lists in a way we want: [itertools.product](https://docs.python.org/3/library/itertools.html#itertools.product). We read the files into lists, call itertools.product, and convert to resulting list back into strings:

    import sys
    import itertools

    lists = []
    for filename in sys.argv[1:]:
        words = []
        with open(filename) as fp:
            for line in fp:
                words.append(line.rstrip())
        lists.append(words)

    for element in itertools.product(*lists):
        print("".join(element))


## Conclusion

This article listed three tools to combine word lists like a Cartesian product. The hashcat utility is meant for the job and performs well. The shell command, but is more complicated than I would expect from such a simple task. Finally, using a Python script any task is solvable.

## Read more

* [GitHub repo with Python script and shell script shown above](https://github.com/Sjord/cartesianwords)
* [Hashcat utils](https://hashcat.net/wiki/doku.php?id=hashcat_utils)
