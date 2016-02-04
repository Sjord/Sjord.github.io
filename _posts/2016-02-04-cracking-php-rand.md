---
layout: post
title: "Cracking PHP rand()"
thumbnail: roulette-240.jpg
date: 2016-02-11
---

Webapps occasionaly need to create tokens that are hard to guess. For example for session tokens or CSRF tokens, or in forgot password functionality where you get a token mailed to reset your password. These tokens should be cryptographically secure, but are often made by calling `rand()` multiple times and transforming the output to a string. This post will explore how hard it is to predict a token made with `rand()`.

## How rand works

In PHP, the function [`rand()`](https://secure.php.net/rand) creates pseudorandom numbers. The initial state of the random number generator (the seed) is set with [`srand`](https://secure.php.net/srand). If you don't call `srand` yourself, PHP seeds the random number generator with some hard to guess number when you call `rand`. The seed passed to `srand` totally determines the string of numbers that `rand` will generate.

The random number generator keeps a state that is initially set by `srand` and then changed every time you call `rand`. This state is specific to the process, so two processes typically return different numbers for `rand`.

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

As we said the random number series is totally defined by the seed, so we can simply try every possible number as argument for `srand` to get the random number generator in the right state. Note that this will only work if the server process is fresh. If the server process has already seen a lot of `rand` calls, we need to do the same amount in our cracking program to get the same state.

If we got a token from a fresh process, the following PHP script can be used to crack it:

    for ($i = 0; $i < PHP_INT_MAX; $i++) {
        srand($i);
        if (Token::gen(10) == "2118Jx9w3e") {
            die("Found: $i \n");
        }
    }

To search the 4294967295 possible arguments to `srand`, this will take approximately 12 hours. However, since PHP just calls the glibc `rand` function, we can reimplement the PHP code as C and speed things up.


## State cracking

On some servers, an attacker can get a new process by opening a lot of connections. If all web server handlers are busy, the server will spawn some new processes to handle incoming connections. These new processes then will have a fresh state and can be used to request a range of random strings. Since we are interested in multiple strings of one process, we need to do requests to the same process every time. This can be done by using a keep alive connection, where you can do multiple requests over one TCP connection.
