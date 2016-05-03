## Notes

Java raadt char[] aan in plaats van string, en die overschrijven als je klaar bent.
Beschermt tegen aanvallers die je geheugen kunnen lezen.
Wat zijn de situaties waarin dat kan?
Hoe kan je een string veilig wissen?
    test programma in python
    string laten garbage collecten
    string overschrijven
    string interning
    Geheugen is geabstraheerd, zelfs in C door de optimizer, dus het is niet mogelijk om de contents van het geheugen te bepalen

## Bla

The [Java Cryptography Architecture Guide](http://docs.oracle.com/javase/8/docs/technotes/guides/security/crypto/CryptoSpec.html) says you should use a char array instead of a String, so that you can clear secrets from memory:

> It would seem logical to collect and store the password in an object of type java.lang.String. However, here's the caveat: Objects of type String are immutable, i.e., there are no methods defined that allow you to change (overwrite) or zero out the contents of a String after usage. This feature makes String objects unsuitable for storing security sensitive information such as user passwords. You should always collect and store security sensitive information in a char array instead.

This implies that if you have a secret, you should clear it from memory when you are done with it, so it does not stay in memory for an attacker to read.

## Possible ways to read memory

There several ways an attacker can read memory:

* [Heartbleed](http://heartbleed.com/), the buffer over-read bug in OpenSSL, allows for reading memory remotely.
* [Cold boot attack](https://en.wikipedia.org/wiki/Cold_boot_attack), where the attacker reboots a computer with his own software to read the memory.
* [DMA attack](https://en.wikipedia.org/wiki/DMA_attack), where an attacker reads memory from the Firewire or Thunderbolt port, for example.
* An attacker can read files and uses /proc/self/mem to read memory. This is not as simple as it sounds, because you need to read at a specific position in this file.
* Memory is written to disk because of paging or hibernation, and an attacker gets access to the disk.
* The program crashes and a core dump is written to disk.

## Clearing a secret from memory in Python

### Our test setup

We are going to create a Python script that stores a secret key in a variable, and then we read the memory of this process to see whether the secret is present in memory.

There are a couple of ways to read the memory of a process:
* Read /proc/self/mem or /proc/$pid/mem.
* Use gdb to dump a process' memory.
* Create a core file using either gcore or by aborting the program.

Any process can read its own /proc/self/mem memory file, but only at specific offsets. The contents of the file correspond with the address space of the memory, and not all addresses have memory mapped to them. The file /proc/self/maps shows which address contains what. You can then seek to that position and read a blob of memory. Reading memory of other processes is not allowed unless you attach a process using ptrace. This is what debuggers use. Instead of doing this, it may be easier to use a real debugger like gdb to dump the memory to a file.

Another way is to create a core file. This can be done with the command `gcore` (after installing gdb), or by aborting the process and letting the operating system create a core file.

There are several settings that influence whether a core file is created when a program exits abnormally:
* `/proc/sys/kernel/core_pattern` - contains the filename to create or program to execute.
* `ulimit -c` determines the maximum size of a core file. If you always want core files, use `ulimit -c unlimited`.

After configuring that we want core files, we can call `os.abort()` in Python to exit our program and dump the memory.

We want to test having a secret variable in memory. However, we can't put it in the source code because the whole source code will be read in memory. We will read the secret from another file. Here is our test code:

    import os

    with open('key.txt') as fp:
        secret = fp.read()

    os.abort()

After running this, a core file is generated and we use grep to check whether the secret was present in memory:

    $ grep supersecretstring 5858.python.core 
    Binary file 5858.python.core matches

### Clearing variables

Let's try to delete the secret variable from memory by assigning a new value to the `secret` variable:

    secret = None

When we run grep again, we see that the secret is still present in memory. Obviously the `secret` variable no longer points to the secret value, but the value itself was not deleted. Let's try to run the garbage collector to clean up things after us:

    import gc
    secret = None
    gc.collect()

Our secret is still present in memory. The garbage collector has freed the memory and will use it again in the future, but it has not cleared the contents. We can run some memory-intensive code to try and overwrite the just-freed memory, but there is no guarantee that the secret will be overwritten. If we want to overwrite it, we have do so explicitly.

### SecureString

[SecureString](https://github.com/dnet/pysecstr) is a module for Python 2 that adds a `clearmem` function that overwrites the contents of a string. The C implementation looks like this:

    static PyObject* SecureString_clearmem(PyObject *self, PyObject *str) {
        char *buffer;
        Py_ssize_t length;

        if (PyString_AsStringAndSize(str, &buffer, &length) != -1) {
            OPENSSL_cleanse(buffer, length);
        }
        return Py_BuildValue("");
    }

Let's try SecureString with our example program:

    import SecureString
    SecureString.clearmem(secret)

Grep finds nothing, indicating that the secret is indeed cleared from memory.

### Memset


/proc/self/mem lezen werkt niet http://unix.stackexchange.com/questions/6301/how-do-i-read-from-proc-pid-mem-under-linux
os.abort() is makkelijkst
/proc/sys/kernel/core_pattern
    |/usr/share/apport/apport %p %s %c %P
    stopt crash in /var/crash/
    bae64 encoded, dus niet zo handig



sjoerd@ubuntu:~/dev/safestrings$ sudo bash
root@ubuntu:~/dev/safestrings# echo '%p.%e.core' > /proc/sys/kernel/core_pattern
root@ubuntu:~/dev/safestrings# exit
sjoerd@ubuntu:~/dev/safestrings$ ls
normal.py
sjoerd@ubuntu:~/dev/safestrings$ python normal.py 
Aborted

Geen core dumped?

ulimit -c unlimited

sjoerd@ubuntu:~/dev/safestrings$ grep helloworld 3720.python.core 
Binary file 3720.python.core matches


secret = 'helloworld'
secret = None

nog steeds er in

met gc.collect() nog steeds

Blijkt dat source code in de core zit. Lees key uit extern bestand

met secret = None nog steeds in geheugen
met gc.collect() nog steeds

libssl-dev, python-dev
pip install SecureString

SecureString.clearmem(secret)  # niet in geheugen!!! :)

Niet compatible met Python 3

Python string is niet meer immutable

memset werkt tegen: segfault bij clearen, met key nog in geheugen

Python heeft een string literal pool. Dus mutaten gaat kapot! String interning

Je wist één string, andere string gaat ook stuk



http://security.stackexchange.com/questions/74718/is-it-more-secure-to-overwrite-the-value-char-in-a-string
.NET heeft SecureString

http://security.stackexchange.com/questions/29019/are-passwords-stored-in-memory-safe
http://stackoverflow.com/questions/8881291/why-is-char-preferred-over-string-for-passwords-in-java/8889285#8889285
http://security.stackexchange.com/questions/122189/can-secrets-be-made-safe-in-memory
