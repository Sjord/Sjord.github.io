---
layout: post
title: "Tenex password guessing bug"
thumbnail: pdp10-240.jpg
date: 2016-10-24
---

In 1974, BBN computer scientist Alan Bell discovered a security flaw in the operating system Tenex. The password checking procedure would access certain memory pages during checking. By looking at which pages were accessed during checking of the password, user passwords could be guessed one character at a time.

## Tenex introduction

Bolt, Beranek and Newman (BBN) was a company initially specialized in acoustics. Because acoustic models required a lot of computation, BBN got interested in computing. The company was the first to purchase a PDP-1. The scientists at BBN had specific requirements for computers. First, they specifically wanted time sharing functionality, so that multiple programs could be run in parallel. Secondly, they wanted virtual memory so that programs were not limited by the 4096 words of memory the PDP-1 had. For the PDP-1, they implemented both in software.

Later, BBN tried to get DEC to change the PDP-6 to conform to their wishes, to no avail. Then in 1970 BBN decided to modify a PDP-10 to fit their needs. BBN added a paging hardware module to the PDP-10 to implement virtual memory in hardware. This also needed support in software, and BBN decided to implement its own operating system: Tenex.

![PDP-10 console](/images/pdp10-console.jpg "PDP-10 console")

## Bug summary

Tenex had several several system calls in the operating system, which could be called using the JSYS instruction. One of these system calls was a procedure to check the password of another user. This procedure would check the password one character at a time. By putting the password to be checked on a page boundary, and checking whether the second page is accessed, it was possible to guess passwords one character at a time.

The bug was discovered in 1974 by a young computer scientist, Alan Bell. Alan joined BBN just a year before and was interested in the operating system, which was the most complicated piece of software that he encountered so far. He read the source code of Tenex in his own time to figure out how it worked, and this is how he discovered the flaw.

## Paging introduction

The scientists at BBN wanted to run memory-intensive LISP programs, but the PDP-1 came with only 4096 words of memory. A solution to this was to implement virtual memory: memory could be made to look bigger by storing parts on disk and putting it in memory just as it was needed. 
Blocks of memory would be swapped between disk and memory to seemingly have a lot of memory. These blocks are called pages and the process of exchaning pages between disk and memory is called paging.

Paging makes it possible to address more memory than the machine actually has. This "virtual memory" is simulated by storing rarely used memory on disk and keeping only the most often used pages in memory. If a memory page is referenced that is not currently in memory, program execution is paused, the page is loaded from disk and put in memory, and execution continues. 

BBN was one of the first to see the advantage of paging, but the advantages weren't obvious to everyone. According to Dan Murphy, who worked at BBN from 1965 until 1972: "Then too, paging and "virtual memory" were still rather new concepts at that time.  Significant commercial systems had yet to adopt these techniques, and the idea of pretending to have more memory that you really had was viewed skeptically in many quarters within DEC."

At BBN they implemented paging for the PDP-1 in software. Although it worked well, it was pretty slow.  "However, the actual number of references
was sufficiently high that a great deal of time
was spent in the software address translation
sequence, and we realized that, ultimately,
this translation must be done in hardware if a
truly effective paged virtual memory system
were to be built."

BBN asked DEC to design a PDP-6 with paging, but eventually DEC stopped producing PDP-6 models without there ever having been paging support. After a couple of years, BBN decided to build their own virtual memory support using the PDP-10. Because the PDP-10 had insufficient support for this, a hardware module was added between the processor and the memory, that handled the paging. 

![BBN pager hardware](/images/bbn-pager.jpg "BBN pager hardware")

Besides the hardware there was also software support for paging in Tenex. Each program under Tenex could use the full address space for adressing memory. Furthermore, programs had a lot of control over the paging. Alan Bell writes: "The virtual memory system allowed the user a lot of control over what pages were placed in the address space. While usually a paging file was in the address space, it was also possible to map data files, etc. Therefore, the user was given control over what was mapped where and the type of access allowed."

One of the paging settings was the "trap to user" bit. This would cause an interrupt to the program whenever a page was accessed. This was generally not very useful, but was an easy way to exploit the character guessing bug.

## Password checking introduction

Like many modern systems, Tenex supported multiple users that could authenticate themselves with passwords. The operating system provided a system call system call available to check other users passwords. "This password checking JSYS was given a user name string and a password string. It would return success or failure. To prevent quickly trying many passwords, it delayed returning to the user code for 3 seconds on failure."

The two passwords, the one in the system and the one supplied by the user, were compared using a standard string comparison algorithm: one character at a time. "The algorithm would step down each character in the strings, making the comparison. It would indicate failure as soon it saw a disagreement. If it got too the end of both strings, it would indicate success."

