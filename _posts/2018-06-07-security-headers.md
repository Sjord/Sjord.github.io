
## Strict-Transport-Security

Example: Strict-Transport-Security: max-age=31536000; includeSubDomains; preload

HTTP Strict Transport Security (HSTS) tells the browser to load the site over HTTPS in the future. 

Normally if you type a domain into your browser's URL bar, the browser first connects over the insecure HTTP to the site. The site then tells the browser to use HTTPS. However, a man-in-the-middle attacker can intercept the first request and keep the connection on insecure HTTP. The Strict-Transport-Security header tells the browser to connect immediately with HTTPS.

### Advise

Always use this header. Enable HTTPS and return a Strict-Transport-Security header on all domains. First test with a short max-age like 86400 (one day), and use 31536000 (one year) in production. If all your domains support HTTPS, add the includeSubDomains attribute. Don't add the preload attribute at first.

## X-Frame-Options

Example: X-Frame-Options: SAMEORIGIN

This header prevents loading the site in an iframe. This prevents against a clickjacking attack, where an invisible iframe is loaded on top of the attacker's page. The user thinks she is clicking on the attacker's page, while he is actually interacting with the overlaid iframe.

### Advise

Prevent your site from being loaded in an iframe, but use the [frame-ancestors](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/frame-ancestors) directive instead.

## X-Content-Type

Example: X-Content-Type: nosniff

Browsers render images different than text. Therefore, they need to determine what kind of data a particular response from the server contains. In some situations browsers try to guess what kind of data is returned, which is called content sniffing. This can lead to various vulnerabilities including cross-site scripting (XSS), if the browser thinks something is HTML when it is actually something else. To prevent this, content sniffing can be disabled with this header. The browser then uses only the Content-Type header and does not look at the response's contents to determine how to render it.

### Advise

Use this header on all responses. Make sure the Content-Type header corresponds to the content of the response. For example, don't return JSON with Content-Type: text/html.

## Content-Security-Policy

## X-Xss-Protection

Example: X-Xss-Protection: 1; mode=block

### Advise

Use this in all responses. You can omit this header if you have a strong Content-Security-Policy without unsafe-inline or with nonces.

## Referrer-Policy

Example: Referrer-Policy: strict-origin-when-cross-origin

## Expect-CT


## Public-Key-Pins


