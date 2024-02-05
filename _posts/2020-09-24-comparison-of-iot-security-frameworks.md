---
layout: post
title: "Comparison of IoT security frameworks"
thumbnail: scaffolding-480.jpg
date: 2020-09-24
---

Several IoT frameworks have been devised that can help vendors in
developing secure devices. These frameworks contain security measures to
follow during development, helping vendors to create a secure device.

<!-- Photo source: https://pixabay.com/photos/construction-site-construction-work-592458/ -->

*This article was published in September 2020 on the site of my employer at the time, Qbit. Since that site no longer exists, I published it again here in 2024.*

## Introduction

The Dutch Radiocommunications Agency is considering making several
[security measures legally required](/2020/09/30/iot-security-regulation/), and asked Qbit
which security measures would be best suited for
this. Qbit evaluated more than 400 security measures from four security
frameworks. This article describes the differences between these
security frameworks, and which can help you to best secure your IoT
device.

The following security frameworks are covered in this article:

-   [ETSI TS 303 645
    V2.1](https://www.etsi.org/deliver/etsi_en/303600_303699/303645/02.01.00_30/en_303645v020100v.pdf),
    provisions for the security of consumer devices that are connected
    to a network;
-   [IoT Security Compliance
    Framework](https://www.iotsecurityfoundation.org/best-practice-guidelines/),
    from the IoT Security Foundation;
-   [OWASP Internet of Things Security Verification Standard
    (ISVS)](https://owasp.org/www-project-iot-security-verification-standard/)
    provides security requirements for IoT applications;
-   [ENISA Baseline security recommendations for IoT in the context of
    Critical Information
    Infrastructures](https://www.enisa.europa.eu/publications/baseline-security-recommendations-for-iot).

## ETSI EN 303 645

The European Telecommunications Standards Institute (ETSI) specifies 65
security provisions for consumer IoT devices that are connected to a
network. The standard is meant for organisations involved in the
development and manufacturing of consumer IoT devices, i.e. vendors. As
such, it aims to provide a relatively complete set of requirements. The
requirements are less useful for testing a finished product; in a black
box test it is difficult to observe whether some provisions have been
implemented or not. Even so, it is a complete and usable set of
provisions, and it supports most provisions, with examples and
rationales provided.

The wording of the provisions is such that it makes it clear that ETSI
wants to avoid restricting devices to a specific technology or protocol.
For example, a requirement on passwords may prevent devices from using
an authentication mechanism that does not rely on passwords. Therefore,
EN 303 645 uses the term "authentication value" instead of "password".
Unfortunately, in certain cases this makes the provisions insufficiently
specific. For example, in the following provision there is a lot of room
for interpretation as to what cryptographic algorithm should be used:

> Provision 5.1-3 Authentication mechanisms used to authenticate users
against a device shall use best practice cryptography, appropriate to
the properties of the technology, risk and usage.

Overall, ETSI 303 645 is a practical, usable guide that provides vendors
with measures to secure IoT devices.

## IoT Security Compliance Framework

The IoT Security Foundation released the IoT Security Compliance
Framework, which comprises a set of 233 requirements.

Requirements are either mandatory or advisory, and are applicable to
certain device classes, which depend on the impact of a compromised
device. Devices where a hack would cause minor inconvenience is denoted
Class 0 and less security measures apply to such devices. Devices that
handle sensitive data are denoted Class 3, and for these most security
measures apply. As many devices handle sensitive data in some form, the
security requirements this framework imposes are pretty strict.

The division into classes largely ignores the indirect, societal impact
of attacks. Even if the device does not have strict security
requirements, it can still be used in a DDoS attack on a unrelated
website.

The framework contains many requirements that enforce a secure business
process, or require a secure design. This helps vendors to consider
security during the design process.

Even though these are good recommendations to help vendors secure
products, these requirements are less useful for black-box testing to
determine whether a device conforms to these requirements. For example,

> 2.4.5.38, "maintenance changes should trigger full security regression
testing",

applies more to the business process than to the functionality of the
device.

Even so, there are also many requirements that are sufficiently specific
and measurable. For example, one of the simplest and most important
requirements is

> 2.4.8.4: the product does not accept the use of null or blank
passwords.

The framework has a wide scope, and includes security requirements for
mobile applications, cloud services, the supply chain and the production
process. This causes several very similar requirements; passwords should
be secure for the IoT device, for the mobile application, for the web
interface, etc.

## OWASP ISVS

The OWASP Internet of Things Security Verification Standard (ISVS)
provides security requirements for Internet of Things (IoT)
applications. It is modelled after the Application Security Verification
Standard (ASVS), a standard that is growing in popularity for the
verification of security controls for web-applications and web services.

The ISVS is currently in the very early stages of development where the
latest public version is released as an appendix to the ASVS standard.
It consists of a list of 34 verification requirements that are
predominantly targeted at the technical security aspects of an IoT
application.

In its current form, as part of the ASVS, the ISVS defines three
assurance levels with increasing depth. This essentially means that an
IoT application is verified against more requirements when a higher
security level is selected. Level 1 requirements can be considered as
the bare minimum. The requirements at this level are typically easy to
verify. Level 2 introduces requirements that defend against the majority
of today's security risks. Level 3 is reserved for applications that
need a high level of assurance and require significant security
verification. Examples of such applications are in the area of military,
health, financial or critical infrastructures.

## ENISA Baseline Security Recommendations for IoT

The ENISA (European Union Agency for Cybersecurity) Baseline Security
Recommendations for IoT provides measures on three main categories:

- Policies
- Organisational, People and Process measures
- Technical measures

The measures regarding policies target the development process at the
vendor. Virtually all of these are insufficiently SMART when applied to
the end product of the process, the IoT device. The Organisational,
People and Process measures target the interaction between the vendor
and the consumer, and cover vulnerability disclosure, for example.
Finally, the [technical measures](/2019/05/08/enisa-iot-technical-measures/) provide the most concrete measures of
how the IoT device should behave.

Several of the measures that are included as a single point in the ENISA
document actually consist of several requirements. For example:

> GP-TM-18: Ensure that the device software/firmware, its configuration
and its applications have the ability to update Over-The-Air (OTA), that
the update server is secure, that the update file is transmitted via a
secure connection, that it does not contain sensitive data (e.g.
hardcoded credentials), that it is signed by an authorised trust entity
and encrypted using accepted encryption methods, and that the update
package has its digital signature, signing certificate and signing
certificate chain, verified by the device before the update process
begins.

This one measure consists of at least eight requirements. This makes it
difficult to categorize and evaluate.

The ENISA measures are meant to provide information on how to secure
devices. Several measures dictate that a specific part of the device
should be secure. For example:

> GP-TM-35: Cryptographic keys must be securely managed.

It is self-evident that for a device to be secure, all its subcomponents
need to be secure. However, for vendors that are unaware of how to
develop secure components, indicating that something must be secure may
be insufficient. For testers, it may even be unclear what level of
security is demanded, or against what kind of attack the system should
be secure. Most of these measures have been discarded as insufficiently
specific.

## Conclusion

Using any one of these frameworks can help to secure IoT devices. When
to use which framework? For vendors, we recommend the following:

-   Implement ETSI 303 645 for mature, well explained and specific
    instructions on how to achieve basic security in IoT devices.
-   If you want additional security, not only in the device but in the
    business process and the surrounding systems, use the IoT Security
    Compliance Framework.
-   If you want a checklist, or verify after development whether a
    product is secure, use the OWASP IoT Security Verification Standard.
-   If you want a less formal process, but are in need of good
    recommendations on how to secure your devices, consult the ENISA
    guidelines.

For hackers and testers, the OWASP ISVS has potential to be the best
match. It is specifically meant to provide a checklist of things to
verify when testing. However, it is also a immature project. As
alternative, ETSI 303 645 is sufficiently specific to be usable for
testers.
