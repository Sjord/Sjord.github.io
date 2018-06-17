
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

A Content Security Policy (CSP) specifies which resources can be loaded by the browser. This prevents against cross-site scripting attacks where an attacker loads his own JavaScript which then runs in the context of the application. A CSP whitelists some domains or methods that can be used to load JavaScript, which makes it harder for an attacker to inject a script that is not whitelisted.

### Advise

This header is only as good as your policy. Creating a good content security policy takes some effort. To create an effective policy you may need to change all your web pages. It's good practice to block `<script>` tags on the page, but if you are currently using that functionality it isn't possible to block all script tags. One possibility is to move all scripts to separate files, another is to use nonces, where a random code is added to each script tag.

First test your policy by using Content-Security-Policy-Report-Only. Configure a reporting URL, for example using the free service [Report URI](https://report-uri.com/). Consider using nonces if you want to keep using script tags on the page.

## X-Xss-Protection

Example: X-Xss-Protection: 1; mode=block

Browsers can sometimes detect when a cross-site scripting attack is happening. When JavaScript code is submitted in the request and then put verbatim on the page, it is a good sign that a reflected cross-site scripting attack is being used. Browsers can then choose not to execute the JavaScript. However, for some browsers you have to explicitly enable this feature.

### Advise

Use this in all responses. You can omit this header if you have a strong Content-Security-Policy without unsafe-inline or with nonces.

## Referrer-Policy

Example: Referrer-Policy: strict-origin-when-cross-origin

When you click on a link, the URL of the page that contains the link is sent in the request, in the Referer header. This may be a problem if the URL contains confidential information, such as a username. When clicking a link to another domain, the username will be sent in the request to the other domain. The Referrer-Policy header can be used to control which information from the URL is sent to another domain.

### Advise

Use this at least on HTML pages that contain links to other domains. There is no problem in including this on all responses.

## Expect-CT

To host a site on HTTPS, you need to have a certificate to show that you own the domain. It would be a big problem if a certificate for your domain would be accidentally issued to someone else, because then that someone could impersonate your domain. To keep track of issued certificates, there is a big certificate store that all certificates go into, the certificate transparency logs. The Expect-CT header indicates that the certificate used is indeed added to the certificate transparency logs and that the browser can check the logs to make sure the site uses a registered certificate.

### Advise

Don't bother with this header. Chrome already makes certificate transparency mandatory, even for sites that don't use this header. Other browsers don't support this header.

If you are a high-profile target worried about man-in-the-middle attacks with valid certificates, make sure you monitor the certificate transparency logs for anything that looks like your domain. This can also help to quickly find phishing sites.

## Public-Key-Pins

A secure HTTPS connection makes use of a certificate. The Public-Key-Pins header can narrow down which exact certificate may be used. The site can specify the key of any certificate in the certificate chain, and the HTTPS connection will only be made if the key matches the certificate.

### Advise

Don't use this header. Support is being removed from browsers. It's easy to break your site by accidentally misconfiguring public key pinning.
