---
layout: post
title: "Avoid hotlinking images with Cross-Origin-Resource-Policy"
thumbnail: photo-480.jpg
date: 2024-11-27
---

An image on your site can be directly included in other sites. You end up with the costs of hosting and serving the image, while the other sites gains the benifits of showing your nice image on their page. With the response header *Cross-Origin-Resource-Policy* it is possible to notify the browser that images should only be usable by the same site or origin as the image, thus making hotlinking impossible.

<!-- Photo source: https://pixabay.com/photos/camera-phone-girl-hands-1869430/ -->

## Cross-Origin-Resource-Policy

Cross-Origin-Resource-Policy (CORP) is an HTTP response header that specifies whether the resource can be loaded from cross-origin domains. It is originally meant to protect against attacks such as [Spectre](https://en.wikipedia.org/wiki/Spectre_(security_vulnerability))

 that allows you to specify a policy for handling requests from other origins. By instructing browsers to block resource sharing in specific scenarios, CORP mitigates not only unauthorized hotlinking but also speculative side-channel attacks like Spectre and Cross-Site Script Inclusion (XSSI) attacks.

CORP is particularly effective for no-cors requests, which are typically issued for images, scripts, and other static assets by default. Rather than preventing the request entirely, CORP prevents the response body from being exposed to the requesting site if the origin doesn’t comply with the defined policy.

CORP Values

- same-origin: Only requests from the same origin as the resource are allowed.
- same-site: Requests from any origin within the same site (e.g., example.com and sub.example.com) are allowed.
- cross-origin: Resources can be requested and used by any origin.

To prevent hotlinking, the same-origin or same-site values are most relevant.

## Why Move Away From Referer-Based Hotlink Protection?

Historically, hotlink protection relied on the Referer header, which indicates the source of the request. For example, when an image is requested from https://yourbusiness.example/infographic.png, the Referer might show either:

- https://yourbusiness.example (if the request is from your site).
- https://anotherbusiness.test (if the request is from an external site).

This approach allowed servers to block requests with invalid or absent Referer headers. However, there are significant drawbacks:

1. Breaks Direct Linking: Legitimate direct links can be blocked.
1. Referer Omission: Referer headers may be stripped due to browser configurations, HTTPS-to-HTTP mismatches, or intentional suppression by embedding sites using referrerpolicy="no-referrer".
1. Dynamic Processing: Referer-based checks require server-side logic for each request, increasing complexity and latency.

CORP, on the other hand, offloads enforcement to the browser, bypassing these issues.

## Conclusion

Cross-Origin-Resource-Policy offers a modern, browser-enforced method to prevent hotlinking and protect your server resources. By moving away from outdated methods like Referer-based protection, you can achieve a more robust, scalable solution with minimal server-side complexity.

Whether you run a small blog or a large site using CDNs, implementing CORP is a step toward better resource management, security, and user experience.

Ready to protect your assets? Add CORP headers today and say goodbye to unwanted hotlinking!
