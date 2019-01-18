---
layout: post
title: "Command injection on CommonAccord"
thumbnail: commonaccord-480.png
date: 2019-07-03
---


I was looking for open source web applications to hack, using Google BigQuery on the GitHub dataset. Initially I was looking for XSS by searching for `<?=$_`. This resulted in the source code for the site CommonAccord. The payload I was searching for didn't immediately result in XSS, but I found some other, more interesting things.

The web site has several actions. I wasn't clear how they worked, so I grepped on one of them, which gave the following result;

    vendor/cmacc-app/view/showme1.php
    21:$document = `perl $lib_path/parser-showme1.pl $path/$dir`;

Here, $dir is based on user input. This looks vulnerable to command injection.

<img src="/images/commonaccord-ls.png" width="680">