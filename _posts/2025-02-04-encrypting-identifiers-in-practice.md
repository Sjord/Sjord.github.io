---
layout: post
title: "Encrypting identifiers in practice"
thumbnail: numbers-480.jpg
date: 2025-02-25
---

- Does not work on collections.
- Possible to make a different mapping for every page.
- Fast.

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