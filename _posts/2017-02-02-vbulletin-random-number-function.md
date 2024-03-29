---
layout: post
title: "vBulletin random number function"
thumbnail: bingo-wood-240.jpg
date: 2017-03-29
---

VBulletin is a message board web application written in PHP. Before PHP 7 came with [`random_int`](https://www.php.net/manual/en/function.random-int.php) and [`random_bytes`](https://www.php.net/manual/en/function.random-bytes.php) it was pretty hard to create a secure random number, and vBulletin uses a pretty bad way to solve that problem. In this post we look at the code and how it can fail to create a random number that is hard to guess.

## vBulletin's vbrand function

Here is one of the functions that create a "random" number, used in the creation of API keys and CRSF tokens:

    /**
    * vBulletin's own random number generator
    *
    * @param	integer	Minimum desired value
    * @param	integer	Maximum desired value
    * @param	mixed Param is not used.
    */
    function vbrand($min = 0, $max = 0, $seed = -1)
    {
        mt_srand(crc32(microtime()));

        if ($max AND $max <= mt_getrandmax())
        {
            $number = mt_rand($min, $max);
        }
        else
        {
            $number = mt_rand();
        }
        // reseed so any calls outside this function don't get the second number
        mt_srand();

        return $number;
    }

There are a couple of function calls here:

* [`mt_rand`](https://www.php.net/manual/en/function.mt-rand.php) returns a pseudo-random number.
* [`mt_srand`](https://www.php.net/manual/en/function.mt-srand.php) "seeds" the random number generator. This determines which sequence comes out of `mt_rand`. If you know the parameter to `mt_srand`, the output of `mt_rand` is predictable.
* [`microtime`](https://www.php.net/manual/en/function.microtime.php) returns the current time. The returned value has microsecond precision, but not microsecond accuracy. More on that later.
* [`crc32`](https://www.php.net/manual/en/function.crc32.php) returns a 32-bit numeric hash of a string.

## Using time as entropy is insecure

What this does it seed the random number generator with a checksum of the current time and then create a random number. The only input to this function is the current time, so the number totally depends on the time. This means that if we know the exact time, we can recreate the random number. 

With web servers it is easy to know their approximate time, but it is a bit harder to know the precise time. There are many microseconds in a second and `vbrand` is called quite a few times to create a token. This means it is pretty hard to predict a whole token.

Still, if the attacker knows the approximate time he can brute-force this 32-byte token in something like 10<sup>15</sup> tries, where it would take 10<sup>77</sup> tries if the token were completely random. This is still unfeasible to do remotely, but clearly indicates that the security of the token is not what it is supposed to be.

## Completely fails on Cygwin

On some platforms, `microtime()` does not have microsecond accuracy. On Cygwin, for example, it just has millisecond accuracy. This makes the random tokens vBulletin creates a lot less random. Let's print 10 "random" passwords:

    <?php
    include('functions.php');
    for ($i = 0; $i < 10; $i++) {
        echo fetch_random_password(32) . "\n";
    }

This outputs the following:
    
    uuuuuuuuuuuuuuuuuuuuuuuuuuuu8uuu
    uuuuuuuuuuuuuuuuuuuuuuuuuuuu8uuu
    uuuuuuuuuuuuuuuuuuuuuuuuuuuu8uuu
    uuuuuuuuuuuuuuuuuuuuuuuuuuuu8uuu
    uuuuuuuuuuuuuuuuuuuuuuuuuuuu8uuu
    uuuuuuuuuuuuuuuuuuuuuuuuuuuu8uuu
    uuuuuuuuuuuuuuuuuuuuuuuuuuuu8uuu
    uuuuuuuuuuuuuujjjjjjjjjjjjjj6jjj
    jjjjjjjjjjjjjjjjjjjjjj6jjjjjjjjj
    jjjjjjjjjjjjjjjjjjjjjj6jjjjjjjjj

This doesn't look random at all. What happens is that `microtime` returns the same value every time it is called, and this results in the same character every time. That is, until a millisecond elapses and it switches to the next character. The reduced accuracy in `microtime` creates a reduced entropy in the password.

## Conclusion

Don't base your "randomness" on the output of microtime. Use [`random_int`](https://www.php.net/manual/en/function.random-int.php) and [`random_bytes`](https://www.php.net/manual/en/function.random-bytes.php), or [random_compat](https://github.com/paragonie/random_compat) if still on PHP 5.
