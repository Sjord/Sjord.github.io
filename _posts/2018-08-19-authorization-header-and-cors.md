---
layout: post
title: "Can you send an Authorization header without Allow-Credentails?"
thumbnail: showing-credentials-240.jpg
date: 2018-09-12
---

<!-- photo source: https://commons.wikimedia.org/wiki/File:Showing_off_Credentials!_-_Online_Relations_Manager_@VisitTampaBay_-_@TheKatLewis_TampaBay.jpg -->

## Authorization header

The Authorization HTTP header provides authentication information on a request. There are several types of authentication, and some are supported by browsers. When an unauthenticated request is received by the server, it will respond with a HTTP 401 Unauthorized response with a WWW-Authenticate header. This will trigger the browser to ask the user for credentials. The browser will then perform the same request, but include an Authorization header with the entered credentials.

## Cross origin access with credentials

Sites can't just access each other's pages. It would be insecure if this site could perform an AJAX request to your bank's site, using the cookies from your browser. However, there are some use cases for cross-site access. In that case, the CORS HTTP headers can grant access to another site. One of these if the header `Access-Control-Allow-Credentials`, which allows authentication information such as cookies, authorization headers and client certificates in a cross-origin request.

## XHR requests with Authorization header


* If you specify the authorization header, you need Access-Control-Allow-Headers: Authorization.
* If you use the browser's authorization header, you need Access-Control-Allow-Credentials: true
