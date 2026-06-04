---
layout: post
title: "Session poisoning Zen Cart for a free discount"
thumbnail: poisonbasket-480.avif
date: 2024-02-06
---

Web applications often keep state corresponding to the current user. The
user gets a cookie with an opaque token, the session token. The browser
includes this cookie in each request. The web application uses this
token to retrieve the corresponding data from the database, which can be
used by the web application.

In most web application frameworks, this system is transparent, and the
web application just sees an associative array with information
belonging to the current user. What is in the array is generally trusted
by the web application. The web application is the only one modifying
this session array. Since it is on the server, the user cannot directly
view or edit the session data.

Session data is often used for sensitive information. For example, the
identifier of the current user may be stored in the session, or whether
the current user is an administrator. This can be secure, because the
session is under control of the web application. However, if the web
application is careless with writing variables to the session, a user
may be able to modify certain session variables. This vulnerability is
known as session poisoning.

Session poisoning is possible when:

- the application uses the same session variable for multiple purposes;
  or
- the user can write arbitrary variables to the session.

One case of session poisoning is when different parts of the application
use the same session variables for different purposes. The
authentication system may use the session variable `user_id` to mean the
currently authenticated user. The password reset system may use that
same variable to indicate which user wants his password reset. Then, by
requesting the pages in the proper order with the proper parameters, it
may be possible to log in to the application without authenticating.

A simpler instance of session poisoning is when user input is used
directly as the key to access the session, and it is thus possible to
write arbitrary variables to the session. This is often a serious
vulnerability, as it can totally compromise the authentication and
authorization layers of an application. It is often nearly impossible to
find with only hands-on testing, but easy to find with access to the
code. Even if it is possible to write arbitrary variables to the
session, it is sometimes difficult to find a situation in which that may
be exploited.

## Example: Zencart session poisoning

[Zen Cart](https://github.com/zencart/zencart) is a e-commerce web
application, a webshop. It has functionality in the category product
listing to either show or hide images. To remember this, it sets the
session variable `$_SESSION['imageView']` to true or false, and offers
an API endpoint to change this. However, this API endpoint is too
generic and accessible by all users:

```php
class zcAjaxAdminSessionChange extends base
{
    public function change()
    {
        if (!isset($_SESSION[$_POST['name']])) {
            $_SESSION[$_POST['name']] = true;

        } elseif ($_SESSION[$_POST['name']] === true) {
            $_SESSION[$_POST['name']] = false;
            return $_POST['name'] . ' set to false!';
        }

        $_SESSION[$_POST['name']] = true;
        return $_POST['name'] . ' set to true!';
    }

}
```

This endpoint can be called with a POST request to the URL
`/ajax.php?act=ajaxAdminSessionChange&method=change`, with `name=...` in
the POST body. The name from the user input is used directly in the key
of the session array, and this makes it possible to set any session
variable to true or false.

This vulnerability can for example be used to access discount coupons
without knowing the coupon code. When you enter a coupon code on
checkout to get a discount, Zen Cart validates the coupon code. It
verifies whether the entered coupon code corresponds with a coupon in
the database, whether that coupon is still valid. If so, it writes the
identifier to the session variable `$_SESSION['cc_id']`.

With the vulnerability in `ajaxAdminSessionChange`, any user can also
write to `cc_id`. It is only possible to write true or false. However,
because PHP is not type-safe and the application converts these to
integers, these are interpreted as coupons 1 and 0. The first coupon
that is created within Zen Cart has identifier 1, so that is the only
one we can use.

First, we add items to the cart and proceed to the checkout. Then, we
perform a POST request to
`/ajax.php?act=ajaxAdminSessionChange&method=change`, with `name=cc_id`.
This sets `$_SESSION['cc_id']` to true, which will be interpreted as
having coupon with identifier 1. If we now refresh the page, we can see
the coupon was applied:

<img src="/images/zencart-congratulations-discount.png" alt="Congratulations you have redeemed the Discount Coupon" style="width: 100%">
