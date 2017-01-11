---
layout: post
title: "Checking passwords against a dictionary in ASP.NET MVC"
thumbnail: dictionary-240.jpg
date: 2017-01-12
---

When using passwords for authentication, users may choose passwords that are too easily guessed. A method to prevent this is to have a list of known passwords and deny any password that is present in the list. This post will describe how to implement this in ASP.NET MVC Core. When registering, the user will get an error if he tries to use a password that is present in the dictionary.

## Changing password requirements

By default, an ASP.NET MVC project will have some limitations on passwords that users choose. First, there is a length requirement defined in the [`RegisterViewModel`](https://github.com/Sjord/CheckPasswordDictionary/blob/master/src/CheckPasswordDictionary/Models/AccountViewModels/RegisterViewModel.cs#L17). Furthermore, by default the password must also contain uppercase letters, digits and non-alphanumeric characters. These options can be set by changing the `PasswordOptions` in [`Startup.cs`](https://github.com/Sjord/CheckPasswordDictionary/blob/master/src/CheckPasswordDictionary/Startup.cs#L47). When creating a new project, there is already this line:

    services.AddIdentity<ApplicationUser, IdentityRole>()

Password options can be set by passing a callback as parameter. In this example, we disable all character class requirements:

    services.AddIdentity<ApplicationUser, IdentityRole>(o => {
        o.Password.RequireNonAlphanumeric = false;
        o.Password.RequireDigit = false;
        o.Password.RequireUppercase = false;
    })

## Adding a password validator

When a user chooses a password, we want to check it against the list and reject it if it is in there. To do this, we need some code that validates the password when the user registers.
We create a new class [`CheckPasswordDictionary`](https://github.com/Sjord/CheckPasswordDictionary/blob/master/src/CheckPasswordDictionary/CheckPasswordDictionary.cs) that implements `IPasswordValidator<ApplicationUser>` and register it in [`Startup.cs`](https://github.com/Sjord/CheckPasswordDictionary/blob/master/src/CheckPasswordDictionary/Startup.cs#L55) like this:

    services.AddScoped<IPasswordValidator<ApplicationUser>, CheckPasswordDictionary>();

The `IPasswordValidator` interface has one method, `ValidateAsync`, that should return whether or not the password is acceptable.

## Checking the password against a list

We have a simple text file with commonly used passwords. We read this into a [`HashSet`](https://msdn.microsoft.com/en-us/library/bb359438.aspx) using [`File.ReadLines`](https://msdn.microsoft.com/en-us/library/dd383503.aspx). The advantage of a HashSet is that lookups are fast, so we can check quickly whether our password is in the dictionary. To obtain the path to the dictionary, we use an `IHostingEnvironment` object that is provided to the password validator using dependency injection.

    private HashSet<string> LoadDictionary()
    {
        var filename = Path.Combine(hostingEnvironment.ContentRootPath, "Data/dictionary.txt");
        return new HashSet<string>(File.ReadLines(filename));
    }

## Returning our verdict

In the `ValidateAsync` function, we return a `IdentityResult.Failed` if the password is in the list, and a `IdentityResult.Success` otherwise:

    public async Task<IdentityResult> ValidateAsync(UserManager<ApplicationUser> manager, ApplicationUser user, string password)
    {
        var dictionary = LoadDictionary();
        if (dictionary.Contains(password))
        {
            return IdentityResult.Failed(new IdentityError { Code = "TOOCOMMON", Description = "Password is present in a list with commonly used passwords" });
        }
        return IdentityResult.Success;
    }

## Possible improvements

This method of looking up the password in a dictionary is a bit naive in that it doesn't detect common variations or "leet-speak". However, it can be effective at blocking the most common passwords. Note that the dictionary I used does not contain passwords that consist solely of numbers. You should probably block those in some other way. It also makes sense to include the name of your website or service in the dictionary. Finally, you should check that the password is not the same as the username.

## Conclusion

It is fairly simple to create a new validator for passwords, so that users can't register with a password that is too simple.

The [example project can be found on GitHub](https://github.com/Sjord/CheckPasswordDictionary).
