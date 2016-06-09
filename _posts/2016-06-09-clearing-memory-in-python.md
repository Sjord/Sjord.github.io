---
layout: post
title: "Clearing memory in Python"
thumbnail: shredded-paper-240.jpg
date: 2016-06-09
---

The post ["Clearing secrets from memory"](/2016/05/22/should-passwords-be-cleared-from-memory/) discussed that it might be beneficial to clear secrets from memory after using them. This post discusses how to do this in Python.

## Our test setup

We are going to create a Python script that stores a secret key in a variable, and then we read the memory of this process to see whether the secret is present in memory.

There are a couple of ways to read the memory of a process:
* Read `/proc/self/mem` or `/proc/$pid/mem`.
* Use [gdb](https://www.gnu.org/software/gdb/) to dump a process' memory.
* Create a core file using either gcore or by aborting the program.

Any process can read its own `/proc/self/mem` memory file, but only at specific offsets. The contents of the file correspond with the address space of the memory, and not all addresses have memory mapped to them. The file `/proc/self/maps` shows which address contains what. You can then seek to that position and read a blob of memory. Reading memory of other processes is not allowed unless you attach a process using [ptrace](https://en.wikipedia.org/wiki/Ptrace). This is what debuggers use. Instead of doing this, it may be easier to use a real debugger like gdb to dump the memory to a file.

Another way is to create a core file. This can be done with the command `gcore` (after installing gdb), or by aborting the process and letting the operating system create a core file.

There are several settings that influence whether a core file is created when a program exits abnormally:
* `/proc/sys/kernel/core_pattern` - contains the filename to create or program to execute.
* `ulimit -c` determines the maximum size of a core file. If you always want core files, use `ulimit -c unlimited`.

After configuring that we want core files, we can call `os.abort()` in Python to exit our program and dump the memory.

We want to test having a secret variable in memory. However, we can't put it in the source code because the whole source code will be read in memory. We'll read the secret from another file. Here is our test code:

    import os

    with open('key.txt') as fp:
        secret = fp.read()

    os.abort()

After running this, a core file is generated and we use grep to check whether the secret was present in memory:

    $ grep supersecretstring 5858.python.core 
    Binary file 5858.python.core matches

## Methods to clear memory

Now that we can check the process memory for our secret string, we can try several ways to try to clear that secret from memory.

### Clearing variables

Let's try to delete the secret variable from memory by assigning a new value to the `secret` variable:

    secret = None

When we run grep again, we see that the secret is still present in memory. Obviously the `secret` variable no longer points to the secret value, but the value itself was not deleted. Let's try to run the garbage collector to clean up things after us:

    import gc
    secret = None
    gc.collect()

Our secret is still present in memory. The garbage collector has freed the memory and will use it again in the future, but it has not cleared the contents. We can run some memory-intensive code to try and overwrite the just-freed memory, but there is no guarantee that the secret will be overwritten. If we want to overwrite it, we have do so explicitly.

### Bytearray

Java suggests using a `byte[]`, because it is mutable and can be cleared after use. In Python 3 we have something similar, the `bytearray`. It is possible to read a secret into a bytearray and clear it afterwards, like this:

    secret = bytearray(20)
    with open('key.txt', 'rb') as fp:
        fp.readinto(secret)

    # Use `secret`

    for i in range(len(secret)):
        secret[i] = 0

The problem is that when using secret, it should never be converted to a string. Presumably you want to use the secret for something. You can't use it with pycrypto, and the [pull request](https://github.com/dlitz/pycrypto/pull/81) to change that simply converts the bytearray to a string. The [requests](http://docs.python-requests.org/en/master/) library sends numbers instead of text when given a bytearray. If we want to sent text we have to convert the bytearray to a string, which again puts it in memory without the possibility to remove it.

### Memset

We can use [ctypes](https://docs.python.org/2/library/ctypes.html) to call memset, a function to write memory. One [StackOverflow answer](http://stackoverflow.com/questions/982682/mark-data-as-sensitive-in-python/983525#983525) has an example:

    import sys
    import ctypes

    def zerome(string):
        location = id(string) + 20
        size     = sys.getsizeof(string) - 20

        memset =  ctypes.cdll.msvcrt.memset
        # For Linux, use the following. Change the 6 to whatever it is on your computer.
        # memset =  ctypes.CDLL("libc.so.6").memset

        print "Clearing 0x%08x size %i bytes" % (location, size)

        memset(location, 0, size)

This makes a lot of assumptions about the implementation of strings, which may change with different Python versions and different environments. In fact, when I run the code, it gave a segfault:

    Clearing 0x7fcf8c1bc38c size 35 bytes
    Segmentation fault (core dumped)

Even worse, the core dump contains the secret string. The code that was supposed to keep the secret secret exposed it while crashing.

### SecureString

[SecureString](https://github.com/dnet/pysecstr) is a module for Python 2 that has a `clearmem` function that overwrites the contents of a string. The C implementation looks like this:

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

Grep finds nothing, indicating that the secret is indeed cleared from memory. So this works, but read on for the major pitfall.

## String interning breaks when clearing strings

Normally, strings in Python are immutable. Python takes advantage of this fact by storing multiple strings with the same value once. This is called string interning.

    >>> "5" is str(5)
    True

What would normally be two instances of a string with the same content are stored only once, to save space and make operations more efficient. This is only done for short strings.

By clearing the string with `SecureString.clearmem`, we break the immutability of the string, which is a requirements for string interning to correctly work. If we call `clearmem` on a string, any other string with the same content may also be cleared:

    secret = "5"
    other_string = str(5)

    SecureString.clearmem(secret)
    print(other_string)

We would expect this to print "5", but it actually prints nothing. This is because `secret` and `other_string` point to the same memory location, which is cleared by our `clearmem` call. This means that if you have users entering passwords, they may be able to clear variables in your program if they know the contents.


## Conclusion

There is no good way to read, use and clear a string in Python. Using bytearray works, but it is hard to use a bytearray for anything without converting it to a string. Using `SecureString` works, but may also clear other strings within the program because of string interning.
