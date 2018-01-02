---
layout: post
title: "Check CRSF token by default in ASP.NET MVC"
thumbnail: barbed-wire-fence-240.jpg
date: 2016-12-22
---

ASP.NET MVC protects against [CSRF](https://en.wikipedia.org/wiki/Cross-site_request_forgery) by using a secret token, and checking it if an attribute is present. In this post we will show how to check the CSRF token for all POST requests.

## Normal way of CSRF protection

ASP.NET MVC has [CSRF protection](https://www.asp.net/mvc/overview/security/xsrfcsrf-prevention-in-aspnet-mvc-and-web-pages) in the form of a secret token. Every form is provided with a token, that is checked on the server upon submit. Because the attacker that tries to exploit a cross site request does not have this token, he can not successfully submit the form. To use the standard MVC CSRF token, two things are necessary. First, you need to include the token in the form:

    @using (Html.BeginForm()) 
    {
        @Html.AntiForgeryToken()
        ...
    }

The statement `@Html.AntiForgeryToken()` will output a hidden form field containing the CSRF token. On submit, this token is submitted to the server, which has to check whether the request has a valid token. This can be done by adding the `ValidateAntiForgeryToken` attribute to the controller method:

        [HttpPost]
        [ValidateAntiForgeryToken]
        public ActionResult Test(TestViewModel model)
        {
            ...
        }

Using these two simple statements will protect you against CSRF.

## Enabling token checking by default

If you ever forget the `ValidateAntiForgeryToken` token on a controller method, that method is vulnerable to CSRF. The default behavior is to not check the CSRF token. We can change that by adding the `ValidateAntiForgeryToken` filter to all methods. 

In a new MVC project, the `Application_Start` method in `Global.asax.cs` calls `FilterConfig.RegisterGlobalFilters`, and we can use that method to register a filter to run on every request:

    filters.Add(new ValidateAntiForgeryTokenAttribute());

This works a little too well: no page can be requested anymore, since all GET requests now also require a token. We want just the POST requests to check for the token. For this, we need to create our [own filter](https://github.com/Sjord/CheckTokenByDefault/blob/master/CheckTokenByDefault/ValidateAntiForgeryTokenOnPost.cs).

    public class ValidateAntiForgeryTokenOnPost : IAuthorizationFilter
    {
        public void OnAuthorization(AuthorizationContext filterContext)
        {
            if (filterContext.HttpContext.Request.HttpMethod != "GET")
            {
                AntiForgery.Validate();
            }
        }
    }

As you can see, this filter only checks the CSRF token on anything else than a GET request. We can [register this filter](https://github.com/Sjord/CheckTokenByDefault/blob/master/CheckTokenByDefault/App_Start/FilterConfig.cs) on all requests as we did before:

    filters.Add(new ValidateAntiForgeryTokenOnPost());

Now, all our POST requests are protected against CSRF. It no longer matters whether we forget to add an attribute if we create a new controller method.

**Update**: The [AutoValidateAntiforgeryTokenAttribute](https://docs.microsoft.com/en-us/dotnet/api/microsoft.aspnetcore.mvc.autovalidateantiforgerytokenattribute?view=aspnetcore-2.0) is a built-in class that provides this functionality.

## Conclusion

ASP.NET MVC has great CSRF protection built-in, but it can be made even better by checking the CSRF token by default, instead of relying on an attribute which may be missing.

An example project using the code in this article is [available on GitHub](https://github.com/Sjord/CheckTokenByDefault).
