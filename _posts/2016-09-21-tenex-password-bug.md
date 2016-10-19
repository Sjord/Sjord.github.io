---
layout: post
title: "Tenex password guessing bug"
thumbnail: pdp10-240.jpg
date: 2016-10-24
---

In the 1970 operating system Tenex it was possible to guess user passwords one character at a time, by looking at which memory pages were accessed during the checking of the password.

## Tenex introduction

Bolt, Beranek and Newman (BBN) was a company initially specialized in acoustics. Because the acoustic models required a lot of computation, BBN got interested in computing. The company was one of the first to use a PDP-1. Because they wanted to run memory-intensive LISP programs they wanted a system with paging (TODO explain paging). First they implemented this in software, but later they added a paging hardware module to the PDP-10 and wrote their own operating system to support it: Tenex.

## Bug summary

Tenex had several several system calls in the operating system, which could be called using the JSYS instruction. One of these system calls was a procedure to check the password of another user. This procedure would check the password one character at a time. By putting the password to be checked on a page boundary, and checking whether the second page is accessed, it was possible to guess passwords one character at a time.

The bug was discovered in 1974 by a young computer scientist, Alan Bell. Alan joined BBN just a year before and was interested in the operating system, which was the most complicated piece of software that he encountered so far. He read the source code of Tenex in his own time to figure out how it worked, and this is how he discovered the flaw.

## Paging introduction

Paging makes it possible to address more memory than the machine actually has. This "virtual memory" is simulated by storing less used memory on disk and keeping only the most often used parts, or pages, in memory. If a memory page is referenced that is not currently in memory, execution is paused, the page is loaded from disk and put in memory, and execution continues. At BBN they implemented paging for the PDP-1 in software. Although it worked well, it was pretty slow. According to Dan Murphy (TODO intro):

> However, the actual number of references
was sufficiently high that a great deal of time
was spent in the software address translation
sequence, and we realized that, ultimately,
this translation must be done in hardware if a
truly effective paged virtual memory system
were to be built.

BBN asked DEC to design a PDP-6 with paging, but eventually DEC stopped producing PDP-6 models without there ever having been paging support. After a couple of years, BBN decided to build their own virtual memory support using the PDP-10. Because the PDP-10 had insufficient support for this, a hardware module was added between the processor and the memory, that handled the paging. Software support was also required, and BBN decided to develop Tenex.

TODO Much user control
>  The virtual memory system allowed the user a lot of control over what pages were placed in the address space. While usually a paging file was in the address space, it was also possible to map data files, etc. Therefore, the user was given control over what was mapped where and the type of access allowed. There was another JSYS that controlled this.
TODO plaatje

> TODO insert another parts of IEEE annals?

> Then too, paging and "virtual memory"
were still rather new concepts at that time.
Significant commercial systems had yet to
adopt these techniques, and the idea of pre-
tending to have more memory that you really
had was viewed skeptically in many quarters
within DEC.

The OS kept a number of access bits associated with each virtual page. One of these was "trap to user" which caused a user program interrupt when that virtual page was accessed. This "trap to user" bit didn't have much utility.

## Password checking introduction

Like many modern systems, Tenex supported multiple users that could authenticate themselves with passwords. Unlike modern systems, there was a system call available to check other users passwords. 

> This password checking JSYS was given a user name string and a password string. It would return success or failure. To prevent quickly trying many passwords, it delayed returning to the user code for 3 seconds on failure.

>  The password checking routine in the OS was implemented as standard string comparison algorithm. While I don't remember if the strings were prepended with their length or if they were terminated with a null, the algorithm would step down each character in the strings, making the comparison. It would indicate failure as soon it saw a disagreement. If it got too the end of both strings, it would indicate success. On failure, it would delay 3 seconds.

## Bug explanation

To exploit the vulnerability, Alan's program would place the first character it didn't know at the end of the page, with the rest of the string on the next page. The next page would have the "trap to user" bit set to detect any access to the page. If the character was wrong, the OS would never access the characters on the next page and would return false after a three second delay. If the character was correct, the OS would check the next character, which would access the next page and cause a trap to the user.

By changing the character each time and continuing with the next character after one was guessed, it was possible to guess a password one character at a time. 

>  My program placed the first character it didn't know at the end of a page and would place the rest of the string on the next page. This meant that if that character was wrong, the OS would never access the characters on the next page. It would step through all possible characters for the first character until it matched, would type that character on the console, and then move on to the next character. So the password would slowly be typed on the terminal with each character coming out after an average of 30-40 seconds. This would continue until the password was complete.
The OS kept a number of access bits associated with each virtual page. One of these was "trap to user" which caused a user program interrupt when that virtual page was accessed. I set this bit on for the page with the additional characters. This caused an interrupt if the previous character was good. Because it couldn't perform the interrupt while in the OS, it waited until right after the return from the password JSYS.

The "trap to user" bit was the simplest way to validate the flaw. This "trap to user" bit didn't have much utility. But more common mechanisms like turning off read access or not mapping a page would have also worked.

