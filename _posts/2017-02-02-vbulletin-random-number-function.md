---
layout: post
title: "vBulletin random number function"
thumbnail: bingo-wood-240.jpg
date: 2017-03-29
---

VBulletin is a web application where users can log in and post messages on message boards, written in PHP. Before PHP 7 came with [`random_int`](http://php.net/manual/en/function.random-int.php) and [`random_bytes`](http://php.net/manual/en/function.random-bytes.php) it was pretty hard to create a secure random number, and vBulletin uses a pretty bad way to solve that problem. In this post we look at the code and how it can fail to create a random number that is hard to guess.

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

* [`mt_rand`](http://php.net/manual/en/function.mt-rand.php) returns a pseudo-random number.
* [`mt_srand`](http://php.net/manual/en/function.mt-srand.php) "seeds" the random number generator. This determines which sequence comes out of `mt_rand`. If you know the parameter to `mt_srand`, the output of `mt_rand` is predictable.
* [`microtime`](http://php.net/manual/en/function.microtime.php) returns the current time. The returned value has microsecond precision, but not microsecond accuracy. More on that later.
* [`crc32`](http://php.net/manual/en/function.crc32.php) returns a 32-bit numeric hash of a string.



On Cygwin:

    <?php
    include('functions.php');
    for ($i = 0; $i < 100; $i++) {
        echo fetch_random_password(32) . "\n";
    }
    
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
    jjjjjjjjjjjjjjjjjjjjjj6jjjjjjjjj
    jjjjjjjjjjjjjjjjjjjjjj6jjjjjjjjj
    jjjjjjjjjjjjjjjjjjjjjj6jjjjjjjjj
