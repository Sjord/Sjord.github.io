---
layout: post
title: "Open redirect in CrushFTP"
thumbnail: handoff-480.jpg
date: 2019-06-05
---

CrushFTP is a file transfer solution with a web interface to transfer files and perform administrative tasks. It had an open redirect vulnerability in the login functionality.

<!-- photo source: http://www.jbsa.mil/News/Photos/igphoto/2000909063/ -->

## Open redirect on login

When you are not logged in to a web application and try to access some page, you are redirected to the login page. Often, when you log in you are then redirected back where you wanted to go in the first place. This is a good place to look for open redirects.

CrushFTP does redirect the user to the original page, but it is not straightforward exploitable.

1. Browsing to http://localhost:8000/abc redirects to the login page at http://localhost:8080/WebInterface/login.html?path=/abc.
2. After logging in, the browser is redirected to http://192.168.1.102:8080/#/abc.

Because there is a `#` in there, and the URL is changed through JavaScript instead of a proper HTTP redirect, this does not seem exploitable. We can try but putting a URL in the path parameter:

    http://localhost:8080/WebInterface/login.html?path=https://www.sjoerdlangkemper.nl

This does not redirect to another domain, so no redirect vulnerability here.

## Looking at the source

CrushFTP is written in Java, which is easy to [decompile](http://jd.benow.ca/). If we decompile the crushftp.jar file we find a sendRedirect method in the ServerSessionHTTP class. This redirects the browser to a page specified by a parameter. We want to find a place where sendRedirect is called where we can determine the parameter. I found the following code:

    if (request.getProperty("skip_login", "").equals("true"))
    {
      sendRedirect(header0.substring(header0.indexOf(" ") + 1, header0.lastIndexOf(" ")).trim());
      write_command_http("Content-Length: 0");
      write_command_http("");
    }

If the `skip_login` request property is true, then the server redirects to the specified path. The `header0` variable contains something like `GET /abc HTTP/1.1`, and the part between the two spaces is passed to sendRedirect. This can be used as an open redirect, using this request:

    http://localhost:8080//www.sjoerdlangkemper.nl?skip_login=true

This will trigger a redirect to `//www.sjoerdlangkemper.nl`, thus redirecting to another domain.

## Conclusion

Black-box testing was followed by code review to find an open redirect vulnerability.