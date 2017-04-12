---
layout: post
title: "Generating password lists with regular expressions"
thumbnail: cipherdisk-240.jpg
date: 2017-05-10
---

When cracking passwords, it is sometimes useful to try specifically formatted passwords. This post describes how you can generate a big list of passwords conforming to a specific pattern.

## Cracking passwords with lists

Password cracking tools such as [hashcat](https://hashcat.net/hashcat/), [John the Ripper](http://www.openwall.com/john/) or [Hydra](http://sectools.org/tool/hydra/) try a large number of passwords to find the correct one. As input, they typically take a text file containing a large list of possible passwords. Lists of passwords that were [leaked](https://wiki.skullsecurity.org/Passwords) are typically used. However, sometimes you have some clue as to the format of the passwords. For example, they start with an uppercase character and end with a number. In this case, you can use a tool that creates a list of words conforming to a specific pattern. Below are some tools that can do that.

## Hashcat maskprocessor

Hashcat has built-in support for [masks](https://hashcat.net/wiki/doku.php?id=mask_attack), but there is also a tool that converts a hashcat mask into a list: [maskprocessor](https://github.com/hashcat/maskprocessor). You can specify character sets, and use those character sets in a mask. You can specify up to four own your own character sets, and there are these built-in character sets:

* `?l`, lowercase letters
* `?u`, uppercase letters
* `?d`, digits
* `?s`, punctuation
* `?a`, all printable ASCII characters
* `?b`, all possible bytes

It is possible to combine static text with masks. Consider the following command:

    ./mp64.bin "password?d"

This will output the following list:

    password0
    password1
    password2
    password3
    password4
    password5
    password6
    password7
    password8
    password9

## Exrex

An alternative to hashcat's masks is to use regular expressions to define patterns. [Exrex](https://github.com/asciimoo/exrex) is a tool that outputs all possible matches to a given regex. If you are already familiar with regular expressions, this is a great way to generate word lists.

Consider this example:

    python exrex.py '(password|secret)[1-3]'

This outputs the following list:

    password1
    password2
    password3
    secret1
    secret2
    secret3

Although regular expressions are actually meant for matching strings instead of generating strings, this method works great if you are already familiar with regular expressions. It allows you to quickly generate a list of passwords if you have some clue as to what they are. 

For example, if you perform a pentest and get an account with the password `Spring2017`, you may want to use the following regex to create similar passwords:

    (Spring|Winter|Autumn|Fall|Summer|Winter)(20)?1[4567]

## Conclusion

This post showed two ways to generate custom password lists matching a specific pattern, which can help when cracking passwords.
