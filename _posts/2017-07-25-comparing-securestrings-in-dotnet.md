---
layout: post
title: "Comparing secure strings in .NET"
thumbnail: ph-compare-240.jpg
date: 2017-11-08
---

In .NET, the SecureString class protects data in memory. The contents of a SecureString object are not accessible as a normal string and that makes it hard to work with it. This post describes some secure ways to compare two SecureString objects.

## The SecureString class

In an [earlier post](/2016/05/22/should-passwords-be-cleared-from-memory/) we described that secrets can leak if they are not handled in a controlled way in memory. When storing strings in an immutable string type it is not possible to remove the value from memory, and any modification of the string will actually result in a copy. To mitigate this problem, .NET has a [SecureString](https://msdn.microsoft.com/en-us/library/system.security.securestring(v=vs.110).aspx) class.

The documentation describes the security properties of the SecureString type:

> A SecureString object is similar to a String object in that it has a text value. However, the value of a SecureString object is pinned in memory, may use a protection mechanism, such as encryption, provided by the underlying operating system, can be modified until your application marks it as read-only, and can be deleted from computer memory either by your application calling the Dispose method or by the .NET Framework garbage collector.

A SecureString is pinned in memory, which means that it is not moved around or copied, but it always in the same place in memory. This way we can clear that memory when we are done with the SecureString, so that our secret is no longer exposed. To properly use the security offered by SecureString, we need to take care when using it so it is not exposed anywhere else in memory. For example, by converting the SecureString to a normal String we would break the offered security, because now our secret is somewhere else in memory in an immutable type that we cannot clear. This makes the SecureString hard to work with.


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

This way, we can create two BSTRs from our SecureStrings and compare those. Comparing the unmanaged memory can be done in several ways:

* Using [unsafe code](https://stackoverflow.com/a/4502736/182971). Call [ToPointer](https://msdn.microsoft.com/en-us/library/system.intptr.topointer(v=vs.110).aspx) on both BSTRs and compare the values of the pointers in a loop.
* Use [Marshal.ReadInt32](https://msdn.microsoft.com/en-us/library/eawzfdz5(v=vs.110).aspx) to read the length prefix of each BSTR, then call [Marshal.ReadByte](https://msdn.microsoft.com/en-us/library/a0c0f616(v=vs.110).aspx) that many times on each BSTR.
* Use P/Invoke to call a native function to do the compare.

Here is an example to call [lstrcmp](https://msdn.microsoft.com/en-us/library/windows/desktop/ms647488(v=vs.85).aspx) using P/Invoke to compare the two BSTR instances:

    [DllImport("kernel32.dll", CharSet = CharSet.Auto)]
    static extern int lstrcmp(IntPtr lpString1, IntPtr lpString2);

    ...

    var isEqual = lstrcmp(b1, b2) == 0;

This may be the easiest way to compare two BSTR instances, and thus two SecureString instances.

## Time-safe comparing SecureStrings

The problem with lstrcmp is that the time it runs depends on how equal the two strings are. It first compares the first character of the two strings; if those are equal it compares the second character, and so on. The more characters match the longer it takes. In certain cases, this makes it possible for an attacker to guess one character at a time, by measuring the time it takes to compare the two strings. To prevent this, we want the comparison function to take the same amount of time, no matter how much of both strings are equal.

Unfortunately there is no constant-time string comparison function on Windows, so we have to make our own. A typical time-safe comparison uses XOR on each character pair.

    static bool IsEqual(IntPtr bstr1, IntPtr bstr2)
    {
        var length1 = Marshal.ReadInt32(bstr1, -4);
        var length2 = Marshal.ReadInt32(bstr2, -4);

        if (length1 != length2) return false;

        var equal = 0;
        for (var i = 0; i < length1; i++)
        {
            var c1 = Marshal.ReadByte(bstr1 + i);
            var c2 = Marshal.ReadByte(bstr2 + i);
            equal |= c1 ^ c2;
        }
        return equal == 0;
    }

As you can see we first read the length of each string, which is four bytes before our BSTR pointer. Then if the lengths are equal we process every character pair. The XOR of the two pairs is only zero if they are equal, so the `equal` variable will only stay zero if all characters are equal. At no point do we break out of the loop, so the contents of the strings do not influence the time it takes to run this code.

## Comparing hashes

The `IsEqual` function above first compares the lengths of the two secure strings, and returns early if they are different. This could make it possible for the attacker to guess the length of the secret: if the function returns quickly, the length is incorrect. If the function takes the time to compare the values, the length is correct. This can be prevented by hashing the two strings and comparing those. The attacker should not be able to reproduce the hash, which can be accomplished by using a random key and a HMAC. In pseudo-code:

    bool Compare(a, b) {
        key = CreateRandomKey();
        return hmac(key, a) == hmac(key, b)
    }

Since the attacker doesn't know the HMAC values, we don't even need to use a time-safe comparison function. We can implement this using P/Invoke on functions in the Windows Crypto API.

First, we create a cryptographic context. If C were object-oriented, this would be our object on which we can call methods. Since it isn't, we create a context which we need to pass to every function we call. Second, we create a random key using [CryptGenKey](https://msdn.microsoft.com/en-us/library/windows/desktop/aa379941(v=vs.85).aspx). Since we use a random key each time we compare two values, an attacker can never figure out the value of the key, even if our algorithm was vulnerable to a timing attack. Then we call the function CreateHmacForSecureString, which is described below, and we clean up the key and the context. 

Note that this code example is missing some crucial error handling. If one of the crypto functions fails, both `hmac1` and `hmac2` will be filled with null-bytes. The crypto functions won't throw exceptions on error. Instead, they will silently fail and the `CompareSecureStrings` function will return true to indicate that the strings are equal, even if they aren't. As you see, creating a secure program becomes a lot harder when using these low-level functions.

        bool CompareSecureStrings(SecureString ss1, SecureString ss2)
        {
            IntPtr hProv = IntPtr.Zero;
            CryptAcquireContext(ref hProv, null, null, PROV_RSA_FULL, CRYPT_VERIFYCONTEXT);

            IntPtr hKey = IntPtr.Zero;
            CryptGenKey(hProv, CALG_RC2, CRYPT_EXPORTABLE, ref hKey);

            var hmac1 = CreateHmacForSecureString(hProv, hKey, ss1);
            var hmac2 = CreateHmacForSecureString(hProv, hKey, ss2);

            CryptDestroyKey(hKey);
            CryptReleaseContext(hProv, 0);

            return hmac1.SequenceEqual(hmac2);
        }

In the `CreateHmacForSecureString` function, we set up a hash object by calling `CryptCreateHash`, and instruct it to use SHA512 as an underlying function for the HMAC. Then we marshal our SecureString to a BSTR, and hash the contents. Finally, we retrieve and return the resulting HMAC value.

        byte[] CreateHmacForSecureString(IntPtr hProv, IntPtr hKey, SecureString ss)
        {
            IntPtr hHash = IntPtr.Zero;
            CryptCreateHash(hProv, CALG_HMAC, hKey, 0, ref hHash);

            var hmacInfo = new _HMAC_Info() { HashAlgid = CALG_SHA512 };
            CryptSetHashParam(hHash, HP_HMAC_INFO, ref hmacInfo, 0);

            var bstr = Marshal.SecureStringToBSTR(ss);
            var len = (uint)Marshal.ReadInt32(bstr, -4);
            CryptHashData(hHash, bstr, len, 0);
            Marshal.ZeroFreeBSTR(bstr);

            uint length = 64;
            byte[] pbData = new byte[64];
            CryptGetHashParam(hHash, HP_HASHVAL, pbData, ref length, 0);

            CryptDestroyHash(hHash);

            return pbData;
        }

As you can see, it is entirely possible to create a comparison function that is safe against timing attacks. Our last example doesn't even leak the length of the secret. However, it is also very hard to implement this correctly. You have to be aware of both the memory and timing properties of each function. You have to clean up everything manually, even if an error occurs.

## Conclusion

It is pretty hard to use the SecureString correctly. By converting it to native memory, you are dependent on unsafe code, pointer arithmetic or P/Invoke to native functions. It is hard to imagine a situation where creating such a comparison function is worth the effort. SecureString protects against an improbable attack vector, and the amount of effort to create a secure comparison function while maintaining this protection may not be worth it.

The example code can be found on [GitHub](https://github.com/Sjord/CompareSecureStrings).
