---
layout: post
title: "If you don't want to solve a captcha, simply don't request one"
thumbnail: captcha-480.jpg
date: 2024-10-30
---

<!-- Photo source: https://pixabay.com/photos/artificial-intelligence-technology-3964530/ -->

Sometimes login pages are protected with captcha's. In this case I mean images with scrambled text created by the application itself. The scrambled text needs to be decoded by a human and entered into an input box to submit the form.

These commonly work like this:

1. The login page has an image tag that points to a dynamically created captcha. E.g. `<img src="captcha.php">`.
2. The captcha endpoint creates a random string, stores it in the session and creates an image with the scrambled random string.
3. When the login page is submitted, it checks the submitted captcha against the captcha stored in the session.

This can be bypassed in two ways:

1. Block requests to captcha.php, and exclude the captcha from form posts. When submitting the login page, both the session variable and the post variable are undefined, and thus equal.
2. Request and solve the captcha once. Block subsequent requests to captcha.php and always submit the same captcha solution. Since captcha.php is never requested again, the captcha stored in the session remains the same.

