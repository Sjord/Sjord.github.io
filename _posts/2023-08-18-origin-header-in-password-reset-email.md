---
layout: post
title: "Origin header in password reset email"
thumbnail: hanging-umbrella-480.jpg
date: 2023-09-13
---

Some applications use the `Origin` header to determine their own domain. This can result in account takeover when used in password reset emails.

<!-- Photo source: https://pixabay.com/photos/architecture-modern-sculpture-art-3148080/ -->

Typically, when you forgot your password, you can enter your username in a form and the application sends you a password reset mail. That mail contains a URL with a token that authenticates you to the application, and you can change your password.

The email needs to contain an absolute URL to the application, with scheme and domain. This is typically not hardcoded, to support running the application on different domains or environments. So the application has to figure out the domain it is currently running on to use in the password reset email.

One way to do this is to look at the `Origin` header. It contains both the scheme and the domain, and seems ideal to create a URL from. However, being a request header it can be changed by the client.

The attack thus works as follows: an attacker requests a password reset on behalf of the victim, and modifies the `Origin` header in that request to a domain they control. The victim now receives a password reset email that looks legitimate, but links to the attackers domain. If the victim clicks on the link, the attacker learns the password reset token and can take over the victim's account.

Of course, the victim needs to click the link for this to work. Since they didn't request the password reset themselves, the probability is quite low that they do.

## Which variable can be trusted?

Of course, you shouldn't use data that is under control of the user for security-relevant URLs such as password resets. However, for developers it is often not obvious which values are headers and which values can be trusted.

When an application needs its own domain, developers often just print all variables and pick the one that best suits their needs. So they run `var_dump($_SERVER)` or `print(request.META)` and look in the output for a variable that suits their needs. `HTTP_ORIGIN` looks promising. It's a request header, but you would have to know what the `HTTP_` prefix means to know that.

Furthermore, these mappings mix trusted and untrusted variables. `REMOTE_ADDR` is set by the server, and can be trusted. `HTTP_HOST` is often secure, but not always. `HTTP_REFERER` is definitely not to be trusted. This makes it harder on the developer to be aware of the security implications of the variables they use.

## Examples

I thought this would be quite a rare bug, but even so I found several examples on GitHub:

[JammerCore](https://github.com/JammerCore/JammerCore/blob/0d6a9459480b3a1d6355f93421b3e7118a3b3db1/public-api/vx/user.php#L202):

```
define('SH_URL_SITE', isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'http://ludumdare.org');

$message = [
    "Click the following link to change your password:",
    SH_URL_SITE."?".SH_ARGS."id=$id&key=$key".SH_PASSWORD,
];

return mailSend_Now($mail, $subject, $message, 'user-password-reset');
```

[FestEasy](https://github.com/wahello/festeasy/blob/staging/backend-service/backend/api/v1/resources/auth/recover_password.py#L19-L27):

```
class RecoverPassword(Resource):
    def post(self):
        ...
        host_url = (request.environ['HTTP_ORIGIN']
                    if 'HTTP_ORIGIN' in request.environ.keys() else None)
        url = (
            '{host_url}/reset-password?token={token}'
            .format(
                host_url=host_url,
                token=forgot_password_token.token,
            )
        )
        send_templated_email(url, ...)

```

[Mentii](https://github.com/mentii/mentii/blob/7a041b2846a59fae2dad551a1e727bf19e7d0880/Backend/app.py#L415-L419):

```
def forgotPassword():
  dynamoDBInstance = getDatabaseClient()
  httpOrigin = request.environ.get('HTTP_ORIGIN')
  user_ctrl.sendForgotPasswordEmail(httpOrigin, request.json, mail, dynamoDBInstance)
  return ResponseCreation.createEmptyResponse(200)
```

[OpenXeco](https://github.com/CybersecurityLuxembourg/openxeco-core/blob/09035691c8492785fd63425077427322ea10fefc/oxe-api/resource/account/forgot_password.py#L36-L56):

```
def post(self, **kwargs):
    origin = request.environ['HTTP_ORIGIN']
    url = f"{origin}/login?action=reset_password&token={reset_token}"
    send_email(..., render_template('password_reset.html', url=url, project_name=project_name))
```

[WeConnect-api](https://github.com/muhozi/WeConnect-api/blob/develop/api/views/user.py):

```
origin_url = request.headers.get('Origin') or ''
reset_link = '{}/auth/reset-password/{}'.format(origin_url, gen_token)
email = render_template('emails/reset.html',
                        name=user.username, url=reset_link)
```

[Ajenti](https://github.com/ajenti/ajenti/blob/5ab0dd3f08d2928810d8235123bda3b071d02a97/ajenti-core/aj/security/pwreset.py#L69):

```
origin = http_context.env['HTTP_ORIGIN']
link = f'{origin}/view/reset_password/{serial}'
self.notifications.send_password_reset(mail, link)
```

[Gangster-Legends-V2](https://github.com/ChristopherDay/Gangster-Legends-V2/blob/96c994aa1f180cd26c8f2bab939a8a6d9f113f98/modules/installed/forgotPassword/forgotPassword.inc.php#L37):

```
$url = $_SERVER["HTTP_ORIGIN"] . $_SERVER["SCRIPT_NAME"] . "?page=forgotPassword&action=resetPassword&auth=" . $user["U_password"] . "&id=" . $user["U_id"] . "";

$body = "To reset your password please follow the link below: \r\n " . $url;
mail($user["U_email"], "Password Reset", $body);
```

[Szurubooru](https://github.com/rr-/szurubooru/blob/master/server/szurubooru/api/password_reset_api.py#L31):

```
if config.config["domain"]:
    url = config.config["domain"]
elif "HTTP_ORIGIN" in ctx.env:
    url = ctx.env["HTTP_ORIGIN"].rstrip("/")
elif "HTTP_REFERER" in ctx.env:
    url = ctx.env["HTTP_REFERER"].rstrip("/")
else:
    url = ""
url += "/password-reset/%s:%s" % (user.name, token)

mailer.send_mail(
    config.config["smtp"]["from"],
    user.email,
    MAIL_SUBJECT.format(name=config.config["name"]),
    MAIL_BODY.format(name=config.config["name"], url=url),
)
```
