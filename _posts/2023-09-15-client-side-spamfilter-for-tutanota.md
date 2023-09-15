---
layout: post
title: "I made a client-side spamfilter for Tutanota email"
thumbnail: filter-480.jpg
date: 2023-10-11
---

<!-- Photo source: own work -->

## Introduction

When Google Workspaces started charging money, I switched to another email provider. My new email provider, Tutanota, is great in many ways, but the spam filter is not very good. I would get dozens of spam messages each day. Often I would get the same spam message multiple times. These would show up in my inbox, even after I reported an email as spam. I was getting tired of this, and decided to do something about it. I made my own client-side spam filter.

## Browser extension

Tutanota doesn't have IMAP or something that would make it possible to retrieve and filter messages in a standard way. I mainly use the Tutanota web app from the browser, and decided to implement the spam filter there, using JavaScript.

## Reverse engineering the API

I opened the Tutanota web app and the browser console, and tried some things. There's a global `tutao` variable that offers an entry into the Tutanota internals.

Most web applications have such a global variable, but this is not necessarily the case. It is entirely possible to implement a single-page app (SPA) without using global variables.

## Running script on the page

I created a browser extension with a content script that runs on the Tutanota mail app. A content script has access to the DOM, but still runs within its own context. Since I want to hook into the Tutanota JavaScript, I want to access the global variables in the page. This is not possible from the content page, but it *is* possible to add a script to the page itself. So [my content script](https://github.com/Sjord/tutanospam/blob/main/tutanospam/content.js) does nothing else than adding another script to the page. That scripts can access the global variables that contain Tutanota functionality:

```
const script = document.createElement('script');
script.setAttribute('src', chrome.runtime.getURL('page.js'));
document.body.appendChild(script);
```

## 
