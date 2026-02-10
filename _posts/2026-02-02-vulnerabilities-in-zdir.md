---
layout: post
title: "Vulnerabilities in zdir 3"
thumbnail: zdir-bugs-480.avif
date: 2026-02-11
---

Zdir is a webapp that lists files in a directory, with preview and download functionality.

<!-- Photo source: https://pixabay.com/illustrations/art-beetle-bug-nature-animal-8595775/ -->

## Open source?

It looks open source, but its frontend is 2 MB of minified JavaScript.

## XSS in filenames

Uploading a file named `<img src=a onerror=alert('XSS')>.txt` runs JavaScript.

<img src="/images/zdir-xss.png" alt="an alert is shown in zdir">

The origin of this vulnerability is HTML concatenation:

```
if (W.Ftype == "file") return ` <a class = "name-link" href="${ie}${pe}">${ue}</a>`;
```

## XSS in markdown preview

Including `<img src=a onerror=alert(1)>` in a markdown file will show the alert when the file is previewed. Including the payload in a `README.md` will even execute JavaScript when the directory is viewed.

## Username is not correctly verified on login

During login, the posted username is read, but later replaced with the stored username from the config file. As long as the submitted username is lowercase, it will match.

The username is still used in the MD5(username + password) check. Because of this, parts of the username can be moved into the password and still pass the check. For example, "admin" / "password" could also be entered as "adm" / "inpassword".

```go
//密码进行md5加密
get_password = md5s(get_username + get_password)
//用户名转小写
get_username = strings.ToLower(username)

//比较用户名、密码是否匹配
if username == get_username && password == get_password {
```

## IP address spoofing through header

zdir uses several HTTP headers to determine the client’s IP address. It first checks X-Forward-For, then X-Real-IP, and finally falls back to the server’s reported IP.

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

The RenameFile function in zdir does not fully validate the old_name parameter. While new_name is checked to ensure it does not contain `../` or similar, `old_name` is used directly to build the source file path. This allows path traversal.

The function requires authentication. Path traversal only works if the process has permission to rename or move the targeted file. In many cases, system files like /etc/passwd cannot be changed because they are not writable by the user running zdir.

## Session token is hash of credentials

The session token in zdir is created as MD5(username + password + random_string). The random string is 6 characters long, with 62 possible characters for each position.

If an attacker intercepts a token, they can try all possible random strings offline to recover the username and password. Modern GPUs can test MD5 hashes quickly, so this search is feasible.

Getting a valid token is not easy.

```go
token := md5s(get_username + get_password + RandStr(6))
```

## Password insecurely hashed

zdir stores MD5(username + password) in its configuration file. MD5 is a fast hash and not designed for password storage.

If someone gets access to the config file, they can try different passwords until they find one that matches the stored hash. Modern hardware can perform this search quickly.

