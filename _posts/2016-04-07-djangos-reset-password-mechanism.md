---
layout: post
title: "Django's reset password mechanism"
thumbnail: snowflake-240.jpg
date: 2016-04-07
---

Django enables users to reset their password with a token that is emailed to the user. This mechanism contains some smart features, so let's look at how it works.

## Django's token generator

Django comes with password reset functionality, but it is disabled by default. To use it you have to make [an URL pattern to the view](https://docs.djangoproject.com/en/1.9/topics/auth/default/#using-the-views) so that the correct views become accessible.

A user may request a reset email that contains a token to access a page with which he can reset his password. This page needs to check whether the token presented by the user is the same token that was sent out in the email. There are basically two ways to do this:

* Generate a random token and store it in the database. When the user returns, check the token against the database.
* Generate a token in a deterministic way. When the user returns, recreate the token and check both tokens against each other.

Django uses the second method. It creates a token that consists of a hash of user properties. It is not kept in the database. Instead, when the user visits the link in the email, the hash is recalculated and compared to the token in the link. The [implementation](https://github.com/django/django/blob/master/django/contrib/auth/tokens.py) for creating the token looks like this:

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

As you can see the token consist of a timestamp and a HMAC with the following items:

* user ID
* password
* time of last login
* current date

The variable `key_salt` contains some hard-coded string. The function `salted_hmac` uses a secret from the settings (`SECRET_KEY`), so its output is not predictable for anyone who does not have the site configuration.

## Pretty clever

The token needs to have some properties for it to be secure:

* It should not be possible for users to create their own token.
* Tokens should expire, so that they are not valid forever.
* Tokens should be invalidated once used.

Let's see how the Django code implements these properties.

### It should not be possible for users to create their own token

The function to make the token uses the `salted_hmac` function, which uses a secret as key. Every installation should have its own secret, so that the token created for one site can't be used on another.

The `salted_hmac` function is also used in other places, such as when storing [notification messages in cookies](https://github.com/django/django/blob/master/django/contrib/messages/storage/cookie.py#L128). Now, consider what happens when you trick the application in creating a notification message that looks like a password reset token. If you can trick it in signing a notification message that looks like `user_id +  password + date`, you got yourself a valid password reset token. To prevent this, every call to `salted_hmac` has a fixed string with the purpose of the hash. That is what the `key_salt` parameter is for. For password reset tokens it contains "django.contrib.auth.tokens.PasswordResetTokenGenerator", and for notification messages it contains "django.contrib.messages". This makes it impossible to use hashes for another purpose.

### Tokens should expire, so that they are not valid forever

Reset tokens are typically used as soon as the user receives the email. If you request a reset token and then forget about it, it should not remain valid forever. Django implements this by using the day number both in the token and in the hash.

Putting the day number (`timestamp` in the code above) in the hash we make sure that the user can not change it to make the token valid any longer. But putting it just in the hash would make the token valid only today, while the day number is the same as when the token was created. This would be inconvenient for anyone using the functionality around midnight. That is why the day number is also in the token. First, the day number in the token is checked against the hash. Then, the `check_token` function checks whether the day number is not too long ago. This makes sure tokens are only valid for a limited time, by default three days.

### Tokens should be invalidated once used

When storing tokens in a database, you can simply delete them or mark them as used. For signed tokens, however, you can't really see whether they already have been used. Django solves this in a smart way, by hashing the login time and password into the token. This means that if you either log in or change your password, the password reset token is no longer valid. So if you use the token and reset your password, the token is invalidated. If you remembered your password after all and log in, the token is also no longer valid.

## Conclusion

We've looked at Django's reset password tokens. These are not stored in the database and are made with a HMAC of user properties. The HMAC's properties makes the reset tokens have the correct behavior. It makes the link invalid as soon as the user logs in, because then the `login_timestamp` field changes. It also invalidates the link as soon as the password changes, presumably after the user has used the link.

As you can see this method is elegant, but also pretty complex compared to storing a random token in the database. I would not recommend anyone to implement this scheme themselves. That said, Django's solution seems secure and elegant.
