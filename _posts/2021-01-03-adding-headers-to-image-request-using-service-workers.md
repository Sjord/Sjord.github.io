---
layout: post
title: "Adding request headers to image requests using a service worker"
thumbnail: drawing-header-480.jpg
date: 2021-01-06
---

Service workers can modify requests from a web application. This includes requests from `<img>` tags, but additional steps are needed before a request header can be added.

<!-- image source: https://pixabay.com/nl/photos/persoon-vrouw-jong-meisje-nice-4453937/ -->

## Problem: authenticate images using a header

Some web applications have authentication credentials in HTTP request headers. Usually, this is in the form of the following header:

    Authorization: Bearer eyJhbjwt.jwt.jwt

This header can be included in every request performed from JavaScript. However, if the browser performs a request itself, this header is not automatically added. If the application adds an `<img>` tag to the page, the image is loaded, but no `Authorization` header is added to the request. This can be a problem if authentication is needed for the images. One way to add these headers to image requests is by using a service worker.

## Service worker

A service worker is like an intercepting proxy in that it intercepts HTTP requests, except it runs in a JavaScript thread in the browser. Every time the page performs a request, a `fetch` request is sent to the service worker, which then has the possibility to handle the request. This is useful, for example, to implement custom caching logic. The service worker can store some local data and determine whether to serve that data or perform a request to the server. Service workers also can modify the HTTP request before sending it through, and this includes adding HTTP headers. However, this only works if the mode of the request is changed.

## Request mode

The browser will perform some cross-origin requests without doing a CORS preflight request. Form posts with specific content types, image requests, embedded iframes. Requests that differ from these can only be sent using JavaScript, and trigger CORS functionality. Perhaps you recognize this from testing for CSRF: an endpoint that accepts `text/plain` is vulnerable to CSRF, but an endpoint that only accepts `application/json` is not. The browser also differentiates between these two types of requests in the service worker. The normal requests coming from image tags and forms are mode *no-cors*. The more elaborate requests are mode *cors*. That a request is mode *cors* doesn't mean that a preflight request is performed. If the request is same-origin, a preflight request is not performed, but the request may still be mode *cors*.

A mode *no-cors* request has certain limitations. For example, you can't modify the content-type from `text/plain` to `application/json`, without also changing the mode. You also can't add a custom request header without changing the mode. That means that if we want to add a HTTP request header to our image request, we have to change the mode of the request from `no-cors` to `cors`.

## Example code

In the main page, [register a service worker](https://developers.google.com/web/fundamentals/primers/service-workers/) with the following call:

    navigator.serviceWorker.register('/sw.js')

In the service worker, handle the `fetch` event. Create a copy of the request with the custom header and a mode of *cors*:

    self.addEventListener('fetch', function(event) {
        const newRequest = new Request(event.request, {
            headers: {"Authorization": "Bearer XXX-my-token"},
            mode: "cors"
        });
        return fetch(newRequest);
    }

## Conclusion

Modifying a *no-cors* request silently fails, unless the mode of the request is changed to *cors*.

## Read more

* [Fetch Living Standard - request mode](https://fetch.spec.whatwg.org/#concept-request-mode)
* [Request constructor](https://developer.mozilla.org/en-US/docs/Web/API/Request/Request)
* [Service Workers: an Introduction](https://developers.google.com/web/fundamentals/primers/service-workers/)