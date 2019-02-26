---
layout: post
title: "Command injection on CommonAccord"
thumbnail: commonaccord-480.png
date: 2019-07-03
---

I found and fixed a simple command injection vulnerability in the CommonAccord web site.

## Finding a vulnerability

I was looking for open source web applications to hack, using Google BigQuery on the GitHub dataset. Initially I was looking for XSS by searching for `<?=$_`. This resulted in the source code for the site CommonAccord. The payload I was searching for didn't immediately result in XSS, but I found some other, more interesting things.

The web site has several actions. I wasn't clear how they worked, so I grepped on one of them, which gave the following result:

    vendor/cmacc-app/view/showme1.php
    21:$document = `perl $lib_path/parser-showme1.pl $path/$dir`;

Here, $dir is based on user input. The backticks indicate that this line is executed in a command shell. This looks vulnerable to command injection. By appending a semicolon a command can be executed on the server. For example, `;ls -l /` gives the directory listing of the root directory.

<img src="/images/commonaccord-ls.png" width="100%">

## Fixing the vulnerability

To solve this, I initially wanted to pass the `$path/$dir` securely as an argument to Perl. To do this, I searched for an API that takes an array of arguments, like this:

    $document = exec_array(["perl", "$lib_path/parser-showme1.pl", "$path/$dir"]);

This way, the third argument is always passed as the third argument to the program, no matter what it contains. Additionally, the shell doesn't need to be involved, making it impossible to inject `&&` or something like that in the command. Java has [an API like this](https://docs.oracle.com/javase/7/docs/api/java/lang/Runtime.html#exec(java.lang.String[])), but unfortunately PHP doesn't.

In the end I decided to perform input sanitation to solve this problem. This is usually a defense-in-depth measure and not the correct solution in itself, but here it provides a convenient way to solve multiple issues at one spot. I [added](https://github.com/CommonAccord/Cmacc-Org/pull/22) the following line:

     $dir = preg_replace('~[^\w/.,_-]~u', '_', $dir);

This replaces everything except allowed characters by an underscore. 

So the regex is `~[^\w/.,_-]~u`:

 * `~` - start of regex
 * `[` - start of bracket expression. Match any letter between brackets.
 * `^` - invert the match, so that all except the following match.
 * `\w` - all word characters, A-Z, a-z, 0-9
 * `/.,_-`, literal characters. The dash - has to be at the end.
 * `]` - end of bracket expression.
 * `~` - end of regex
 * `u` - interpret strings as unicode, so that é also matches.

## Conclusion

I helped the author of this web site by implementing basic input validation that protects against command injection. The optimal solution for this problem, an execution API that allows separate arguments, was not available in PHP.