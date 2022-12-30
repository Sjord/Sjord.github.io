---
layout: post
title: "Clever ORM causes password reset vulnerability"
thumbnail: border-collie-480.jpg
date: 2023-01-04
---

At one of my previous jobs, the database logic was a little bit too clever, resulting in a vulnerability in the authentication layer.

<!-- Photo source: https://pixabay.com/nl/photos/border-collie-hond-met-bot-750593 -->

I worked for a hosting company, developing the customer configuration panel. We had an [ORM](https://en.wikipedia.org/wiki/Object%E2%80%93relational_mapping) that retrieved data from the database and put it in an object. It was trying to be smart in finding objects. When retrieving a blog post with something like `Blog->get($id)`, you could pass anything in the `$id` parameter and the ORM would do it's best to find the corresponding object. It would first try to retrieve the blog post with the given identifier. If that didn't work, it would interpret the `$id` parameter as a slug and try to find a blog post by slug. After that, it would try URL, or some other field. Pretty convenient for the developers, making it easy to retrieve objects by any unique property.

This led to a security vulnerability in the password reset flow. When a user requested a password reset, we would generate a random token, save it to the database and email it to the user. The user would click the link in the email, which would look up the token in the database, and authenticate the user. The lookup was of course done using our ORM, something like `ResetToken->get($tokenFromEmail)`. Because the ORM was trying to be smart about this, `$tokenFromEmail` could not only contain an secret token, but also an numeric object identifier. Guessing tokens is hard, but guessing sequential numbers is easy. So this allowed anyone to enumerate reset tokens and reset user's passwords.

I actually discovered this vulnerability during a demonstration of new functionality. We changed something about the password reset flow. I wanted to show what happens when you enter an invalid token. So I entered some random numbers such as "123". I expected the error page for an invalid token, but got a prompt for a new password. Then I realized that I actually got the reset token with identifier 123.

We solved this by creating a new function that explicitly looks up the password reset objects by their secret token.

Another solution would be to go really type-safe and use different types for identifiers and tokens and other types of data. So `ResetToken->get(new ResetTokenIdentifier(123))` would look up by identifier, and `ResetToken->get(new ResetTokenSecret($tokenFromEmail))` would look up by secret. I have never encountered this level of type safety in an actual program. Most programs I see are "stringly typed".

I have seen other vulnerabilities because of guessing them meaning of user input, or trying to smartly recover when some lookup fails. In general it's better to be strict about it, and not too clever.