![String comparison is done one character at a time](/images/tenex-serial-string-compare.png "String comparison is done one character at a time")

## Bug explanation

To exploit the vulnerability, Alan's program would place the first character it didn't know at the end of the page, with the rest of the string on the next page. The next page would have the "trap to user" bit set to detect any access to the page. If the character was wrong, the OS would never access the characters on the next page and would return false after a three second delay. If the character was correct, the OS would check the next character, which would access the next page and cause a trap to the user.

By changing the character each time and continuing with the next character after one was guessed, it was possible to guess a password one character at a time. 

>  My program placed the first character it didn't know at the end of a page and would place the rest of the string on the next page. This meant that if that character was wrong, the OS would never access the characters on the next page. It would step through all possible characters for the first character until it matched, would type that character on the console, and then move on to the next character. So the password would slowly be typed on the terminal with each character coming out after an average of 30-40 seconds. This would continue until the password was complete.
The OS kept a number of access bits associated with each virtual page. One of these was "trap to user" which caused a user program interrupt when that virtual page was accessed. I set this bit on for the page with the additional characters. This caused an interrupt if the previous character was good. Because it couldn't perform the interrupt while in the OS, it waited until right after the return from the password JSYS.

The "trap to user" bit was the simplest way to validate the flaw. This "trap to user" bit didn't have much utility. But more common mechanisms like turning off read access or not mapping a page would have also worked.

![If the first character is correct, the next page is accessed and a interrupt is sent to the user.](/images/tenex-password-guess-bug-incorrect.png "If the first character is correct, the next page is accessed and a interrupt is sent to the user.")

![If the first character is correct, the next page is accessed and a interrupt is sent to the user.](/images/tenex-password-guess-bug-correct.png "If the first character is correct, the next page is accessed and a interrupt is sent to the user.")

## Alternative exploits

Alan used the "trap to user" bit to detect whether a page was accessed, but more common mechanisms like turning off read access or not mapping a page would have also worked.

It would also be possible to exploit this bug using a timing attack. The amount of physical memory was in the range of 64K words and the virtual address space was much larger so it would have been easy to force pages out of physical memory by accessing other pages. One could then time the access.

## Fix

After finding the bug and proving that it was exploitable, Alan told Ray Tomlinson (famous for inventing email) over lunch. Later that day, Bob Clements created a patch to fix the issue. The solution he came up with was to reference the first and last character before doing anything, so that page access no longer depended on the password: "I put the patch in, and made it as trivial as possible to get it out in the
field.  Test the eighth WORD before doing anything.  That would give you a bad memory reference immediately if you were playing against the page boundary.  Otherwise, drop into the 39 character loops."

## Release procedure

In 1974 there was no responsible disclosure yet and not many companies had experience with fixing security bugs. "Computer security research was basically non-existent in 1974. So I simply saw this as a bug in the OS as opposed to something more important.", writes Alan. Even so, BBN took care that all sites running Tenex would receive the patch at approximately the same time. "They sent an email to all the sites running Tenex saying that an important security patch was forthcoming at a particular time. They didn't want people exploiting it by getting the actual patch before others." At that time there would be between a dozen and two dozen sites with Tenex machines.

## Proper fix

The behavior of the password check could no longer be determined from the page accesses with the fix Bob Clements put in. However, developers at BBN soon became aware that storing passwords in plaintext was not very secure to begin with. Bob writes, "A day or so later, someone realized that relying on keeping plaintext copies of passwords in the system files was not a smart thing to do.  So we created encrypted passwords."

"We encrypted the passwords so you had to compare the encrypted form
of the password with the pre-stored version of the encrypted password.
At no point was the un-encrypted password in memory except briefly
when you first created the password and then briefly when you threw the
plaintext up against the encrypted version.  This was a lot like modern Unix."

## Conclusion

In Tenex, it was possible to configure the paging system to notify the user when a specific page was accessed. This could be used to guess the password of a user in a couple of minutes. This is a typical example of a side-channel attack, where the behavior of a program is determined without communicating directly with the program. Although at the time there was little known about security research, BBN handled the bug admirably by distributing a patch within days of the bug being found.

## Read more

1. [TENEX and TOPS-20](http://tenex.opost.com/ahc_20150101_jan_2015.pdf), [Dan Murphy](http://www.opost.com/dlm/) 
1. ["Tenex hackery" in alt.folklore.computers](https://groups.google.com/d/msg/alt.folklore.computers/v9KnB8BIXGY/aZ-qDLtD0gAJ)
1. [TENEX, a paged time sharing system for the PDP-10](http://www.dtic.mil/cgi-bin/GetTRDoc?AD=AD0729261), Daniel Bobrow, Jerry Burchfiel, Dan Murphy, Ray Tomlinson
