---
layout: post
title: "Mark sensitive information as such in the source code"
thumbnail: highlighter-480.jpg
date: 2023-01-18
---

Sensitive information such as passwords and API keys should not be stored in strings, but in specific class meant for that purpose, to avoid accidentally exposing them in logs or stack traces.

<!-- Photo source: https://pixabay.com/nl/photos/markeerstift-kleuren-neon-markeren-1103715/ -->

## Exposing sensitive information by accident

In web applications, sensitive information is sometimes accidentally logged or shown in a stack trace. To prevent this, sensitive information should be marked as such in the source code. This can be done either with an attribute, or by creating a wrapper class.

Keeping sensitive information such as API keys around in strings makes it easy to work with them, but it also makes it easy for application components to show them in places where you don't want to. Some programming languages, such as PHP, have the option to include parameters in stack traces. When an error occurs, the application may expose sensitive information to the user. A stack trace in PHP may look like this:

```
PHP Fatal error:  Uncaught ValueError: p in index.php:3
Stack trace:
#0 index.php(7): connect_to_database('root', 'p@ssw0rd')
#1 index.php(10): login('sjoerd', 'hunter2')
#2 {main}
  thrown in index.php on line 3
```

Another example is logging objects that contain sensitive information. Logging a User object may write all their attributes, including their password hash and 2FA secret, to a log file. The following Serilog example will do its best to log the whole User object, including sensitive fields.

```
Log.Information("User login failed: {@User}", user);
```

## Using a wrapper class

Sensitive information can be stored using its own class. It stores the sensitive string in a private field, and only returns it when specifically asked for:

```
public class SensitiveString
{
    private string sensitiveValue;

    public SensitiveString(string sensitiveValue)
    {
        this.sensitiveValue = sensitiveValue;
    }

    public string GetValue()
    {
        return sensitiveValue;
    }

    public override string ToString()
    {
        return "***";
    }
}
```

Contrary to C# custom, we use a `GetValue` method here instead of a public field. That is to prevent anyone from accessing the actual sensitive value. When making it a public field, Serilog happily logs the content, defeating the purpose of this wrapper class.

Another obvious idea would be to extend the built-in String class. However, in any language I know of the String class is final and cannot be extended.

When such a class is logged or output in some other way, the sensitive information won't be shown. Of course, you would have to call `GetValue()` and get the string at some point, to interact with system functions.

## Using attributes

Another option is to keep sensitive information as strings, but mark fields with an attribute that indicates they are sensitive. For Serilog, there's Destructurama.Attributed, with attributes such as `NotLogged` and `LogMasked`.

This is easier to work with, but needs support of the logging library. If you add another logging library or some other inspection tool, it may not support the attributes you are using.

## Clarity in the code

Besides providing additional protection for sensitive information, this also informs developers about which information is sensitive. This makes reasoning about code and reviewing it easier, since it's clear which data must not be exposed.

## Conclusion

Storing sensitive information in a specific class, or marking it with an attribute, can both prevent accidental exposure and better communicate the sensitivity to developers.

Handling sensitive information can be improved if frameworks by providing a standardized class or attribute for sensitive information. It would be nice if you could pass a SensitiveString to `KeyDerivation.Pbkdf2` and other methods that take sensitive input.

## Read more

* [Logging objects with properties which hold sensitive data (to be hashed) - Stack Overflow](https://stackoverflow.com/questions/58670397/logging-objects-with-properties-which-hold-sensitive-data-to-be-hashed)
* [C# .net core, Log request without sensitive information - Stack Overflow](https://stackoverflow.com/questions/55104151/c-sharp-net-core-log-request-without-sensitive-information)
* [Using attributes to control destructuring in Serilog](https://nblumhardt.com/2014/07/using-attributes-to-control-destructuring-in-serilog/)

