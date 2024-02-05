---
layout: post
title: "The most important security problems with IoT devices"
thumbnail: pcb-480.jpg
date: 2020-09-10
---

In this article, we list the eleven most important security issues faced
by IoT devices. We will review them from the most severe though to the
least. It is worth noting that the six most severe issues were used in
an actual attack, or to demonstrate proof-of-concept of a vulnerability.

*This article was published in September 2020 on the site of my employer at the time, Qbit. Since that site no longer exists, I published it again here in 2024.*

The security of IoT devices has been a cause for concern for some time
and has had the inevitable consequence of allowing both small- and
large-scale attacks. Most of these attacks originate from simple
security problems, for example, the retention of default passwords on a
telnet service. The Dutch Radiocommunications Agency wants to impose
security requirements on IoT devices and their manufacturers, and asked
Qbit for advice.

## 1. Incorrect access control

Services offered by an IoT device should only be accessible by the owner
and the people in their immediate environment whom they trust. However,
this is often insufficiently enforced by the security system of a
device.

IoT devices may trust the local network to such level that no further
authentication or authorisation is required. Any other device that is
connected to the same network is also trusted. This is especially a
problem when the device is connected to the Internet: everyone in the
world can now potentially access the functionality offered by the
device.

A common problem is that all devices of the same model are delivered
with the same default password (e.g. "admin" or "password123"). The
firmware and default settings are usually identical for all devices of
the same model. Because the credentials for the device -- assuming that,
as is often the case, they are not changed by the user - are public
knowledge, they can be used to gain access to all devices in that
series.

IoT devices often have a single account or privilege level, both exposed
to the user and internally. This means that when this privilege is
obtained, there is no further access control. This single level of
protection fails to protect against several vulnerabilities.

## 2. Overly large attack surface

Each connection that can be made to a system provides a new set of
opportunities for an attacker to discover and exploit vulnerabilities.
The more services a device offers over the Internet, the more services
can be attacked. This is known as the attack surface. Reducing the
attack surface is one of the first steps in the process of securing a
system.

A device may have open ports with services running that are not strictly
required for operation. An attack against such an unnecessary service
could easily be prevented by not exposing the service. Services such as
Telnet, SSH or a debug interface may play an important role during
development but are rarely necessary in production.

## 3. Outdated software

As vulnerabilities in software are discovered and resolved, it is
important to distribute the updated version to protect against the
vulnerability. This means that IoT devices must ship with up-to-date
software without any known vulnerabilities, and that they must have
update functionality to patch any vulnerabilities that become known
after the deployment of the device.

For example, the malware Linux.Darlloz was first discovered late 2013,
and worked by exploiting a bug reported and fixed more than a year
earlier.

## 4. Lack of encryption

When a device communicates in plain text, all information being
exchanged with a client device or backend service can be obtained by a
'Man-in-the-Middle' (MitM). Anyone who is capable of obtaining a
position on the network path between a device and its endpoint can
inspect the network traffic and potentially obtain sensitive data such
as login credentials. A typical problem in this category is using a
plain-text version of a protocol (e.g. HTTP) where an encrypted version
is available (HTTPS). A Man-in-the-Middle attack where the attacker
secretly accesses, and then relays communications, possibly altering
this communication, without either parties being aware.

Even when data is encrypted, weaknesses may be present if the encryption
is not complete or configured incorrectly. For example, a device may
fail to verify the authenticity of the other party. Even though the
connection is encrypted, it can be intercepted by a Man-in-the-Middle
attacker.

Sensitive data that is stored on a device (at rest) should also be
protected by encryption. Typical weaknesses are lack of encryption by
storing API tokens or credentials in plain text on a device. Other
problems are the usage of weak cryptographic algorithms or using
cryptographic algorithms in unintended ways.

## 5. Application vulnerabilities

Acknowledging that software contains vulnerabilities in the first place
is an important step in securing IoT devices. Software bugs may make it
possible to trigger functionality in the device that was not intended by
the developers. In some cases, this can result in the attacker running
their own code on the device, making it possible to extract sensitive
information or attack other parties.

Like all software bugs, security vulnerabilities are impossible to avoid
completely when developing software. However, there are methods to avoid
well-known vulnerabilities or reduce the possibility of vulnerabilities.
This includes best practices to avoid application vulnerabilities, such
as consistently performing input validation.

## 6. Lack of Trusted Execution Environment

Most IoT devices are effectively general-purpose computers that can run
specific software. This makes it possible for attackers to install their
own software that has functionality that is not part of the normal
functioning of the device. For example, an attacker may install software
that performs a DDoS attack. By limiting the functionality of the device
and the software it can run, the possibilities to abuse the device are
limited. For example, the device can be restricted to connect only to
the vendor's cloud service. This restriction would make it ineffective
in a DDoS attack since it can no longer connect to arbitrary target
hosts.

