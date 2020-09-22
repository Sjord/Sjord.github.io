---
layout: post
title: "IoT security regulation"
thumbnail: iot-board-480.jpg
date: 2020-09-30
---

## History of IoT security

The security devices of IoT consumer devices has been bad for a long time. The same basic problems keep appearing. 

One of the reoccurring problems is the use of default passwords. In 2014, [over 73,000 camera's could be accessed using default passwords](https://www.csoonline.com/article/2844283/peeping-into-73-000-unsecured-security-cameras-thanks-to-default-passwords.html). In 2018, the Mirai botnet compromised a large number of devices. Mirai gained access using default usernames and passwords. Last year, [Rapid7 reported vulnerabilities in children's smart watches](https://blog.rapid7.com/2019/12/11/iot-vuln-disclosure-childrens-gps-smart-watches-r7-2019-57/). One of the main problems was a default password. As you can see, manufacturers have had the chance for years to become aware of this vulnerability and prevent it, but the vulnerability still occurs in many devices.

## Insufficient market pressure

If a device is hacked, it typically remains functioning normally from the perspective of the end user. If it stops working, the end user may not be aware that this is because of insufficient security. Consumers typically are not aware that their device has been hacked, making it unlikely that they select a more secure product in their next purchase.

And even when consumers prefer a secure product, it is pretty hard to evaluate in advance whether a product is secure. The box saying "military grade encryption" is not a good indication of overall product security. Devices are not typically tested for security before hitting the market, and consumer organisations have only recently started to evaluate security in consumer devices.

This lack of pressure and lack of transparency means that the free market is not going to solve the security problems of IoT devices on its own.

## Regulations and enforcement

Security in IoT devices needs to be regulated. There should be laws that state that devices should have certain security provisions. If the device does not meet the minimum security requirements, the distributor is fined and the device is taken of the market.

How this currently works in Europe is that devices are tested selectively after they are available on the market. No permission is required before selling the product, but the distribution can be forbidden if the device does not meet the regulations. It is limited to European distributers. You would still be able to purchase an insecure device from AliExpress.

Enforcing regulations would give authorities the means to formally reprimand companies that distribute insecure devices.

## Basic requirements

The regulations need to be fairly simple. The goal is to eliminate the low-hanging fruit, such as default passwords. Forbidding zero-day vulnerabilities such as buffer overflows would not work. The requirements should be fairly easy to implement for the device manufacturer. They should be easy to test by authorities.

I participated in a research project for [Qbit](https://www.qbit.nl/blog/publication-iot-devices-blog-2/) and the [Dutch Telecommunication Agency](https://www.agentschaptelecom.nl/actueel/nieuws/2020/08/26/acht-simpele-eisen-kunnen-de-cyberveiligheid-van-%E2%80%98slimme-apparatuur%E2%80%99-sterk-verbeteren), in which we determined that the following requirements make sense:

* Use strong passwords
* Use unique passwords
* Enforce authentication
* Restrict functionality to what's needed
* Encrypt network traffic
* Update firmware
* Verify firmware before installing
* Inform the end user about security

## Conclusion

These requirements are pretty basic; easy to implement and easy to verify in a black-box test. Even so, if IoT devices conform to these requirements, the security of them would improve greatly.

## Read more

* [Essential requirements for securing IoT consumer devices](https://www.agentschaptelecom.nl/binaries/agentschap-telecom/documenten/rapporten/2020/08/26/onderzoeksrapport-essential-requirements-for-securing-iot-consumer-devices/Essential+requirements+for+securing+consumer+IoT+devices.pdf)