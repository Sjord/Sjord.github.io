---
layout: post
title: "Avoid hotlinking images with Cross-Origin-Resource-Policy"
thumbnail: photo-480.jpg
date: 2024-11-27
---

An image on your site can be directly included in other sites. You end up with the costs of hosting and serving the image, while the other sites gain the benefits of showing your nice image on their page. With the response header *Cross-Origin-Resource-Policy* it is possible to inform the browser that images should only be usable by the same site or origin as the image, thus making hotlinking impossible.

<!-- Photo source: https://pixabay.com/photos/camera-phone-girl-hands-1869430/ -->

## Cross-Origin-Resource-Policy

Cross-Origin-Resource-Policy (CORP) is an HTTP response header that specifies whether the resource can be loaded from cross-origin domains. A CORP header of a response specifies whether the response body may be used by another site or domain. The header may have the following values:

- same-origin: the resource can be used in pages on the same origin (i.e. domain).
- same-site: the resource can be used in pages on the same site (i.e. other subdomains).
- cross-origin: the resource can be used on any domain.

## Spectre

CORP is originally meant to protect against attacks such as [Spectre](https://en.wikipedia.org/wiki/Spectre_(security_vulnerability)). Spectre is an attack that makes it possible to read memory in the same process with a side-channel attack. To load sensitive information into the browser, the attacker can add an image tag that performs a request to a sensitive resource. They add `<img src="https://bank.example/secret.php">` to their page and lure a victim to it. This loads the sensitive information into the browser process, which they can read with the Spectre vulnerability. To prevent such an attack, the bank can add a CORP header to its secret.php resource. This prevents it from being loaded cross-origin. The request will still be performed, but the response is discarded by the browser after it sees the CORP header.

## Blocking hotlinking

Since this header specifies whether a resource can be used by other sites, it can be effective in preventing hotlinking images. By adding a CORP response header with `same-origin` or `same-site` to each image, the images won't be shown in other sites that directly link to the image on your site. You can do this by adding the following response header to images to prevent other domains from including your images directly:

```
Cross-Origin-Resource-Policy: same-origin
```

This prevents hotlinking, but it can also introduce problems. If you host your images on a content delivery network (CDN) that is on another domain that your site, you also cannot load your images on your own site anymore. Also, in some cases hotlinking images may be desired, for example if you want your image to show up in social media previews.

## Conclusion

Cross-Origin-Resource-Policy (CORP) is a security header to prevent advanced cyberattacks, but it is also the easiest solution to prevent hotlinking images.
