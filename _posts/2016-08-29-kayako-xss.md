---
layout: post
title: "XSS in Kayako helpdesk software"
thumbnail: broken-phone-box-240.jpg
date: 2016-08-29
---

In the Kayako helpdesk software, customers can submit tickets which staff can then view and answer. In this feature there is  a XSS vulnerability in the ticket title. This post describes how I found that vulnerability.

## Checking escape functionality

First, I created a new ticket with some HTML tags in each field:

![<h1>foobar</h1> in all the fields](/images/kayako-xss-foobar-ticket.png)

I use a easily recognizable word in each field ("foobar") so that I can later on search in the page source and easily find how strings are escaped. I use `<h1>` instead of `<script>` because this does not break things just yet and is passed through any filters more often.

Then I open the ticket in the staff view. As you can see there is no large text indicative of an H1 tag, and the HTML seems correctly escaped. One interesting thing is that our H1 apparently capitalized the message content, but we will not go into that.

![<h1>foobar</h1> in all the fields](/images/kayako-xss-foobar-ticket-staff.png)

Let's look at the source code. By searching for "foobar" in the source code of the page, we find one interesting spot where the HTML is not correctly encoded:

![The call to SetHeaderTitle is not escaped](/images/kayako-xss-foobar-ticket-source.png)

As you can see the `<h1>` is not encoded at all. This is not necessarily a vulnerability, but it is interesting enough to try some things.

First, lets try `");}); alert("XSS")` as subject line. This breaks out of the quotes and parenthesis and then runs the `alert`, which should pop up a dialog box. However, when we view the ticket as staff we don't get a popup. The source code shows that all quotes have been escaped with backslashes.

![Quotes have been escaped with backslashes](/images/kayako-xss-foobar-ticket-quotes.png)

No luck. Besides breaking out of the quotes, we can also try to break out of the script tag by putting `</script>` in the title. However, when we try that the whole tag gets removed.

## Looking at the code

Time to look at the code to see what escaping is exactly performed:

    $_documentTitle = addslashes(StripScriptTags($this->_documentTitle));
    echo 'SetHeaderTitle("' . $_documentTitle . '");';

The call to [`addslashes`](http://php.net/manual/en/function.addslashes.php) is the reason why our quotes are escaped, and the function `StripScriptTags` removes our `</script>` tag:

    function StripScriptTags($_htmlCode)
    {
        // Strips "empty" script tags (e.g. <script type="text/javascript" src="foo"/>)
        $_htmlCode = preg_replace('@<script[^<]*?/>@si', '', $_htmlCode);

        // Strips "full" script tags (e.g. <script type="text/javascript">foo</script>)
        $_htmlCode = preg_replace('@<script(?:.*?)>.*?</script(?:[\s]*?)>@si', '', $_htmlCode);

        // Strips just closing tags
        $_htmlCode = preg_replace('@</script(?:[\s]*?)>@si', '', $_htmlCode);

        return $_htmlCode;
    }


The last `preg_replace` call removes our script tag. Let's look at what this regex means exactly:

* The `@` is a delimeter. The real regex is in between the two `@`.
* The literal `</script` is matched.
* The `(?:...)` is a non-capturing group, which is not important for this regex.
* The `[\s]*?` matches zero or more white space characters.
* Followed by a literal `>`.

So `</script`, possible whitespace, `>` is removed. 

## Performing the attack

As we have seen `</script>` tags are removed, as long as they contain nothing else but whitespace. This means that we can bypass this sanitization code by putting something that is not whitespace in the tag:

    </script a><iframe src=javascript:alert(`XSS`) />

Note that we have added an `a` to the closing script tag, which prevents `StripScriptTags` from deleting it. We also use backticks instead of quotes in our JavaScript, because quotes would be escaped by `addslashes`. Success at last:

![Popup dialog showing "XSS"](/images/kayako-xss-foobar-ticket-xss.png)

## Conclusion

This vulnerability makes it possible for an anonymous user to inject scripts that are executed by staff. It was easily found using a combination of hands-on testing and code review: first test to find an interesting location where input is not correctly escaped, then read the code to learn what escaping is done exactly.

## Timeline

* 28 Feb 2016, Sjoerd → Kayako: there is XSS in SetHeaderTitle.
* 29 Feb 2016, Kayako → Sjoerd: bug has been filed: SWIFT-4917.
* 6 Mar 2016, Sjoerd → Kayako: the bug is public but should be private.
* 7 Mar 2016, Kayako → Sjoerd: bug marked as private.
* 10 Jun 2016, Sjoerd → Kayako: could you look at this bug?
* 23 Jun 2016, Sjoerd → Kayako: any update? would like to publish in July.
* 23 Jun 2016, Kayako → Sjoerd: please wait until issue is fixed in next release.

## Version 

For this article I used Kayako 4.71.0. The newest version at the time of writing is 4.74.2.
