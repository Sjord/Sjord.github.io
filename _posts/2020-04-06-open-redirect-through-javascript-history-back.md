---
layout: post
title: "Abusing javascript:history.back() as an open redirect"
thumbnail: rearview-dinosaur-480.jpg
date: 2020-04-08
---

Using `javascript:history.back()` on a page may introduce a kind of open redirect. The previous page may not belong to the application that contains the link, so a seemingly trusted link now points to another (untrusted) page. This may be usable in phishing attacks.

<!-- photo source: https://pixabay.com/photos/dinosaur-mirror-wing-mirror-behind-1564323/ -->

## Open redirects and phishing

An open redirect is a webpage that redirects to another URL, and the destination can be set to anything. This can be abused in phishing attacks: an URL with a trusted domain can be presented, even though the URL actually redirects to an attacker page.

Consider the following example. Perhaps you trust `example.com`, so if you click this link you probably end up on a trusted site.

    https://trusted.example.com/Logout?returnUrl=//www.sjoerdlangkemper.nl/

However, since `example.com` has an open redirect, you end up on some hacker's site instead of on `example.com`. If the hacker has set up a phishing page that looks like `example.com`, he may trick you into entering credentials.

The vulnerability originates from misplaced trust: you think you are on a trusted domain since you checked the URL, but through trickery the trusted page has been replaced by a phishing page. A glance at the URL before entering credentials would prevent that, but not everybody checks the URL all the time, making the phishing attack more likely to succeed.

## Abusing back functionality

Using JavaScript, it is possible to navigate to the previous page:

    history.back()

This is functionally the same as clicking the back button in the browser: it navigates to the previous page. However, that previous page may not belong to the same domain, and the user may expect links on domains to be trusted.

A phishing attack would work as follows:

1. The attacker links to his own page.
2. The attacker's page forwards to a page on a trusted domain with a link that triggers `history.back()`.
3. The user clicks the link.

The user is now on the attacker's site again, by clicking on a link in an application they trust.

Now, this attack scenario is a little far fetched, since the user needs to visit the attacker's site and click a link. It could succeed in tricking the user, depending on when they check the URL in the browser bar.

## Demo

The National Bank of Egypt is at the domain https://nbe.com.eg/. The [404 error page](https://nbe.com.eg/404) on that domain contains a link that triggers `history.back()`. I made a little [proof of concept page](https://github.com/Sjord/Sjord.github.io/blob/master/_demo/backphish.html) that tries to exploit this.

1. Visit the [PoC page](//demo.sjoerdlangkemper.nl/backphish.html). It forwards you to an error page on nbe.com.eg. 
2. Check the domain. It looks legitimately to be the Bank of Egypt.
3. Click the "Go Back" link. Now you end up on my phishing page.

By clicking a link on a trusted domain, you ended up on a phishing page.

## Conclusion

I am still not certain how much this would fool people, that would not just be fooled by a normal phishing page. Also, our demo was a little contrived in that it clearly shows an error message and a "Go Back" link. However, I guess some users may expect the "Go Back" link to go to the legitimate site.

So using `javascript:history.back()` introduces a small risk that can be exploited by phishing attacks. Just use explicit links to pages instead of relying on browser history.
