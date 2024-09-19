---
layout: post
title: "If you don't want to solve a captcha, simply don't request one"
thumbnail: captcha-480.jpg
date: 2024-10-30
---

Captchas meant to block automated requests can sometimes be bypassed by simply not requesting the captcha image.

<!-- Photo source: https://pixabay.com/photos/artificial-intelligence-technology-3964530/ -->

Sometimes login pages are protected with captchas. In this case I mean images with scrambled text created by the application itself. The scrambled text needs to be decoded by a human and entered into an input box to submit the form.

These commonly work like this:

1. The login page has an image tag that points to a dynamically created captcha. E.g. `<img src="captcha.php">`.
2. The captcha endpoint creates a random string, stores it in the session and creates an image with the scrambled random string.
3. When the login page is submitted, it checks the submitted captcha against the captcha stored in the session.

This can be bypassed in two ways:

1. Block requests to captcha.php, and exclude the captcha from form posts. When submitting the login page, both the session variable and the post variable are undefined, and thus equal.
2. Request and solve the captcha once. Block subsequent requests to captcha.php and always submit the same captcha solution. Since captcha.php is never requested again, the captcha stored in the session remains the same.

## OsTicket example

OsTicket is a ticketing system, where users can file helpdesk tickets. The administrator can optionally enable captchas on the form to file new tickets. This works as follows:

- The form [includes captcha.php](https://github.com/osTicket/osTicket/blob/4689926b2d3d25754f0ddcf8d4e181a2817f6d56/include/client/open.inc.php#L104) as an image.
- Captcha.php creates a captcha and [stores the MD5 of the random code](https://github.com/osTicket/osTicket/blob/4689926b2d3d25754f0ddcf8d4e181a2817f6d56/include/class.captcha.php#L50) in the session.
- When the form is submitted, osTicket [compares](https://github.com/osTicket/osTicket/blob/4689926b2d3d25754f0ddcf8d4e181a2817f6d56/open.php#L28) the MD5 of the posted captcha value with the value from the session.

Now, because of the MD5 step we cannot simply omit the captcha from the post request. However, we can request a captcha once and subsequently use that value for all future post requests within the same session. Requesting captcha.php puts the MD5 of a captcha in the session, and if we then never request captcha.php again, this value remains the same.

## Conclusion

Captchas can sometimes be bypassed by blocking the request to the captcha image.

Custom captchas are rare, especially so in modern web applications. I feel this blog post would be more relevant 20 years ago. However, I recently ran into a custom captcha that could be bypassed using the vulnerability described above, showing that this mistake is still present today.

## Read more

- [Captcha Bypass Vulnerability in /admin/loginc.php · Issue #23 · s3131212/allendisk](https://github.com/s3131212/allendisk/issues/23)
