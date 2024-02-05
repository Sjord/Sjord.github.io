---
layout: post
title: "IoT security regulation"
thumbnail: iot-board-480.jpg
date: 2020-09-30
---

Consumer IoT devices have been riddled with the same vulnerabilities for a long time. Authorities are now considering IoT cybersecurity regulation, which would make it possible to take insecure devices off the market.

<!-- photo source: https://commons.wikimedia.org/wiki/File:Iot_Devices_At_Fscons_(130792869).jpeg -->

## History of IoT security

The security devices of IoT consumer devices has been bad for a long time. The same [basic problems](/2020/09/10/the-most-important-security-problems-with-iot-devices/) keep appearing.

One of the reoccurring problems is the use of default passwords. In 2014, [over 73,000 camera's could be accessed using default passwords](https://www.csoonline.com/article/2844283/peeping-into-73-000-unsecured-security-cameras-thanks-to-default-passwords.html). In 2018, the Mirai botnet compromised a large number of devices, using default usernames and passwords. Last year, [Rapid7 reported vulnerabilities in children's smart watches](https://blog.rapid7.com/2019/12/11/iot-vuln-disclosure-childrens-gps-smart-watches-r7-2019-57/). One of the main problems was a default password. As you can see, manufacturers have had the chance for years to become aware of this vulnerability and prevent it, but the vulnerability still occurs in many devices.

## Insufficient market pressure

If a device is hacked, it typically remains functioning normally from the perspective of the end user. If it stops working, the end user may not be aware that this is because of insufficient security. Consumers typically are not aware that their device has been hacked, making it unlikely that they select a more secure product in their next purchase.

And even when consumers prefer a secure product, it is pretty hard to evaluate in advance whether a product is secure. The box saying "military grade encryption" is not a good indication of overall product security. Devices are not typically tested for security before hitting the market, and consumer organizations have only recently started to evaluate security in consumer devices.

This lack of pressure and lack of transparency means that the free market is not going to solve the security problems of IoT devices on its own.

## Regulations and enforcement

Security in IoT devices needs to be regulated. We need laws that state that devices should have certain security provisions. If the device does not meet the minimum security requirements, the distributor is fined and the device is taken of the market.

How this currently works in Europe is that devices are tested selectively after they are available on the market. No permission is required before selling the product, but the distribution can be forbidden if the device does not meet the regulations. It is limited to European distributors. You would still be able to purchase an insecure device from AliExpress.

The need for regulation is not limited to Europe. In the United States, Senator Mark Warner introduced the [Internet of Things Cybersecurity Improvement Act](https://www.congress.gov/bill/116th-congress/senate-bill/734). In the United Kingdom, the government wants to introduce [IoT regulation](https://www.gov.uk/government/collections/secure-by-design) as well.

Enforcing regulations would give authorities the means to formally reprimand companies that distribute insecure devices.

## Basic requirements

The regulations need to be fairly simple. The goal is to eliminate the low-hanging fruit, such as default passwords, to enforce a minimum security on all devices. The requirements should be fairly easy to implement for the device manufacturer. They should be easy to test by authorities.

Forbidding zero-day vulnerabilities such as buffer overflows is not needed, and would not work. It is hard to determine whether a device has vulnerabilities for the authorities, and it is hard for the manufacturer to prevent them. The biggest threat comes from really basic security omissions, such as exposing debug ports or total lack of authentication. This makes it possible to compromise a whole range of devices with little investment.

### UK's secure by design

The [proposed regulation](https://www.gov.uk/government/collections/secure-by-design) in the United Kingdom has just three points:

* no universal default passwords,
* possibility to report vulnerabilities,
* a defined period for security updates.

These points are adapted from the more complete [ETSI 303 635](https://www.etsi.org/deliver/etsi_en/303600_303699/303645/02.01.01_60/en_303645v020101p.pdf) requirements. It bans the use of default passwords, and improves the communication about security between the vendor, consumers, and security researchers. However, it could really use some more technical requirements. Often, IoT devices have open ports than lack any access control, which these requirements don't cover. However, the regulation is simple and would much improve IoT security.

### Dutch Telecommunication Agency's minimum requirements

I participated in a research project for [Qbit](https://www.eurofins-cybersecurity.com/news/requirements-secured-iot-consumer-devices/) and the [Dutch Telecommunication Agency](https://www.agentschaptelecom.nl/actueel/nieuws/2020/08/26/acht-simpele-eisen-kunnen-de-cyberveiligheid-van-%E2%80%98slimme-apparatuur%E2%80%99-sterk-verbeteren), in which we determined that the following requirements make sense:

* Use strong passwords
* Use unique passwords
* Enforce authentication
* Restrict functionality to what's needed
* Encrypt network traffic
* Update firmware
* Verify firmware before installing
* Inform the end user about security

These requirements are a little more elaborate than the UK's mentioned above. But they are still fairly easy to implement, and you can buy devices today that meet all these requirements. The requirements in [the report](https://www.agentschaptelecom.nl/binaries/agentschap-telecom/documenten/rapporten/2020/08/26/onderzoeksrapport-essential-requirements-for-securing-iot-consumer-devices/Essential+requirements+for+securing+consumer+IoT+devices.pdf) are written more formally, which makes them suitable for legislation. Vendors, authorities, researches and consumers are likely to agree on the exact meaning of the requirements, and it is easy to determine whether a device meets the requirements. If IoT devices conform to these requirements, the security of them would improve greatly.

## Conclusion

Authorities are looking for security regulations for IoT consumer devices. The requirements need to be clear and basic, and leave room for vendors to innovate and sell new devices. However, they also need to improve the security of consumer IoT devices, which is currently in poor state.

## Read more

* [Essential requirements for securing IoT consumer devices](https://www.agentschaptelecom.nl/binaries/agentschap-telecom/documenten/rapporten/2020/08/26/onderzoeksrapport-essential-requirements-for-securing-iot-consumer-devices/Essential+requirements+for+securing+consumer+IoT+devices.pdf)