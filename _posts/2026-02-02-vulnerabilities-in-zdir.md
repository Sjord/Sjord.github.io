---
layout: post
title: "Vulnerabilities in zdir 3"
thumbnail: zdir-bugs-480.avif
date: 2026-02-11
---

Zdir is a webapp that lists files in a directory, with preview and download functionality. I found several vulnerabilities in it.

<!-- Photo source: https://pixabay.com/illustrations/art-beetle-bug-nature-animal-8595775/ -->

## Open source?

Zdir version 4 is the latest version, but its source is not available. Zdir versions up to 3 are available on [GitHub](https://github.com/helloxz/zdir/). Zdir 3 consists of a Go backend and a React frontend. The Go code is well readable, but the frontend is only distributed as a 2 MB minified JavaScript, making it hard to read.

## XSS in filenames

Zdir lists files. The filenames of these files are not properly encoded. Uploading a file named `<img src=a onerror=alert('XSS')>.txt` runs JavaScript.

<img src="/images/zdir-xss.png" alt="an alert is shown in zdir">

The origin of this vulnerability is HTML concatenation:

```javascript
if (W.Ftype == "file") return ` <a class = "name-link" href="${ie}${pe}">${ue}</a>`;
```

## XSS in markdown preview

Zdir supports rendering markdown, but it is not correctly sanitized against XSS. Including `<img src=a onerror=alert(1)>` in a markdown file shows the alert when the file is previewed. Including the payload in a `README.md` will even execute JavaScript when the directory is viewed.

## IP address spoofing through header

Zdir uses several HTTP headers to determine the client’s IP address. It first checks X-Forward-For, then X-Real-IP, and finally falls back to the server’s reported IP.

If the client sends one of these headers with a fake address, zdir will log that value. This means the logged IP may not represent the actual source IP.

```go
// 获取客户端IP
func GetClientIp(c *gin.Context) string {
	//尝试通过X-Forward-For获取
	ip := c.Request.Header.Get("X-Forward-For")
	//如果没获取到，则通过X-real-ip获取
	if ip == "" {
		ip = c.Request.Header.Get("X-real-ip")
	}
	if ip == "" {
		//依然没获取到，则通过gin自身方法获取
		ip = c.ClientIP()
	}
	//判断IP格式是否正确，避免伪造IP
	if V_ip(ip) {
		return ip
	} else {
		return "0.0.0.0"
	}
}
```

## Path traversal in rename functionality

The RenameFile function in zdir does not fully validate the `old_name` parameter. While `new_name` is checked for `../` or similar, `old_name` is used directly in the source file path. This allows path traversal.

The function requires authentication, and the renaming only works if the process has permission to rename or move the targeted file. This cannot be used to read /etc/passwd, for example, because zdir does not have permissions to move this file.

## Authentication

The first time you open Zdir, it asks to configure a username and password. It writes the username and an MD5 hash of the username and password to a config file. Those are the credentials that can be used to log in to the admin panel. When logging in, the backend creates a "CID" and a token, both needed for authentication. The frontend stores these in cookies and sends them in headers on every API request.

### Password insecurely hashed

Zdir stores MD5(username + password) in its configuration file. MD5 is a fast hash and not designed for password storage.

If someone gets access to the config file, they can try different passwords until they find one that matches the stored hash. Modern hardware can perform this search quickly.

### Session token is hash of credentials

The session token in zdir is created as MD5(username + password + random_string). The random string is 6 characters long, with 62 possible characters for each position.

If an attacker intercepts a token, they can try all possible random strings offline to recover the username and password. Modern GPUs can test MD5 hashes quickly, so this search is feasible.

Getting a valid token is not easy, and the random suffix makes it quite slow to brute-force the credentials.

```go
token := md5s(get_username + get_password + RandStr(6))
```

### Username is not correctly verified on login

During login, the entered username is not actually directly checked against the configured username. As long as the submitted username is lowercase, it will match.

However, the username is also part of the password hash, so it is checked more or less accidentally when checking the password. The password hash consists of `MD5(username + password)`. Because there is no separation between username and password, parts of the username can be moved into the password and still pass the check. For example, "admin" / "password" could also be entered as "adm" / "inpassword".

```go
//密码进行md5加密
get_password = md5s(get_username + get_password)
//用户名转小写
get_username = strings.ToLower(username)

//比较用户名、密码是否匹配
if username == get_username && password == get_password {
```

## Insecure CORS headers

Zdir has these CORS headers:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Headers: Content-Type, AccessToken, X-CSRF-Token, Authorization, Token,X-Token,X-Cid
Access-Control-Allow-Methods: POST, GET, OPTIONS, HEAD
Access-Control-Expose-Headers: Content-Length, Access-Control-Allow-Origin, Access-Control-Allow-Headers, Content-Type
Access-Control-Allow-Credentials: true
```

That doesn't even work, because `Allow-Credentials` does not work with an `Allow-Origin` of `*`. It also does not allow CSRF requests; even though Zdir does set cookies with crendentials in them, these are not used by the backend. Only the `X-Cid` and `X-Token` headers are read by the backend for authentication.

## Conclusion

Zdir has quite some vulnerabilities. However, I didn't succeed in getting into the admin portal without credentials, so not all is lost.