To limit the software a device can run, code is typically signed with a
cryptographic hash. Since only the vendor has the key to sign the
software, the device will only run software distributed by the vendor.
This way, an attacker can no longer run arbitrary code on a device.

To totally restrict the code run on the device, code signing must also
be implemented in the boot process, with the help of hardware. This can
be difficult to implement correctly. So called 'jailbreaks' in devices
such as the Apple iPhone, Microsoft Xbox and Nintendo Switch are the
result of errors in the implementation of trusted execution
environments.

## 7. Vendor security posture

When security vulnerabilities are found, the reaction of the vendor
greatly determines the impact. The vendor has a role to receive input on
potential vulnerabilities, develop a mitigation, and update devices in
the field. The vendor security posture is often determined by whether
the vendor has a process in place to adequately handle security issues.

The consumer mainly perceives the vendor security posture as improved
communication with the vendor in relation to security. When a vendor
does not provide contact information or instructions how to take action
in case of reporting a security issue, it will likely not help to
mitigate the issue.

Without knowledge of limitations, end users will continue to use the
device in the method intended. This may result in a less secure
environment. Vendors could make things easier for customers by advising
of the frequency of device security updates, and how to securely dispose
or resell the device so that sensitive data is not passed on.

## 8. Insufficient privacy protection

Consumer devices typically store sensitive information. Devices that are
deployed on a wireless network store the password of that network.
Cameras can provide a video and audio recording of the home in which
they are deployed. If this information were accessed by attackers, it
would amount to a severe privacy violation.

IoT devices and related services should handle sensitive information
correctly, securely, and only after consent of the end-user of the
device. This applies to both storage and distribution of sensitive
information.

In case of privacy protection, the vendor plays an important role. Other
than an external attacker, the vendor or an affiliated party may be
responsible for a privacy breach. The vendor or service provider of an
IoT device could, without explicit consent, gather information on
consumer behaviour for purposes like market research. Several cases are
known where IoT devices, for instance smart televisions, may be
listening in on conversations within a household.

## 9. Intrusion ignorance

When a device is compromised, it often keeps functioning normally from
the viewpoint of the user. Any additional bandwidth or power usage is
usually not detected. Most devices do not have logging or alerting
functionality to notify the user of any security problems. If they have,
these can be overwritten or disabled when the device is hacked. The
result is that users rarely discover that their device is under attack
or has been compromised, preventing them from taking mitigating
measures.

## 10. Insufficient physical security

If attackers have physical access to a device, they can open the device
and attack the hardware. For example, by reading the contents of the
memory components directly, any protecting software can be bypassed.
Furthermore, the device may have debugging contacts, accessible after
opening up the device, that provide an attacker with additional
possibilities.

Physical attacks have an impact on a single device and require physical
interaction. Since it not possible to perform these attacks en-masse
from the Internet, we do not recognize this as one of the biggest
security problems, but it is nevertheless included.

A physical attack can be impactful if it uncovers a device key that is
shared amongst all devices of the same model, and thus compromises a
wide range of devices. However, in that case we consider sharing the key
amongst all devices to be the more important problem, not physical
security.

## 11. User interaction

Vendors can encourage secure deployment of their devices by making it
easy to configure them securely. By giving proper attention to
usability, design, and documentation, users can be nudged into
configuring secure settings.

There is partial overlap between this category and other categories
listed above. For example, the problem of incorrect access control
mentioned above includes using unsafe or default passwords. One way to
solve this is to make the user interaction with the device such that it
is very easy or even mandatory to configure a secure password.

For most of the above security categories, it is difficult for a
non-technical user to evaluate whether a device meets the requirement.
However, user interaction can, by definition, be perceived by the
end-user, and so the consumer can evaluate how well a device performs on
user interaction.

User interaction is an important category to make sure implemented
security measures are activated and correctly used. If it is possible to
change the default password, but the user does not know or cannot
discover the functionality, it is useless.

## Conclusion

The top security problems are without a doubt related to access control
and exposed services. Furthermore, IoT devices should implement
best-practice security measures such as encryption. Vendors can
facilitate secure use of their products by providing documentation and
interacting with users and security professionals. To make it harder for
attackers, devices should be physically secured. Finally, if a device is
compromised it should reject programs supplied by the attacker, and
notify its user that something is wrong.

Focussing on these problems can certainly improve the state of security
of IoT devices. To solve these problems, Qbit recommends vendors to
follow a security framework, or at least implement the eight proposed
essential requirements for securing consumer IoT devices. We will
examine [security frameworks in part 3 of this blog](/2020/09/24/comparison-of-iot-security-frameworks/).
