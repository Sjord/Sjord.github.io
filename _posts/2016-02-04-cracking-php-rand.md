---
layout: post
title: "Cracking PHP rand()"
thumbnail: roulette-240.jpg
date: 2016-02-11
---

Webapps occasionaly need to create tokens that are hard to guess. For example for session tokens or CSRF tokens, or in forgot password functionality where you get a token mailed to reset your password. These tokens should be cryptographically secure, but are often made by calling `rand()` multiple times and transforming the output to a string. This post will explore how hard it is to predict a token made with `rand()`.

## How rand works

In PHP, the function [`rand()`](https://secure.php.net/rand) creates pseudorandom numbers. The initial state of the random number generator (the seed) is set with [`srand`](https://secure.php.net/srand). If you don't call `srand` yourself, PHP seeds the random number generator with some hard to guess number when you call `rand`. The seed passed to `srand` totally determines the string of numbers that `rand` will generate.

The random number generator keeps a state that is initially set by `srand` and then changed every time you call `rand`. This state is specific to the process, so two processes typically return different numbers for `rand`. On Windows this state has a size of 32 bits and can be directly set using `srand`. On Linux the state is 1024 bits.

## Our example program

Our example program is [EZChatter](https://github.com/ChiVincent/EZChatter), a small toy program put together in a day. It does use CSRF tokens, but does not a very good job at creating them securily:

    public static function gen($len = 5)
    {
        $token = '';
        while($len--){
            $choose = rand(0, 2);
            if ($choose === 0)
                $token .= chr(rand(ord('A'), ord('Z')));
            else if($choose === 1)
                $token .= chr(rand(ord('a'), ord('z')));
            else
                $token .= chr(rand(ord('0'), ord('9')));
        }
        return $token;
    }

As you can see it first calls `rand` to determine whether to use an uppercase letter, lowercase letter or number, and then again to pick a specific letter or number.

Every time we request the index.php page we get a new CSRF token, so we can request as many as we want. Our job is to predict tokens that have been handed out to other users, so we can do a CSRF attack on them.

## Seed cracking

As we said the random number series is totally defined by the seed, so we can simply try every possible number as argument for `srand` to get the random number generator in the right state. Note that on Linux this will only work if the server process is fresh. If the server process has already seen a lot of `rand` calls, we need to do the same amount in our cracking program to get the same state. On Windows, the state of the random number generator is the same as the argument to `srand`, so you don't need a fresh process.

If we got a token from a fresh process, the following PHP script can be used to crack it:

    for ($i = 0; $i < PHP_INT_MAX; $i++) {
        srand($i);
        if (Token::gen(10) == "2118Jx9w3e") {
            die("Found: $i \n");
        }
    }

To search the 4294967295 possible arguments to `srand`, this will take approximately 12 hours. However, since PHP just calls the glibc `rand` function, we can reimplement the PHP code as C and speed things up. I have made two versions, one that calls the [glibc rand](https://github.com/Sjord/crack-ezchatter-token/blob/master/crackseed.c) and one that mimics the [Windows rand](https://github.com/Sjord/crack-ezchatter-token/blob/master/wincrackstate.c). It is basically the PHP code from `token.php`, a copy paste of some macro's from PHP's `ext/standard/rand.c`, and a loop to go through every possible seed. This will take about 10 minutes for the Windows version and a couple of hours for the Linux version.

Once completed, you have the random number generator in the same state and you can keep generating the same tokens as on the server. By comparing your own generated tokens with the tokens the server returns you know which tokens have been handed out to other users, and you can start your attack.

## State cracking on Linux

On Windows, cracking the argument to `srand` and cracking the state of the random number generator turn out to be the same thing, but on Linux they are different. The glibc `rand()` keeps a series of numbers, and determines the next state like this:

    state[i] = state[i-3] + state[i-31]
    return state[i] >> 1

So every output is approximately the summed output from 3 and 31 calls ago. Consider the following series of tokens:

* 6ZF5kNgonV
* 9h3byovpGR
* gGt0A94U92

Now, the next rand will be determining whether it will be an uppercase letter, lowercase letter or number. This is determined by the outcomes of `rand` 3 and 31 calls ago. That's the last 9 in `gGt0A94U92` and the y in `9h3byovpGR`. So we expect the next output of `rand(0, 2)` to be approximately ⌊10/10 + 25/26 × 3⌋ = 2 mod 3, so that means we get a number. Let's see if we can predict that number. The next calls to `rand` that determines the number is determined by the `rand` from 3 calls ago, a number, and the rand of 31 calls ago, a lowercase letter. The number will thus be between ⌊2/3 + 1/3 × 10⌋ = 0 mod 10 and ⌊3/3 + 2/3 × 10⌋ = 6 mod 10. We thus expect the number to be between 0 and 6. It turns out to be 4:

* 43J2d2ew31

As you can see we can not accurately predict the next token using this method, but it is also clear that the we can predict so much about it that you can hardly call it random. It may also be possible to crack the whole state of the glibc random number generator given enough tokens, although I have not tried this.

## Conclusion

Tokens should be created using a cryptographically secure random number generator. If they are made with `rand`, the state of the random number generator can be cracked trivially in many cases, and tokens can be predicted. On Linux it is a little bit harder to predict tokens, but this does still not give secure tokens. The random number generator on Windows is particularly easy to exploit, since any state of the random number generator can be cracked within minutes.
