---
layout: post
title: "A method to do TLS on IoT devices"
thumbnail: todo-480.jpg
date: 2019-07-31
---


Consider a device with a web interface in a home network. It has an RFC1918 address like 192.168.1.123. How can we connect to this device using HTTPS using a trusted certificate?

### RFC1918 certificates

An obvious but incorrect solution would be to create a certificate for the IP address, 192.168.1.123. However, you won't find any certificate authority that gives you such a certificate, and for good reason. The IP address does not uniquely identify one host. In your network this IP address points to the correct device, but another network may have another device running on the same IP address. If a certificate authority gives someone a certificate for 192.168.1.123, that person can provide a HTTPS service in all RFC1918 networks. Every IoT manufacturer would want certificates for all RFC1918 addresses, making it impossible to verify that you are really talking to your device on 192.168.1.123, and not to an attacker who got the private key from another device.

### Using domain names to RFC1918 addresses

Instead, we get certificates for domains that point to RFC1918 addresses. At first glance this may seem to have the same problem described above, that everyone can provide a HTTPS connection on every IP address. This is still true, but now the user can check the URL.

Let's say we have an Umbrella web cam with the serial number 12345, which has a certificate for c12345.umbrellacams.com, which points to 192.168.1.123. Now, if an attacker buys a similar webcam and extracts the private key from it, he has a valid certificate for the same IP address, but for a different domain name. His camera has serial number 98765, and his certificate is thus for c98765.umbrellacams.com. If I check that the serial number in the URL corresponds to the serial number on my camera it is not possible to perform a man in the middle attack.

### Dynamic DNS

Obviously every home network is different and the manufacturer does not know in advance which IP address will be assigned to the IoT device. Therefore, the device needs to be able to change the DNS entry to point to the correct IP address.
