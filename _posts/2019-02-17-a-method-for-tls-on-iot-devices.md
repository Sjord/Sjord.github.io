---
layout: post
title: "A method to do TLS on IoT devices"
thumbnail: phone-store-480.jpg
date: 2019-07-31
---

Consider a device with a web interface in a home network. It has an RFC1918 address like 192.168.1.123. How can we connect to this device using HTTPS using a trusted certificate?

<!-- photo source: https://www.flickr.com/photos/usdagov/36372930581/ -->

## RFC1918 certificates

An obvious but incorrect solution would be to create a certificate for the IP address, 192.168.1.123. However, you won't find any certificate authority that gives you such a certificate, and for good reason. The IP address does not uniquely identify one host. In your network this IP address points to the correct device, but another network may have another device running on the same IP address. If a certificate authority gives someone a certificate for 192.168.1.123, that person can provide a HTTPS service in all RFC1918 networks. Every IoT manufacturer would want certificates for all RFC1918 addresses, making it impossible to verify that you are really talking to your device on 192.168.1.123, and not to an attacker who got the private key from another device.

## Using domain names to RFC1918 addresses

Instead, we get certificates for domains that point to RFC1918 addresses. At first glance this may seem to have the same problem described above, that everyone can provide a HTTPS connection on every IP address. This is still true, but now the user can check the URL.

Let's say we have an Umbrella web cam with the serial number 12345, which has a certificate for c12345.umbrellacams.com, which points to 192.168.1.123. Now, if an attacker buys a similar webcam and extracts the private key from it, he has a valid certificate for the same IP address, but for a different domain name. His camera has serial number 98765, and his certificate is thus for c98765.umbrellacams.com. As long as the user checks that the serial number in the URL corresponds to the serial number on my camera it is not possible to perform a man in the middle attack.

## Dynamic DNS

Obviously every home network is different and the manufacturer does not know in advance which IP address will be assigned to the IoT device. Therefore, the device needs to be able to change the DNS entry to point to the correct IP address. The domain name c12345.umbrellacams.com now points to 192.168.1.123, but if something in the network changes the device may get a new IP address. The device should contact some service that changes the DNS entry for c12345.umbrellacams.com as soon as it detects it has a new IP address.

This should be done securely, otherwise other people can make c12345.umbrellacams.com point to something else. So the device should authenticate to the DNS service that it is really camera 12345. Luckily, it already has a private key and certificate to do this.

## Limitations

### Separate domain

Other subdomains on umbrellacams.com are not to be trusted. If I buy a camera and get the private key from it, I can host a HTTPS web site on the subdomain assigned to the camera. This means that umbrellacams.com should be a domain particularly for this usage, and not shared with the company web site.

### Different key per device

Each device must have a different key and certificate for this to be secure. This is pretty hard for device manufacturers. It is much easier to roll out the same firmware on all devices, but this would compromise all devices and domains as soon as one is hacked.

For the same reason, a wildcard certificate won't be secure, since that will give control of all subdomains to all devices.

## Custom client

There is an alternative to the setup described above. If the manufacturer has control over the client, he can set up his own secure link. If the device has a corresponding mobile app or desktop application the authentication logic can be made to work however you like. It is only when you want to use browsers and trusted certificates that you are limited by the behavior of those.

## Conclusion

With this setup it is possible to provide HTTPS that works in a browser and is properly secure. 