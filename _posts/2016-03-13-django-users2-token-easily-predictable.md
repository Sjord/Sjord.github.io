---
layout: post
title: "Django's reset password mechanism"
thumbnail: snowflake-240.jpg
date: 2016-03-13
---

Django enables users to reset their password a token that is emailed to the user. This mechanism contains some smart features. so let's look at how it works.

## Django's token generator

Django comes with password reset functionality, but it is disabled by default. To use it you just have to make [an URL pattern to the view](https://docs.djangoproject.com/en/1.9/topics/auth/default/#using-the-views) so that the correct views become accessible.

A user may request a reset email that contains a token to access a page with which he can reset his password. The token consists of a hash of user properties. It is not kept in the database. Instead, when the user visits the link in the email, the hash is recalculated and compared to the token in the link.
The [implementation](https://github.com/django/django/blob/master/django/contrib/auth/tokens.py) for creating the token looks like this:


    def _make_token_with_timestamp(self, user, timestamp):
        # timestamp is number of days since 2001-1-1.  Converted to
        # base 36, this gives us a 3 digit string until about 2121
        ts_b36 = int_to_base36(timestamp)

        # By hashing on the internal state of the user and using state
        # that is sure to change (the password salt will change as soon as
        # the password is set, at least for current Django auth, and
        # last_login will also change), we produce a hash that will be
        # invalid as soon as it is used.
        # We limit the hash to 20 chars to keep URL short

        hash = salted_hmac(
            self.key_salt,
            self._make_hash_value(user, timestamp),
        ).hexdigest()[::2]
        return "%s-%s" % (ts_b36, hash)

    def _make_hash_value(self, user, timestamp):
        # Ensure results are consistent across DB backends
        login_timestamp = '' if user.last_login is None else user.last_login.replace(microsecond=0, tzinfo=None)
        return (
            six.text_type(user.pk) + user.password +
            six.text_type(login_timestamp) + six.text_type(timestamp)
        )

As you can see it uses the following items for the hash:

* user ID
* password
* time of last login
* current date

The variable `key_salt` contains some hard-coded string. The function `salted_hmac` uses a secret from the settings (`SECRET_KEY`), so its output is not predictable for anyone who does not have the site configuration.

## Pretty smart

This is pretty clever. It makes the link invalid as soon as the user logs in, because then the `login_timestamp` field changes. It also invalidates the link as soon as the password changes, presumably after the user has used the link.

## Conclusion
