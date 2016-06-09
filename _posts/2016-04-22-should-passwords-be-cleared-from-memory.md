---
layout: post
title: "Clearing secrets from memory"
thumbnail: memory-240.jpg
date: 2016-05-22
---

When handling a secret such as an encryption key or a password, it is important not to leak it in any way. If the secret ends up in a log file, core dump or error message this could result in showing the secret to people who are not meant to see it. It may even be a good idea to remove the secret from memory after it is has been used. This blog post will look into whether that is good practice and how you can clear things from memory.

## Java's recommendation

In the Java world it is pretty common to clear memory with secret contents after use. Because the Java String type is immutable and can't be cleared, a `char[]` should be used for secrets. The [JPasswordField](https://docs.oracle.com/javase/tutorial/uiswing/components/passwordfield.html), for example, returns a `char[]` instead of a `String` for the `getPassword` method. The [example code](https://docs.oracle.com/javase/tutorial/uiswing/components/passwordfield.html) also shows how the `char[]` can be cleared, "for security".

The [Java Cryptography Architecture Guide](http://docs.oracle.com/javase/8/docs/technotes/guides/security/crypto/CryptoSpec.html) formalizes this practice in a guideline:

> It would seem logical to collect and store the password in an object of type java.lang.String. However, here's the caveat: Objects of type String are immutable, i.e., there are no methods defined that allow you to change (overwrite) or zero out the contents of a String after usage. This feature makes String objects unsuitable for storing security sensitive information such as user passwords. You should always collect and store security sensitive information in a char array instead.

This implies that if you have a secret, you should clear it from memory when you are done with it, so it does not stay in memory for an attacker to read.

## Possible ways to read memory

There are some ways an attacker can read memory:

* [Heartbleed](http://heartbleed.com/), the buffer over-read bug in OpenSSL, allows for reading memory remotely.
* [Cold boot attack](https://en.wikipedia.org/wiki/Cold_boot_attack), where the attacker reboots a computer with his own software to read the memory.
* [DMA attack](https://en.wikipedia.org/wiki/DMA_attack), where an attacker reads memory from the Firewire or Thunderbolt port, for example.
* An attacker with read access within the program can use /proc/self/mem to read memory. This is not as simple as it sounds, because you need to read at a specific position in this file.

There are also ways that memory is persisted on the hard drive, making it possible for a dumpster-diving attacker to gain access to secrets:

* When hibernating the memory contents are written to disk to restore later.
* When a system is low on memory some memory pages are written to disk (paging or swapping).
* The program crashes and a core dump is written to disk.

Up until Heartbleed, memory reading attacks were something for high value targets and specialized attackers. Heartbleed showed that there can be widespread memory reading bugs that can be used to access secrets remotely, in particular the private key of SSL certificates.

## Shortening the time a secret is in memory

You could make the point that once an attacker has access to your memory it's game over. The program that uses the password needs to have it in memory for at least a short time, so it is readable anyway. Does the time you keep it in memory really matter?

Clearing secrets from memory reduces the time they are readable from memory. It makes the difference between having one password or a hundred passwords in memory. It may slow down an attacker because he has to try several times before he gets the timing just right and can read the secret.

If an attacker can read your memory you are definitely screwed, but clearing secrets may limit the damage a bit.

## It is not easy to clear memory

Now that we have identified attacks and argued that it limits attack impact, it may seem like it is always a good idea to clear secrets from memory. That is not the case. Clearing memory is very difficult in some environments, and is often more of a hassle than it is worth.

Consider C for example. It is a fairly low-level language that allows direct control over memory in many ways. Even so, an optimizing compiler can change your program in any way that keeps the behavior the same. Since the memory contents are not part of the behavior of your program, an optimizing compiler can remove any statements that just set memory. Your `memset` call will be [optimized away](http://www.daemonology.net/blog/2014-09-04-how-to-zero-a-buffer.html).

In many other environments, the way memory is handled depends on the implementation of the runtime environment. This is even the case for Java, where the guideline advices to use a `char[]`. The guideline assumes that a `char[]` is directly mapped to memory, that there is only one instance in memory and that clearing the array also clears the memory. This is all not strictly specified in the Java specification, so another JVM may implement it in a entirely different way and leave the contents in memory.

For normal web applications, don't bother with clearing memory. If you are building a nuclear missile launch silo, make sure to clear memory contents. This also means choosing a language or implementations that explicitly allows that. If you do implement clearing memory contents, make sure you thoroughly test it by actually searching the memory for your secret.

## Conclusion

Clearing memory mitigates the impact of a memory-reading attack. It is pretty hard to implement correctly and should only be used when the secrets are particularly secret.

There is another post that goes into [clearing memory in Python](/2016/06/09/clearing-memory-in-python/).
