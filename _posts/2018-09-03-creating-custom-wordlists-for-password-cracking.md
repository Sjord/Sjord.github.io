---
layout: post
title: "Creating custom word lists for password cracking"
thumbnail: bombe-240.jpg
date: 2018-09-26
---

When cracking passwords, the success greatly depends on the quality of the word list you use. This article lists some methods to create custom word lists for cracking passwords.

<!-- photo source: https://commons.wikimedia.org/wiki/File:Bombe_Machine,_Bletchley_Park.jpg -->

## From a regular expression

[Exrex](https://github.com/asciimoo/exrex) expands a regular expression into all possible possibilities. This can be helpful if you want to combine certain words, or use characters or numbers from a specific range. This is mainly helpful if you already know regular expressions. Otherwise, you may be better of using hashcat's built-in [masks](https://hashcat.net/wiki/doku.php?id=mask_attack).

<img src="/images/exrex.png">

## From a website

[CeWL](https://digi.ninja/projects/cewl.php) is a tool that spiders a web site and collects words from it. It is a pretty mature tool written in Ruby. It returns a large list of words. This is particularly useful to gather company-specific jargon, like product names. It is easy to run and gives quick results.

<img src="/images/cewl.png">

## From Twitter

[Twofi](https://digi.ninja/projects/twofi.php) is CeWL for Twitter. It searches the timeline of specific users or keywords and collects words. It returns a lot of garbage, such as "words" from shortened URLs, and you need a API key to use it. It is a little bit of a hassle and it doesn't return as good results. I would only recommend this if you know the Twitter handle of the owners of your accounts. 

<img src="/images/twofi.png">

## From personal data

[Cupp](https://github.com/Mebus/cupp) asks you some questions about the person you are targeting, and composes a word list depending on the answers. For example by combining the first name with the birth date. It can be useful if you know all this information, but I would probably just add all this information to the dictionary I am using and use combination attacks in hashcat.

<img src="/images/cupp.png">

## From keyboard walks

Keyboard walks are made up of adjecent keys on the keyboard, like 12345678, or 1qazxsw2. The hashcat tool [kwprocessor](https://github.com/hashcat/kwprocessor) can creates such walks. You can configure start characters, routes to take and keyboard layouts, and it comes with sane examples so that you can start right away.

<img src="/images/kwprocessor.png">

## From Markov chains

The hashcat tool [statsprocessor](https://github.com/hashcat/statsprocessor) can create a word list from a Markov chain. If you have some text you think is indicative of how passwords are chosen, you can analyze that to determine which letter pairs occur often. Then, the statistics on these letter pairs can be used to create a word list. The word list will then be ordered in such a way that the most likely passwords are at the top. This is pretty advanced, and I am not sure when you would want to use this.

To create such a word list, first, generate statistics with [hcstatgen](https://github.com/hashcat/hashcat-utils/blob/master/src/hcstatgen.c). Then, create a word list using [statsprocessor](https://github.com/hashcat/statsprocessor). 

<img src="/images/statsprocessor.png">

## Conclusion

There are several tools available to create customized word lists. Particularly using the name of the site, company, or product has been successful for me in the past. Furthermore, you can use hashcat's mask and combination attacks. And if you really want to crack those last passwords, you can use the tools listed above to obtain even more valuable words.
