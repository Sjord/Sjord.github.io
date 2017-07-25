---
layout: post
title: "Comparing secure strings in .NET"
thumbnail: ph-compare-240.jpg
date: 2017-11-08
---

In an [earlier post](/2016/05/22/should-passwords-be-cleared-from-memory/) we described that secrets can leak if they are not handled in a controlled way in memory. When storing strings in an immutable string type it is not possible to remove the value from memory, and any modification of the string will actually result in a copy. To mitigate this problem, .NET has a [SecureString](https://msdn.microsoft.com/en-us/library/system.security.securestring(v=vs.110).aspx) class.

The documentation describes the security properties of the SecureString type:

> A SecureString object is similar to a String object in that it has a text value. However, the value of a SecureString object is pinned in memory, may use a protection mechanism, such as encryption, provided by the underlying operating system, can be modified until your application marks it as read-only, and can be deleted from computer memory either by your application calling the Dispose method or by the .NET Framework garbage collector.

A SecureString is pinned in memory, which means that it is not moved around or copied, but it always in the same place in memory. This means that we can clear that memory when we are done with the SecureString, so that our secret is no longer exposed. To properly use the security offered by SecureString, we need to take care when using it so it is not exposed anywhere else in memory. For example, by converting the SecureString to a normal String we would break the offered security, because now our secret is somewhere else in memory in an immutable type that we cannot clear. This makes the SecureString hard to work with.


## Comparing SecureStrings

Let's say we want to compare two SecureString instances to see whether they are equal. Unfortunately, we can't use the `Equals` method. It is not overloaded and will simply test whether the two objects are the same instance, not whether they have the same value. As mentioned earlier, we also don't want to use String or any other immutable type. Instead, we marshal the SecureString to a [BSTR](https://msdn.microsoft.com/en-us/library/windows/desktop/ms221069(v=vs.85).aspx). This will create a plaintext copy of our string. This copy is not managed by the garbage collector, and we have to clear it ourselves when we are done with it. Because this copy is unmanaged and we clean it up afterward, we keep control over where the secret is stored in memory.

Converting a SecureString to a BSTR is done with the [Marshal.SecureStringToBSTR](https://msdn.microsoft.com/en-us/library/system.runtime.interopservices.marshal.securestringtobstr(v=vs.110).aspx) method. Clearing and releasing the memory is done with the [Marshal.ZeroFreeBSTR](https://msdn.microsoft.com/en-us/library/system.runtime.interopservices.marshal.zerofreebstr(v=vs.110).aspx) method.

    var mySecret = new NetworkCredential("", "secret").SecurePassword;
    var myBstr = Marshal.SecureStringToBSTR(mySecret);
    try
    {
        // do something with myBstr
    }
    finally
    {
        Marshal.ZeroFreeBSTR(myBstr);
    }

This way, we can create two BSTRs from our SecureStrings and compare those. Comparing the unmanaged code can be done in several ways:

* Using [unsafe code](https://stackoverflow.com/a/4502736/182971). Call [ToPointer](https://msdn.microsoft.com/en-us/library/system.intptr.topointer(v=vs.110).aspx) on both BSTRs and compare the values of the pointers in a loop.
* Use [Marshal.ReadInt32](https://msdn.microsoft.com/en-us/library/eawzfdz5(v=vs.110).aspx) to read the length prefix of each BSTR, then call [Marshal.ReadByte](https://msdn.microsoft.com/en-us/library/a0c0f616(v=vs.110).aspx) that many times on each BSTR.
* Use P/Invoke to call a native function to do the compare.

Here is an example to call [lstrcmp](https://msdn.microsoft.com/en-us/library/windows/desktop/ms647488(v=vs.85).aspx) using P/Invoke to compare the two BSTR instances:

    [DllImport("kernel32.dll", CharSet = CharSet.Auto)]
    static extern int lstrcmp(IntPtr lpString1, IntPtr lpString2);

    ...

    var isEqual = lstrcmp(b1, b2) == 0;

This may be the easiest way to compare two BSTR instances, and thus two SecureString instances.
