---
layout: post
title: "Grepping functions with ANTLR"
thumbnail: antlers-240.jpg
date: 2018-04-11
---


<!-- photo source: https://www.pexels.com/photo/animal-antler-antlers-blur-219906/ -->

## Searching code

For security code reviews, I sometimes want to find a specific pattern in the code. To do this I typically use grep or some other text-based search tool, but this is often insufficient. Specifically if you want to find code that is missing, or to find some relation between several lines of code.

### Finding missing attributes

For example, in ASP.NET controller methods can be marked with attributes that specify certain behavior for the action. For example, that is handles POST requests, that authentication is not needed, or that the method should be protected against CSRF:

        // POST: /Account/ExternalLogin
        [HttpPost]
        [AllowAnonymous]
        [ValidateAntiForgeryToken]
        public IActionResult Login(string provider)
        {
            ...
        }

To check the application for CSRF, we want to find all methods with `[HttpPost]` and without `[ValidateAntiForgeryToken]`.

## Parsing the code

To solve this problem, we will parse the C# code. This way, we can reliably identify methods with their attributes and find our matching methods. We use the [ANTLR](http://www.antlr.org/) parser toolkit and its existing [grammars](https://github.com/antlr/grammars-v4).



## Getting started with ANTLR

1. Download [the ANTLR jar](http://www.antlr.org/download/antlr-4.7.1-complete.jar).
2. Download [a target](http://www.antlr.org/download.html).
3. Download [a grammar](https://github.com/antlr/grammars-v4).
