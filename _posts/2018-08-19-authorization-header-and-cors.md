---
layout: post
title: "Which CORS headers do you need to send an Authorization header?"
thumbnail: showing-credentials-240.jpg
date: 2018-09-12
---

In cross origin requests, the authorization header can be sent in two ways: either by the browser or specified along with the request. This article explains which CORS headers you need for each.

<!-- photo source: https://commons.wikimedia.org/wiki/File:Showing_off_Credentials!_-_Online_Relations_Manager_@VisitTampaBay_-_@TheKatLewis_TampaBay.jpg -->

## Authorization header

The Authorization HTTP header provides authentication information on a request. There are several types of authentication that use this header, and some are supported by browsers, such as [basic authentication](https://en.wikipedia.org/wiki/Basic_access_authentication). When an unauthenticated request is received by the server, it will respond with a HTTP 401 Unauthorized response with a WWW-Authenticate header. This will trigger the browser to ask the user for credentials. The browser will then perform the same request, but include an Authorization header with the entered credentials.

In contrast, some applications use the Authorization header without any intervening from the browser. A JavaScript app may obtain a token from the server and send that with each request to authenticate the request. This is called bearer authentication and the Authorization header is often used to send the token.

## Cross origin access with credentials

If you want to send an Authorization header along with a request to another site, that site has to notify the browser that that is permitted. After all,  sites can't just access each other's pages. It would be insecure if this site could perform an AJAX request to your bank's site, using the cookies from your browser. However, there are some use cases for cross-site access. In that case, the CORS HTTP response headers can grant access to another site. These are response headers, so the application that handles the request has to give its OK that the response is used by another application.

## XHR requests with Authorization header

When performing a cross-origin request which includes authorization header, the server needs to respond with approval of the use of credentials. How this is done differs depending on whether the Authorization header is set by the browser or from your application. 

### By the browser

Browsers support HTTP basic authentication as described above, where the browser asks for a username and password and sends it with every subsequent request. To use this, you need to enable credentials on your request. This will send cookies, client-side certificates, and basic authentication information in the Authorization header along with the request. To do this, you need three things:

* On the client, specify that you want to include credentials. Set [Request.credentials](https://developer.mozilla.org/en-US/docs/Web/API/Request/credentials) to `include`.
* On the server, respond with `Access-Control-Allow-Credentials: true`. This lets the client know that authenticated requests are permitted.
* On the server, respond with `Access-Control-Allow-Origin` header, containing the origin that performs the request. You must specify a URL, a wildcard won't work with authenticated requests.

The browser handles authentication, so the application won't see a username or password. If the user is not yet authenticated to the other site, the browser may display a scary message:

<img src="/images/authorization-cross-origin-dialog.png" alt="Dialog asking for credentials, containing a warning that the credentials will be submitted to another site.">

### By the application

Instead of letting the browser handle authentication, it is possible to send an Authorization header with a request from JavaScript by just specifying the name and value of the header. It works just like any other header.

One of these is the header `Access-Control-Allow-Credentials`, which allows authentication information such as cookies, authorization headers and client certificates in a cross-origin request. Another response header that can be used is `Access-Control-Allow-Headers`, which can be used to whitelist the Authorization header.

You need three things:

* On the client, specify the Authorization [header](https://developer.mozilla.org/en-US/docs/Web/API/Request/headers) you want to include in the request.
* On the server, respond with `Access-Control-Allow-Origin` header, containing the origin that performs the request, or a wildcard.
* On the server, respond with `Access-Control-Allow-Headers: Authorization` to inform the browser that the Authorization header in the request is permitted.

## Test it out

On the [demo page](https://demo.sjoerdlangkemper.nl/auth/fetch.html) you can perform cross-origin requests using different request and response headers.

## Conclusion

If you specify your own authorization header, it works just like any other header. If you want the browser to send along the authorization header, it works like a authenticated request.
