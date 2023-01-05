---
layout: post
title: "XSS in ViewVC"
thumbnail: bug-in-viewvc-480.png
date: 2023-02-15
---

I found a cross-site scripting (XSS) vulnerability in ViewVC, a source code repository web frontend.

<!-- image source: https://pixabay.com/nl/vectors/insecten-bugs-tekenfilm-mier-bij-6809694/, https://github.com/viewvc/viewvc/blob/master/notes/logo/viewvc-logo.svg -->

ViewVC is a Python webapp to browse code repositories. It supports CVS and SVN, which people used when git didn't exist yet. I found a simple HTML injection vulnerability in it. When browsing a source code repository, it shows filenames of files. If the filename contains HTML tags, these are not encoded correctly. The browser interprets these as HTML, and this leads to XSS.

<img src="/images/viewvc-xss.png" style="width: 100%" alt="A popup shows that a JavaScript payload is executed within ViewVC">

## Getting to the source

I actually started searching for vulnerabilities in Tuleap, which is a code management suite with a repository browser and issue tracker. I found a XSS vulnerability in its page that shows Subversion repositories. However, the source code showed that they forwarded requests to ViewVC. So I tested whether I could reproduce the bug in ViewVC, and I could.

## XSS payloads

Here, I used the following payload as filename:

    <h1 onmouseover=alert`XSS`>XSS

The most obvious XSS payload is `<script>alert(1)</script>`. Sometimes this works a little too well, and keeps showing popups on every page you visit. This is pretty annoying when you want to test more of the application, so now I usually use a payload that needs some user interaction to trigger.

Using tags that change the layout of the page also give a visual indicator when HTML tags are interpreted, but the JavaScript is stripped from the input. I am also a big proponent of using the `<marquee>` tag to test for XSS. This causes text to move, which is easy to detect. At the same time, it is rare for applications to use this tag, so searching for `<marquee>` in Burp or the page source will give only XSS payloads as result.The movement does not show well in screenshots, though.

<marquee>Example &lt;marquee&gt; tag</marquee>

## Automatic encoding in template engines

ViewVC uses a template engine, [EZT](https://github.com/gstein/ezt). EZT does support automatic HTML encoding of all variables. However, you have to explicitly enable it, and ViewVC doesn't.

By default, variables are not encoded at all. In [lib/viewvc.py](https://github.com/viewvc/viewvc/blob/master/lib/viewvc.py#L974):

    return ezt.Template(cfg.path(tname))

To HTML-encode variables by default, pass the `base_format` parameter:

    return ezt.Template(cfg.path(tname), base_format=ezt.FORMAT_HTML)

If you don't have automatic encoding, you have to encode each parameter manually. This is hard to get right. It is easy to forget encoding a parameter. This was also underscored in my XSS finding. After the developers had encoded the path name, someone on Twitter pointed out that there was another variable (copied from path) that should also be encoded.

## Conclusion

So, CVE-2023-22456 is a XSS vulnerability in ViewVC, where the input is the name of a file in the repository. CVE-2023-22464 was discovered immediately after that by someone else.

This is my first CVE actually, that I know of. I found many vulnerabilities, but didn't bother with CVEs, or didn't get permission from the client I worked for to share the vulnerability.

If your template engine does not automatically encode variables, you are going to have a bad time. It's hard to be certain that you encoded every single variable, whereas it's secure by default when the template engine does it.

## Read more

* [XSS in changed paths when reviewing revision · Issue #311 · viewvc/viewvc](https://github.com/viewvc/viewvc/issues/311)
* [XSS vulnerability in revision view changed paths · Advisory · viewvc/viewvc](https://github.com/viewvc/viewvc/security/advisories/GHSA-j4mx-f97j-gc5g)
