---
layout: post
title: "PHP type confusion on password comparison"
thumbnail: confusion-240.jpg
date: 2018-07-04
---

When comparing two values, PHP guesses at their type and performs the comparison accordingly. This can be misused by formatting a string as a number, so that comparison is done more loosely.

## Type confusion

PHP is a loosely typed, dynamic language. PHP will sometimes silently convert variables to another type, which is called type juggling. This happens for example when adding a string to an integer:

    php > var_dump(3 + "12");
    int(15);

This also happens when using the normal equals operator, `==`. This operator first guesses at the type of both operands, and then compares them. This even happens when both operands are strings.

    php > var_dump("0" == "-0");
    bool(true)

Both values resemble numbers, are converted to int and then compared.

## Scientific notation

To write very large or very small numbers easily, there is the scientific notation. This adds a power of 10 as multiplier, which in effect shifts the decimal point left or right. For example, a large number may be written as

6.022 &times; 10<sup>23</sup>

In PHP, something similar exists which uses an e:

    php > var_dump(6.022e23);
    float(6.022E+23)

## Combining the two

Now we know that PHP tries to convert strings to number before comparing, and interprets an `e` in a number as the exponent, the stage is set for the type confusion vulnerability when comparing passwords.

This vulnerability arises when a plain hash of a password is checked against the password hash in the database using the equals operator. This example comes from Monstra CMS:

    if (trim($user['password']) == Security::encryptPassword(Request::post('password'))) {
        // Logged in
    }

    function encryptPassword($password)
    {
        return md5(md5(trim($password) . MONSTRA_PASSWORD_SALT));
    }

Now, consider what happens if our password hashes to this MD5 sum:

    0e770334890835629043478642775106

To PHP, this looks like the following number:

0 &times; 10<sup>770334890835629043478642775106</sup>

which equals 0. This means that you can log in with any *other* password that has a similar hash and also equals the number 0.

## Finding a collision

Let's find two hashes that both have the numerical value 0.

    for ($i = 0; $i < 100000000000000; $i++) {
        $hash = encryptPassword($i);
        if ($hash == "0") {
            echo "$i $hash\n";
        }
    }

After about five minutes it has output two values:

    228453663 0e770334890835629043478642775106
    576315426 0e561311390263821655340886129044

We can test it by setting our password to "228453663" and then trying to log in with "576315426". This works, indicating that the hashes were compared as numbers.

## Probability

For our hash to be numerical zero, we need one or more zeroes, then an `e`, then all numbers. Assuming that an MD5 hash is indistinguishable from random, what would be the probability this happens?

First, let's calculate the case with exactly one zero. So the hash starts with `0e`. Since each position can have 16 possibilities, there is a one in sixteen chance that a position contains a specific character. For two characters, this is one over sixteen times sixteen, or <sup>1</sup>&#8725;<sub>256</sub>.

The remaining 30 characters need to be digits. A position has a ten in sixteen chance of being a digit. This means that the 30 characters have a (<sup>10</sup>&#8725;<sub>16</sub>)<sup>30</sup> chance of being all digits.

(<sup>1</sup>&#8725;<sub>16</sub>)<sup>2</sup> &times; (<sup>10</sup>&#8725;<sub>16</sub>)<sup>30</sup> ≈ <sup>1</sup>&#8725;<sub>340,000,000</sub>

So if we assume a random password, there is a one in several hundred million chance that it compares as numerical zero.

## Conclusion

Loosely comparing a MD5 is definitely a vulnerability, but it still does not allow an attacker to walk through the door. An attacker would need thundreds of millions of attempts before he can break security with this vulnerability.

## Read more

* [Writing Exploits For Exotic Bug Classes: PHP Type Juggling](http://turbochaos.blogspot.com/2013/08/exploiting-exotic-bugs-php-type-juggling.html)
* [PHP Magic Tricks: Type Juggling](https://www.owasp.org/images/6/6b/PHPMagicTricks-TypeJuggling.pdf)
