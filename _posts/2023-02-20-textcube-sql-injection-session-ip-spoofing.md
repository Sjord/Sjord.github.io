---
layout: post
title: "IP spoofing and SQL injection in Textcube"
thumbnail: jenga-480.jpg
date: 2023-03-15
---

Textcube is a open source blogging application. It contains a SQL injection vulnerability in a HTTP header that is used to determine the client's IP address.

<!-- Photo source: https://pixabay.com/nl/photos/jenga-evenwicht-gevoeligheid-1941500/?download -->

## Introduction

Textcube is written in plain PHP, without an underlying framework such as Laravel. This is a good recipe for security vulnerabilities, since it is hard to do everything right unless there's a secure framework to build on.

## IP spoofing

Textcube [interprets](https://github.com/Needlworks/Textcube/blob/69a531543597b4c73255d3f175a6248ccb69fdf5/framework/boot/00-UnifiedEnvironment.php#L48-L53) the `Client-IP` and `X-Forwarded-For` headers as the source IP address of the request.

```
if(isset($_SERVER['HTTP_CLIENT_IP'])) {
	$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CLIENT_IP'];
} else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$firstIP = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
	$_SERVER['REMOTE_ADDR'] = $firstIP[0];
}
```

The `$_SERVER['HTTP_CLIENT_IP']` contains the value from the `Client-IP` request header, and that's copied to `$_SERVER['REMOTE_ADDR']`, which normally contains the IP address of the client. This is meant to support proxy servers. Without a proxy server, the client connects directly to the web server, and their IP address will be set as `REMOTE_ADDR`. However, when you have a proxy server or a CDN such as CloudFlare in between, the client connects to the proxy server and not to your webserver. The webserver only receives connections from the proxy server, so `REMOTE_ADDR` will always contain the same IP address. To solve this, most proxy servers relay the IP of the client in a request header. The proxy sets `Client-IP`, and the webserver can use that to determine the IP address the request originated from. The problem is that anyone can set the `Client-IP` header, not just the proxy. If someone includes a `Client-IP` header in a request to the server, they can spoof their IP address. Textcube now thinks that the value from `X-Forwarded-For` or `Client-IP` header where the request came from.

In this case, Burp's active scanner detected that this was possible. I also often test this manually, or look in the code for use of the `X-Forwarded-For` header. There are also * [Burp](https://portswigger.net/bappstore/3a656c1be14148c6bf95642af42eb854) [plugins](https://portswigger.net/bappstore/ae2611da3bbc4687953a1f4ba6a4e04c) that automatically set these headers.

Impact depends on how to application uses the IP address. Sometimes it's possible to bypass a IP allow list or evade brute-force protection. Often it's just possible to change the IP address in the logs.

These header values are generic strings. Even though they typically contain IP addresses when normally used, they don't have to. So it's possible to set the client address to `<script>alert(1)`, and I have found XSS like this before.

## Session handling

The client's IP address is [used](https://github.com/Needlworks/Textcube/blob/69a531543597b4c73255d3f175a6248ccb69fdf5/framework/legacy/Textcube.Control.Session.php#L109-L116) in the session handling logic.

```
private static function getAnonymousSession() {
    ...
    $result = self::query('cell',"SELECT id FROM Sessions WHERE 
        address = '{$_SERVER['REMOTE_ADDR']}' AND userid IS NULL AND preexistence IS NULL");
    if ($result)
        return $result;
    return false;
}
```

It used to be pretty common to bind sessions to a IP address. The thought was that a session would only be valid on a single computer, thus on a single IP address. If the session identifier was used from another IP address, it was likely a result of a compromised session identifier. This does provide some security, but it also causes users to be logged out as soon as they change IP address, which can be quite often, especially for mobile devices. I think binding sessions to IP addresses is no longer widely recommended. [Token binding](/2017/07/05/prevent-session-hijacking-with-token-binding/) and [WebAuthn](https://en.wikipedia.org/wiki/WebAuthn) provide other solutions to bind the session to the computer, without depending on the IP address.

However, the code above does not necessarily bind the session to the IP address, it retrieves the session based on the IP address. That means that multiple people who share the same IP address also get the same session. This apparently only works this way for anonymous sessions, but this seems really strange (and insecure) to me nonetheless.

## SQL injection

The client's address is used in the query without any escaping. 

```
$result = self::query('cell',"SELECT id FROM Sessions WHERE 
    address = '{$_SERVER['REMOTE_ADDR']}' AND userid IS NULL AND preexistence IS NULL");
```

Normally, `$_SERVER['REMOTE_ADDR']` only contains an IP address. But as we described above, it is overwritten with the value from HTTP request headers. This means that we can put a SQL injection payload in the `Client-IP` header and get access to the database.

Since we are already querying the sessions table, we can easily change the query to retrieve a session identifier for another user:

    Client-IP: ' OR userid=1 OR address='

This returns the session identifier for user 1, which is often the admin, if they currently have a valid session. Of course, it is also possible to retrieve any data from the database:

    Client-IP: ' UNION SELECT loginid from tc_Users where userid=1; -- x

The ` -- ` here starts a SQL comment. The `x` is needed so that the space at the end does not get trimmed off. This returns the email address of the admin as the session identifier. So the response would contain:

    Set-Cookie: TSSESSIONtextcubelocal=admin%40sjoerdlangkemper.nl; path=/; 

## Changing built-in variables

Textcube changed the value of `$_SERVER['REMOTE_ADDR']`. This does not only change the value, but also the assumptions on the content. Normally, `$_SERVER['REMOTE_ADDR']` can be trusted to contain an IP address, set by the webserver. By overwriting this value, this assumption is no longer true. Using `REMOTE_ADDR` unescaped in a query would normally not lead to SQL injection, and it's quite possible a code review or scan of isolated files would have missed this vulnerability.

## Testing locally

I tested Textcube by setting up a Docker container running Apache and mounting the Textcube source directory in it. This way, it is also possible to change the code while testing. Sometimes I would put some `die()` statements in the code to see what the code was doing. I used this for example to determine exactly which query was being run when I tried to exploit the SQL injection, and I tried some of those queries on the local MySQL server.

When doing pentests at work, I almost never have my own environment running. I feel that this could really improve the efficacy of a pentest, but it's often quite a hassle to set up a complete environment.

## Conclusion

Unsurprisingly, the outdated PHP application built without a framework did have some security vulnerabilities. IP spoofing with a request header is one that I encounter more often, and it's worth it to test for this. However, that it leads to SQL injection is quite rare.
