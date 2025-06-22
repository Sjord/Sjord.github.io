---
layout: post
title: "Encrypting identifiers in practice"
thumbnail: numbers-480.jpg
date: 2025-06-25
---

Previously, I wrote about [encrypting identifiers](/2023/08/02/encrypting-identifiers/). The idea is that the database uses incrementing numbers as primary key to identify objects, but the application only exposes encrypted keys to the user. Since theorizing about it in that post I implemented it in a web application, which gave me some new insights in how this can be used in practice.

## Cipher selection

Which underlying cipher can we use to encrypt the identifiers?

AES is widely supported and considered secure. It has a block size of 128 bits, which means the encrypted identifiers will be pretty long. Since we start with a 64 bit integer, we would need some way to wrap or pad our integer into a block. More on that later.

I started on a custom [Feistel cipher](https://github.com/Sjord/feistel-cipher). The advantage is that the block size can be set to any value. My cipher encrypts a 64-bit integer into a block of 82 bits, and then base-58 encodes it. This gives a good trade-off between identifier size and security. Creating your own cipher definitely falls in the *don't roll your own crypto* category. It is not widely supported (since I just made it up), which indirectly also means that it has bad performance. More on that later.

Using a [64-bit block cipher](/2023/08/30/encryption-64-bit-block-ciphers/) results in identifiers that are slightly shorter than I would like, but some 64-bit ciphers are well-supported, simple to use, and fast. I went with this option, and chose 3DES because it is supported by the OpenSSL library in PHP.

## Mechanism

A 64-bit integer is interpreted as a single 64-bit block of data and encrypted using a 3DES permutation. The resulting ciphertext is converted to hexadecimal.

```php
trait EncryptedHandle {
    private static string $key = '...';

    public static function findByHandle(string $handle) {
        return static::findOrFail(static::decrypt($handle));
    }

    public function initializeEncryptedHandle()
    {
        $this->makeHidden('id');
        $this->append('handle');
    }

    public function getHandleAttribute() {
        return static::encrypt($this->id);
    }

    private static function encrypt($id) {
        $key = hash_hmac('sha512', __CLASS__, static::$key, true);
        $plaintext = pack('q', $id);
        $ciphertext = openssl_encrypt($plaintext, 'des-ede3-ecb', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);
        return bin2hex($ciphertext);
    }

    private static function decrypt($handle) {
        $key = hash_hmac('sha512', __CLASS__, static::$key, true);
        $ciphertext = hex2bin($handle);
        $plaintext = openssl_decrypt($ciphertext, 'des-ede3-ecb', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);
        $parts = unpack('qid', $plaintext);
        return $parts['id'];
    }
}
```

## Tweak

If is useful if the encryption is [tweakable](/2023/09/27/tweakable-block-ciphers/): the identifier for customer 123 should be different than the identifier for invoice 123. When we encrypt "123", we also want to pass in what type of object is being encrypted. In the PHP example I HMAC the key with the magic `__CLASS__` variable, which contains the name of the current model.

## Performance

Cryptographers care about performance, and try to come up with fast algorithms. For cryptographers, this means that an optimized version implemented in a system level language has a high throughput. That is, it can encrypt many megabytes per second. However, the performance requirements for encrypting identifiers are different. First, we encrypt a single block at a time, making latency far more important than throughput.

Second, we are implementing this in PHP and not in C. The only things that are fast in PHP are the library methods that are again implemented in C. So it makes sense from a performance perspective to build on top of PHP cryptographic function primitives, such as [sodium](https://www.php.net/sodium), [hash](https://www.php.net/manual/en/function.hash.php), and [openssl](https://www.php.net/openssl).

My [first attempt](https://github.com/Sjord/feistel-cipher) at creating an identifier encryption scheme was a Feistel cipher on top of SHA256. This was 10 times slower than doing 3DES encryption. AES encryption is even faster. 3DES is normally considered a fairly slow cipher, but the performance difference between native functions and PHP functions is so large, that it is hard to do anything in the time it takes 3DES to encrypt a single block.

## Security

We rely on the pseudo-random permutation of 3DES to hide information. It is not integrity-protected, which means that the attacker can create their own ciphertext and feed it to the application. The application decrypts it into a numeric identifier and looks it up in the database, where it probably doesn't exist. Since we use a 64-bit cipher, this becomes problematic if we have close to 2<sup>64</sup> in the database, or show close to 2<sup>64</sup> ciphertexts to the attacker.

## Against conventional advice

In general, you should not use:

- 3DES cipher, or any other 64-bit block cipher
- ECB mode of operation
- encryption without authentication

I believe using plain 3DES on identifiers is secure, but a cryptographer would roll their eyes if you ever use this in production.

## OpenSSL cipher support

The PHP installations I have access to support 3DES, but this is not guaranteed. 3DES is disabled in OpenSSL by default, and can be enabled with a compile-time option. PHP does not make guarantees about which ciphers are available. It can be checked at runtime with [openssl\_get\_cipher\_methods](https://www.php.net/manual/en/function.openssl-get-cipher-methods.php), but if 3DES is not available it is unlikely that there is another 64-bit cipher that is supported.

## Using AES

AES is better supported, faster, more secure, but results in longer identifiers. Perhaps this is not such a big problem as I thought, because users rarely type in identifiers by hand. To use AES, we would need a method to convert our 64-bit integer into a 128-bit block. A straightforward way to do this is to pad with zeroes and then check on decryption whether one half of the block consists of zeroes. This does expose more information to the attacker: whether the padding is correct or not. Even though we have created a padding oracle, I don't believe it is possible to exploit this in an attack.

## Limitations

Laravel's database layer, Eloquent, has casting support which makes it possible to automatically convert database values to PHP values and vice versa. We could use this to automatically convert an integer to an encrypted integer and back. The problem is, this automatic casting is not supported for the primary key.

Our new method works on the model, when called like `Model::findByHandle($handle)`. However, it doesn't work everywhere else. In Eloquent, you can call `find` on a collection: `Model::where(...)->find($id)`. However, this doesn't work for `findByHandle` since it is not defined for this collection.

## Possibilities

The behavior of this example is not so different than creating a handle column in the database. However, when dynamically encrypting identifiers it is also possible to vary the encryption with additional information. Handles can be different between users, for example. Or they could change every day.

Having different identifiers for different users would make it impossible for users to send links to each other, and would add an additional defense against CSRF: if you want to trick the admin into modifying an account, you have to know *their* correct identifier for that account.

## Conclusion

Encrypting identifiers works well in practice. It is sufficiently fast and secure. Natively supported ciphers are the only realistic choice in PHP, because of performance.
