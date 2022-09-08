---
layout: post
title: "Circumventing authentication of a webshell"
thumbnail: transparent-padlock-240.jpg
date: 2016-02-04
---

When a website is hacked, the attacker often leaves a backdoor or webshell to be able to easily access the website in the future. These are often obfuscated to avoid detection, and need authentication so only the attacker can gain access to the site. In this post I am going to deobfuscate a webshell and show how the authentication can be bypassed when you have the source code but not the password.

See for example this webshell, that was left on a hacked site:

    <?php
    $auth_pass = "64a113a4ccc22cffb9d2f75b8c19e333";
    $color = "#df5";
    $default_action = 'FilesMan';
    $default_use_ajax = true;
    $default_charset = 'Windows-1251';
    preg_replace("/.*/e","\x65\x76\x61…\x29\x3B",".");
    ?>

## Deobfuscating the webshell

The `preg_replace` has three arguments, the regex, the replacement and the subject. Because the regex has the `e` modifier, it will evaluate anything in the replacement as PHP code. This is thus similar to the following code:

    preg_replace("/.*/", eval("\x65\x76\x61…\x29\x3B"), ".");

Now we know that the second parameter is evaluated, but it still does not look like PHP code. That is because it is hex encoded. A string in double quotes can contain [some escape sequences](https://secure.php.net/manual/en/language.types.string.php#language.types.string.syntax.double) that are interpreted by PHP, and one of them is `\x` to put a character in the string using hexadecimal notation. For example, `\x65` would be an `e` because it says so in the [ASCII table](https://man7.org/linux/man-pages/man7/ascii.7.html). Manually converting this string would be a little bit of work, so we let PHP do it:

    echo "\x65\x76\x61…\x29\x3B";

Result:

    eval(gzinflate(base64_decode('5b19f…Z9P8C')));

Here `base64_decode` converts the text to binary, `gzinflate` uncompresses the binary and `eval` executes it as PHP code. To see what PHP code is run, we replace the `eval` by an `echo`:


    echo gzinflate(base64_decode('5b19f…Z9P8C'));

This finally yields our deobfuscated webshell. It turns out the be a webshell called "WSO", with [source available on GitHub](https://github.com/tennc/webshell/blob/master/php/wso/wso2.php).

These manual steps to deobfuscate a piece of PHP code have been automated by Sucuri in their [PHP decoder](http://ddecode.com/phpdecoder/?results=69b2b644106926dfd107f57afdaaeec3), which performs all of the above steps automatically.

## Bypassing authentication

The `$auth_pass` in the original code already suggested there would be authentication on the webshell. The format of `$auth_pass`, 32 hexadecimal characters, suggest that it is a MD5 of the plaintext password. Now that we have the source of the webshell, we can confirm that:

    if(!empty($auth_pass)) {
        if(isset($_POST['pass']) && (md5($_POST['pass']) == $auth_pass))
            WSOsetcookie(md5($_SERVER['HTTP_HOST']), $auth_pass);

        if (!isset($_COOKIE[md5($_SERVER['HTTP_HOST'])]) || ($_COOKIE[md5($_SERVER['HTTP_HOST'])] != $auth_pass))
            wsoLogin();
    }

It does an MD5 over the posted `pass` parameter, and checks that against `$auth_pass`. Plain MD5s are usually not a very secure way to store passwords. First of all, MD5 is very fast and you can compute [billions of hashes per second](https://blog.codinghorror.com/speed-hashing/) to try to brute force the password. Secondly, the MD5 sum for many weak passwords is already on the internet and can be found by a quick [Google search](https://www.google.nl/search?q=adf431a1517b2331c343a26f41fecaca). However, our hacker has chosen a pretty good password, and I was unable to crack it. But there is another way to gain access to the webshell now that we have the source code.

As you can see in the code it sets a specific cookie when you get the password right. It checks the cookie and if you have it wrong it calls `wsoLogin` to show you a login page and exit the script. Otherwise it continues with the webshell code. The cookie is supposed to have the MD5 of the hostname as key, and the `$auth_pass` contents as content. Luckily, we know both these values and can create our own cookie to gain access to the webshell.

First, calculate the MD5 of the hostname.
The `-n` here is to prevent a newline to end up in the MD5. 

    $ echo -n example.com | md5sum
    5ababd603b22780302dd8d83498e5172 -

Then, set a cookie with key `5ababd603b22780302dd8d83498e5172 ` and value `64a113a4ccc22cffb9d2f75b8c19e333`, the contents of `$auth_pass`. I used the Chrome plugin [EditThisCookie](https://chrome.google.com/webstore/detail/editthiscookie/fngmhnnpilhplaeedifhccceomclgfbg) for this. Now, you are logged in in the webshell without knowing the password.

## Update

I also cracked some passwords in the meantime:

| Hash                             | Password      |
|----------------------------------|---------------|
| 64a113a4ccc22cffb9d2f75b8c19e333 | cmonqwe123#@! |
| 9e4bf26d87b7e8b6b66b0a2305f67184 | lex1312       |
