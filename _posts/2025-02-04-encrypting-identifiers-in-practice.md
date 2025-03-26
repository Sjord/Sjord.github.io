---
layout: post
title: "Encrypting identifiers in practice"
thumbnail: numbers-480.jpg
date: 2025-04-02
---

- Does not work on collections.
- Possible to make a different mapping for every page.
- Fast.
- [previous post](/2023/08/02/encrypting-identifiers/)

```php
trait EncryptedHandle {
    private static string $key = 'P13hwAfDvR+qLb+3EBD5ro3X';

    public static function findByHandle(string $handle) {
        return static::findOrFail(static::decrypt($handle));
    }

    public function getHandleAttribute() {
        return static::encrypt($this->id);
    }

    private static function encrypt($id) {
        $key = hash_hmac('sha512', __CLASS__, static::$key, true);
        $plaintext = pack('q', $id);
        $ciphertext = openssl_encrypt($plaintext, 'des-ede3', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);
        return bin2hex($ciphertext);
    }

    private static function decrypt($handle) {
        $key = hash_hmac('sha512', __CLASS__, static::$key, true);
        $ciphertext = hex2bin($handle);
        $plaintext = openssl_decrypt($ciphertext, 'des-ede3', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);
        $parts = unpack('qid', $plaintext);
        return $parts['id'];
    }
}
```

## Mechanism

I interpret the identifier as a 64-bit integer and encrypt it as a single block with 3DES, and then convert it to hexadecimal.

## Security



## Using AES

## Against conventional advice

In general, you should not use:

- 3DES cipher, or any other 64-bit block cipher
- ECB mode of operation
- encryption without authentication

## Performance

Cryptographers care about performance, and try to come up with fast algorithms. For cryptographers, this means that an optimized version implemented in a system level language has a high throughput. That is, it can encrypt many megabytes per second. However, the performance requirements for encrypting identifiers are different. First, we encrypt a single block at a time, making latency far more important than throughput.

Second, we are implementing this in PHP and not in C. The only things that are fast in PHP are the library methods that are again implemented in C. So it makes sense from a performance perspective to build on top of PHP cryptographic function primitives, such as [sodium](https://www.php.net/sodium), [hash](https://www.php.net/manual/en/function.hash.php), and [openssl](https://www.php.net/openssl).

My [first attempt](https://github.com/Sjord/feistel-cipher) at creating an identifier encryption scheme was a Feistel cipher on top of SHA256. This was 10 times slower than doing 3DES encryption. AES encryption is even faster. 3DES is normally considered a fairly slow cipher, but the performance difference between native functions and PHP functions is so large, that it is hard to do anything in the time it takes 3DES to encrypt a single block.

## Tweak

If is useful if the encryption is [tweakable](/2023/09/27/tweakable-block-ciphers/): the identifier for customer 123 should be different than the identifier for invoice 123. When we encrypt "123", we also want to pass in what type of object is being encrypted. In the PHP example I HMAC the key with the magic `__CLASS__` variable, which contains the name of the current model.

