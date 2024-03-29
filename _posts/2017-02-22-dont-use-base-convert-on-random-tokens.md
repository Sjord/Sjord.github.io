---
layout: post
title: "Don't use base_convert on random tokens"
thumbnail: overflow-240.jpg
date: 2017-03-15
---

The PHP function `base_convert` can convert numbers to a different numeric system. This can be used for example to convert a number to a sequence of letters and numbers. However, because it has limited precision it is not suitable for random tokens such as those used for session tokens or CSRF tokens.

Consider the following code from [FOSOAuthServerBundle](https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/blob/master/Util/Random.php):

    class Random
    {
        public static function generateToken()
        {
            $bytes = false;
            if (function_exists('openssl_random_pseudo_bytes') && 0 !== stripos(PHP_OS, 'win')) {
                $bytes = openssl_random_pseudo_bytes(32, $strong);
                if (true !== $strong) {
                    $bytes = false;
                }
            }
            // let's just hope we got a good seed
            if (false === $bytes) {
                $bytes = hash('sha256', uniqid(mt_rand(), true), true);
            }
            return base_convert(bin2hex($bytes), 16, 36);
        }
    }

The variable `$bytes` will contain some more or less random data. This is binary data, which we want to fall in the ASCII range so that we can use it in cookies and query strings. The code uses the combination of `bin2hex` and `base_convert` for this. The function `bin2hex` convert the binary to hexadecimal, and the function `base_convert` convert it to base 36. Base 36 consists of letters and numbers, so this should give us a compact token.

Unfortunately, `base_convert` does not perform its job correctly. Note what happens when we convert a hash to base 36 and back using `base_convert`:

    echo base_convert("adc83b19e793491b1c6ea0fd8b46cd9f32e592fc", 16, 36);
    // kaseilp6gls8scsgcg08w48ow44wkos
    echo base_convert("kaseilp6gls8scsgcg08w48ow44wkos", 36, 16);
    // adc83b19e7934800000000000000000000000000

Converting back and forth replaced a large part of our hash with zeroes. This is because of how `base_convert` works. It first converts the hexadecimal number to a 64-bits float number, and then convert that number to base 36. Because the float can contain just 64 bits, the resulting base 36 token contains at most 64 bits or 8 bytes of data.

The [PHP manual warns against this](https://www.php.net/base_convert):

> Warning: base\_convert() may lose precision on large numbers due to properties related to the internal "double" or "float" type used. Please see the Floating point numbers section in the manual for more specific information and limitations.

Don't use `base_convert` on your secure tokens.