## Alternative exploits

Alan used the "trap to user" bit to detect whether a page was accessed, but more common mechanisms like turning off read access or not mapping a page would have also worked.

It would also be possible to exploit this bug using a timing attack. The amount of physical memory was in the range of 64.000 words and the virtual address space was much larger so it would have been easy to force pages out of physical memory by accessing other pages. One could then time the access.

## Fix

After finding the bug and proving that it was exploitable, Alan told Ray Tomlinson (famous for inventing email) over lunch. Later that day, Bob Clements created a patch to fix the issue. The solution he came up with was to reference the first and last character before doing anything, so that page access no longer depended on the password:

> I put the patch in, and made it as trivial as possible to get it out in the
field.  Test the eighth WORD before doing anything.  That would give you a bad memory reference immediately if you were playing against the page boundary.  Otherwise, drop into the 39 character loops.

## Release procedure

In 1974 there was no responsible disclosure yet and not many companies had experience with fixing security bugs. "Computer security research was basically non-existent in 1974. So I simply saw this as a bug in the OS as opposed to something more important.", writes Alan. Even so, BBN took care that all sites running Tenex would receive the patch at approximately the same time. "They sent an email to all the sites running Tenex saying that an important security patch was forthcoming at a particular time. They didn't want people exploiting it by getting the actual patch before others." At that time there would be between a dozen and two dozen sites with Tenex machines.

## Proper fix

The behavior of the password check could no longer be determined from the page accesses with the fix Bob Clements put in. However, developers at BBN soon became aware that storing passwords in plaintext was not very secure to begin with. Bob writes, "A day or so later, someone realized that relying on keeping plaintext copies of passwords in the system files was not a smart thing to do.  So we created encrypted passwords."

"We encrypted the passwords so you had to compare the encrypted form
of the password with the pre-stored version of the encrypted password.
At no point was the un-encrypted password in memory except briefly
when you first created the password and then briefly when you threw the
plaintext up against the encrypted version.  This was a lot like modern Unix."

"Bob Thomas did the encryption code.  I did the coding to allow either the
plaintext or the crypto to be checked, stored in the same 8-word system
form of the text.  I distinguished the two forms by checking to see whether
a 7-bit character pointer (plaintext) was being tried or a 36-bit pointer
(crypto) version was in the official file.  This allowed people some time to
switch over.  Or not, if the 7-bit was OK and nobody had to change all at once."

## Conclusion



Tenex introduction
    1970s
    Operating system with paging.
    Developed at BBN.
    JSYS system calls.

Bug summary
    Alan Bell introduction
    Possible to guess characters one character at a time by checking page access.

Paging introduction
    Paging hardware

Password checking introduction
    Password checking JSYS
    Checks one character at a time
    3 second delay

Bug explanation

Alternative exploits
    Timing attack would be possible

Fix
    Fix workings
    Robert Clements introduction
    Release procedure

Proper fix
    Encrypted passwords

Conclusion





http://css.csail.mit.edu/6.858/2015/lec/l16-timing-attacks.txt
Famous password timing attack
    Time page-faults for password guessing [Tenex system]
    Suppose the kernel provides a system call to check user's password.
      Checks the password one byte at a time, returns error when finds mismatch.
    Adversary aligns password, so that first byte is at the end of a page,
      rest of password is on next page.
    Somehow arrange for the second page to be swapped out to disk.
      Or just unmap the next page entirely (using equivalent of mmap).
    Measure time to return an error when guessing password.
      If it took a long time, kernel had to read in the second page from disk.
      [ Or, if unmapped, if crashed, then kernel tried to read second page. ]
      Means first character was right!
    Can guess an N-character password in 256*N tries, rather than 256^N.



Was it even a timing attack?

https://groups.google.com/forum/#!msg/alt.folklore.computers/v9KnB8BIXGY/aZ-qDLtD0gAJ
Mark Crispin:
The bug was that you could align the given password string so that it
was at the end of a page boundary, and have the next page in the
address space mapped to a non-existant page of a write-protected file.
Normally, Tenex would create a page when you tried to access a
non-existant page, but in this case it couldn't (since the file was
write-protected).

So, you did a password-checking system call (e.g. the system call
which tried to obtain owner access to another directory) set up in
this way and you would get one of two failures: Incorrect Password
meant that your prefix was wrong, Page Fault meant that your prefix
was right but that you need more characters.

Larry Campbell:
But if the first byte matched, the page containing the second byte will no
longer be marked "nonexistent", since the kernel has just created a page
full of zeroes.

Bob Clements:
(The quick fix was to always reference the first and eighth word
of the test password block, so the page would always be created.
For those who don't know, the number "eight" comes from the
fact that all user names, passwords, filenames, filename extensions
and the like are up to 39 characters long on TENEX, or 40 characters
with the terminating NULL, and there are of course five ASCII [7-bit]
characters in a 36-bit word.)

1 TENEX and TOPS-20, Dan Murphy, http://tenex.opost.com/ahc_20150101_jan_2015.pdf
