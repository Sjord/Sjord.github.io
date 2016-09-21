
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
