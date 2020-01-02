---
layout: post
title: "XSS in username in Sakai"
thumbnail: sakai-xss-480.png
date: 2019-12-04
---

Sakai is educational software, to keep track of classes, students and test marks.

## Introduction

Some applications are riddled with XSS, because they don't have a structural prevention in place. If you have to call `htmlentities` for every output, you are going to forget a couple of places, which directly leads to XSS. A better solution is to have a templating system that escapes content by default. Sakai does have such a templating system, so in most places the user input is correctly escaped. However, I found one place that doesn't correctly escape input.

## XSS in first name

The username or given name of the user is always an interesting place to try XSS payloads, since it is displayed on many places and is often considered "trusted" by the application developers.

Sakai prevents the most straightforward XSS payloads through input sanitation. HTML tags are removed from the user input. So when inputting `<h1>Sjoerd` as the first name, only `Sjoerd` remains. Only matching brackets are removed, so single brackets remain. There are two ways around this:

* Split the payload over multiple fields. The user can set his first name and last name, which are often displayed next to each other. By putting `<svg` in the first name and `onload=alert(1)>` in the last name, the input sanitation does not see a HTML tag in any of the fields, but the result is a complete tag: `<svg onload=alert(1)>`.
* Insert a partial HTML tag. The tag doesn't have to be closed, since there will be a `>` already on the page somewhere. If the first name is displayed within a `<div>` element, and we inject `<svg onload=alert(1) `, the closing element of the div closes our tag: `<div><svg onload=alert(1) </div>`.

In Sakai, both options work. In the Sakai chat room the first and last name are displayed next to each other. So to trigger XSS, modify the first name of the user to `<img src=a onerror="alert(1)"` or something similar. Then open the chat room. A popup appears, indicating that the XSS payload works.
The page source contains this HTML:

    <span class='chatNameDate'><span class='chatName'><img src=a onerror="alert(1)" Langkemper</span>

<img src="/images/sakai-change-first-name.png" style="width: 100%">
<img src="/images/sakai-xss.png" style="width: 100%">


## Abuse of the template system

The source of the XSS is [this line](https://github.com/sakaiproject/sakai/blob/9ecf2d18a5fde4359689ec85ac6abf0b1fa1347e/chat/chat-tool/tool/src/webapp/jsp/roomMonitor.jspf#L45) in `roomMonitor.jspf`:

    <c:out value="<span class='chatNameDate'><span class='chatName'>${message.owner}</span>" escapeXml="false" />

So Sakai is using a generic template system, which automatically encodes output. However, in this case they explicitly disabled it using `escapeXml="false"`, in order to output HTML, which is something the template system is supposed to handle. The solution is to use the template system correctly, so that it escapes the variable.

## Conclusion

Even web applications that use template systems that escape all output by default can be vulnerable to XSS. Developers have to use the template system correctly to profit from the protection it offers.

I reported this issue to Sakai and they quickly resolved it.

## Read more

Sakai issues:

* [SAK-41763 - Escaping some outputs in the chat - #6928](https://github.com/sakaiproject/sakai/pull/6928)
* [SAK-41763: XSS in chat user name - 12.x - #6971](https://github.com/sakaiproject/sakai/pull/6971)

Other XSS posts:

* [Reflected XSS in Yclas](/2019/10/09/reflected-xss-in-yclas/)
* [XSS in Kayako helpdesk software](/2016/08/29/kayako-xss/)
* [XSS in user-agent header in Bolt CMS](/2016/05/02/xss-in-useragent-header-in-bolt-cms/)
* [Circumventing XSS filters](/2016/01/29/circumventing-xss-filters/)