---
layout: post
title: "Take over sessions in Metasfresh using CORS"
thumbnail: metasfresh-240.png
date: 2018-12-05
---

Metasfresh is a open source ERP web application. By default, it has misconfigured CORS headers which allow any other site to perform authenticated requests to it.

## Cross origin resource sharing

Because of the [same-origin policy](https://en.wikipedia.org/wiki/Same-origin_policy), every site can only access its own data. One site can perform a request to another site, but it can't read the response. This is so that one website can't read confidential information of another website.

However, sometimes there is a need for one site to access another site's data, and there is a way to configure access. Cross origin resource sharing (CORS) specifies a set of headers that make it possible to grant another site access.

## CORS in Metasfresh

Metasfresh responds with the following CORS headers:

    Access-Control-Allow-Origin: *
    Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PATCH, PUT
    Access-Control-Max-Age: 600
    Access-Control-Allow-Headers: x-requested-with, Content-Type, Origin
    Access-Control-Allow-Credentials: true

Furthermore, if you specify a origin header in the request, its value will be reflected in the `Access-Control-Allow-Origin` response header. This means that every site can perform authenticated requests to Metasfresh and read the response. The same-origin policy is effectively disabled.

## Stealing session IDs

Conveniently, Metasfresh also has a page that lists session identifiers. If we retrieve a session identifier, we can then set our SESSION cookie to this value and we will be logged in as this user. The attack scenario is like this:

* An admin that is logged in to Metasfresh visits our page.
* Our page requests session IDs from Metasfresh using cross-origin requests from JavaScript. These requests use the cookie of the admin that visits our page.
* We change our session cookie to a valid session ID, and are logged in into Metasfresh.

<img src="/images/metasfresh-get-sessions.png" alt="A web page on another domain lists Metasfresh session identifiers">

## Conclusion

CORS can be useful to grant access to another domain, but it works by disabling the corner stone of browser security: the same origin policy. Be sure to restrict access to certain domains.

## Read more

* [Implement CORS support #1035](https://github.com/metasfresh/metasfresh-webui-api/issues/1035)
* [CORSFilter](https://github.com/metasfresh/metasfresh-webui-api/blob/master/src/main/java/de/metas/ui/web/config/CORSFilter.java#L68)
