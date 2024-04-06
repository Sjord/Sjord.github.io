---
layout: post
title: "Twig retrieves each attribute twice on Eloquent models"
thumbnail: retrievers-480.jpg
date: 2024-04-10
---

Using the Twig template engine with Laravel Eloquent models works, but is slower than it needs to be since Twig and Eloquent have a hard time determining which attributes actually exist.

<!-- photo source: https://pixabay.com/photos/dogs-golden-retriever-canine-7956516/ -->

## Twig is pretty smart

Twig is a popular PHP template engine. You create a HTML template like this:

```
{% raw %}<h1>Hello {{ planet.name }}</h1>{% endraw %}
```

And when supplied with a *planet* object, Twig will replace the variable with the planet's name. It's tries to be smart about how it retrieves the *name* property from the *planet* object. It tries several things:

- if *planet* is an array or an array-like object it tries `$planet["name"]`.
- if *planet* is an object, it tries:
    - `$planet->name`
    - `$planet->getName()`
    - `$planet->isName()`
    - `$planet->hasName()`

## Eloquent is pretty smart

Eloquent is the ORM for Laravel. It makes it easy to retrieve data from the database and keep it in a model object. Each field in the database is accessible through an attribute on the model object. Eloquent is pretty smart about its attributes:

- It's possible to transform or cast attributes just before returning them.
- Relations to other tables are also retrievable as attributes.
- It's possible to have a `getNameAttribute` method that contains custom code for the `name` attribute.
- The attributes are accessible through multiple ways, including array access.

## Performance problems

Both Twig and Eloquent do "smart" things with object attributes. This makes it hard to agree on which attributes exist. The result is that Twig asks for each attribute twice, which has now become a costly operation since Eloquent needs to figure out whether the attribute exists.

If the template contains `{% raw %}{{ planet.name }}{% endraw %}`, Twig cannot simply use `$planet['name']` and be done. Perhaps `$planet['name']` does not exist but `$planet->getName()` does. Because it has so many possibilities for `planet.name`, it has to check whether the current guess is correct. So it first checks with `isset($planet['name'])`, and if it exists it actually uses `$planet['name']`.

So Twig asks the Eloquent model whether an attribute exists. This is no simple question. In fact, the Eloquent model handles this by retrieving the attribute, and checking whether that succeeded. It then reports that the attribute in fact exists, and Twig retrieves the attribute again for the actual value.

## Conclusion

Both Twig and Eloquent try to be smart about handling attributes. The result is that each attribute is requested twice, reducing performance.

Being smart instead of simple often comes back to bite you. Attempting multiple things in order to guess the programmer's intent is bad for both performance and security. I wrote about an instance of this earlier in my article [Clever ORM causes password reset vulnerability](/2023/01/04/password-reset-vuln-in-orm/). Better to keep it simple.