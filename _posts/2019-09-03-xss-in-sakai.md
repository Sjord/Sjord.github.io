---
layout: post
title: "XSS in username in Sakai"
thumbnail: sakai-xss-480.png
date: 2019-12-04
---

Under Home > Account, I modify my first name to be `<img src=a onerror="alert(1)"`
Then I open the Chat Room. A popup shows that the JavaScript is executed.

The page source contains this HTML:

    <span class='chatNameDate'><span class='chatName'><img src=a onerror="alert(1)" Langkemper</span>

* [SAK-41763 - Escaping some outputs in the chat - #6928](https://github.com/sakaiproject/sakai/pull/6928)
* [SAK-41763: XSS in chat user name - 12.x - #6971](https://github.com/sakaiproject/sakai/pull/6971)
