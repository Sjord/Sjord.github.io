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
The implementation for creating the token looks like this:


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

As you can see the token used in the password reset URL contains a hash of the user ID, user password, the login timestamp and the current date. This is pretty clever. It makes the link invalid as soon as the user logs in, because then the `login_timestamp` field changes. It also invalidates the link as soon as the password changes, presumably after the user has used the link.

## Django-users2 verification link

Django-users2 uses almost the same code:


    def _make_token_with_timestamp(self, user, timestamp):

        ts_b36 = int_to_base36(timestamp)
        key_salt = 'users.utils.EmailActivationTokenGenerator'
        login_timestamp = '' if user.last_login is None else \
            user.last_login.replace(microsecond=0, tzinfo=None)
        value = (six.text_type(user.pk) + six.text_type(user.email) +
                 six.text_type(login_timestamp) + six.text_type(timestamp))
        hash = salted_hmac(key_salt, value).hexdigest()[::2]
        return '%s-%s' % (ts_b36, hash)

As you can see, again a hash is made. This time from the user ID, email, login time and current date. As you can see the user password is missing. We also know the user never logged in, because the account was just created and is not verified yet. This leaves the current date, which we know, and the user ID, which we can easily guess because it is just an incrementing integer. This means that the token in the verification link contains no secrets. We can create our own token and visit the verification link, even if we did not receive the e-mail. This makes it possible to circumvent the verification procedure, and for example create a lot of anonymous accounts automatically.

