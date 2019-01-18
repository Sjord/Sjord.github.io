---
layout: post
title: "ENISA Technical Measures for IoT"
thumbnail: cc2530-240.jpg
date: 2019-02-14
---

| §     | TM       | Description |
|-------|----------|-------------|
| 4.3.1 | GP&#8209;TM&#8209;01 | Employ a hardware-based immutable root of trust.
| 4.3.1 | GP&#8209;TM&#8209;02 | Use hardware that incorporates security features to strengthen the protection and integrity of the device – for example, specialised security chips / coprocessors that integrate security at the transistor level, embedded in the processor, providing, among other things, a trusted storage of device identity and authentication means, protection of keys at rest and in use, and preventing unprivileged from accessing to security sensitive code. Protection against local and physical attacks can be covered via functional security.
| 4.3.2 | GP&#8209;TM&#8209;03 | Trust must be established in the boot environment before any trust in any other software or executable program can be claimed.
| 4.3.2 | GP&#8209;TM&#8209;04 | Sign code cryptographically to ensure it has not been tampered with after signing it as safe for the device, and implement run-time protection and secure execution monitoring to make sure malicious attacks do not overwrite code after it is loaded.
| 4.3.2 | GP&#8209;TM&#8209;05 | Control the installation of software in operating systems, to prevent unauthenticated software and files from being loaded onto it.
| 4.3.2 | GP&#8209;TM&#8209;06 | Enable a system to return to a state that was known to be secure, after a security breach has occured or if an upgrade has not been successful.
| 4.3.2 | GP&#8209;TM&#8209;07 | Use protocols and mechanisms able to represent and manage trust and trust relationships.
| 4.3.3 | GP&#8209;TM&#8209;08 | Any applicable security features should be enabled by default, and any unused or insecure functionalities should be disabled by default.
| 4.3.3 | GP&#8209;TM&#8209;09 | Establish hard to crack, device-individual default passwords.
| 4.3.4 | GP&#8209;TM&#8209;10 | Personal data must be collected and processed fairly and lawfully, it should never be collected and processed without the data subject’s consent.
| 4.3.4 | GP&#8209;TM&#8209;11 | Make sure that personal data is used for the specified purposes for which they were collected, and that any further processing of personal data is compatible and that the data subjects are well informed.
| 4.3.4 | GP&#8209;TM&#8209;12 | Minimise the data collected and retained.
| 4.3.4 | GP&#8209;TM&#8209;13 | IoT stakeholders must be compliant with the EU General Data Protection Regulation (GDPR).
| 4.3.4 | GP&#8209;TM&#8209;14 | Users of IoT products and services must be able to exercise their rights to information, access, erasure, rectification, data portability, restriction of processing, objection to processing, and their right not to be evaluated on the basis of automated processing.
| 4.3.5 | GP&#8209;TM&#8209;15 | Design with system and operational disruption in mind, preventing the system from causing an unacceptable risk of injury or physical damage.
| 4.3.5 | GP&#8209;TM&#8209;16 | Mechanisms for self-diagnosis and self-repair/healing to recover from failure, malfunction or a compromised state.
| 4.3.5 | GP&#8209;TM&#8209;17 | Ensure standalone operation - essential features should continue to work with a loss of communications and chronicle negative impacts from compromised devices or cloud-based systems.
| 4.3.6 | GP&#8209;TM&#8209;18 | Ensure that the device software/firmware, its configuration and its applications have the ability to update Over-The-Air (OTA), that the update server is secure, that the update file is transmitted via a secure connection, that it does not contain sensitive data (e.g. hardcoded credentials), that it is signed by an authorised trust entity and encrypted using accepted encryption methods, and that the update package has its digital signature, signing certificate and signing certificate chain, verified by the device before the update process begins.
| 4.3.6 | GP&#8209;TM&#8209;19 | Offer an automatic firmware update mechanism.
| 4.3.6 | GP&#8209;TM&#8209;20 | Backward compatibility of firmware updates. Automatic firmware updates should not modify user-configured preferences, security, and/or privacy settings without user notification.
| 4.3.7 | GP&#8209;TM&#8209;21 | Design the authentication and authorisation schemes (unique per device) based on the system-level threat models.
| 4.3.7 | GP&#8209;TM&#8209;22 | Ensure that default passwords and even default usernames are changed during the initial setup, and that weak, null or blank passwords are not allowed.
| 4.3.7 | GP&#8209;TM&#8209;23 | Authentication mechanisms must use strong passwords or personal identification numbers (PINs), and should consider using two-factor authentication (2FA) or multi-factor authentication (MFA) like Smartphones, Biometrics, etc., on top of certificates.
| 4.3.7 | GP&#8209;TM&#8209;24 | Authentication credentials shall be salted, hashed and/or encrypted.
| 4.3.7 | GP&#8209;TM&#8209;25 | Protect against ‘brute force’ and/or other abusive login attempts. This protection should also consider keys stored in devices.
| 4.3.7 | GP&#8209;TM&#8209;26 | Ensure password recovery or reset mechanism is robust and does not supply an attacker with information indicating a valid account. The same applies to key update and recovery mechanisms.
| 4.3.8 | GP&#8209;TM&#8209;27 | Limit the actions allowed for a given system by Implementing fine-grained authorisation mechanisms and using the Principle of least privilege (POLP): applications must operate at the lowest privilege level possible.
| 4.3.8 | GP&#8209;TM&#8209;28 | Device firmware should be designed to isolate privileged code, processes and data from portions of the firmware that do not need access to them. Device hardware should provide isolation concepts to prevent unprivileged from accessing security sensitive code.
| 4.3.9 | GP&#8209;TM&#8209;29 | Data integrity and confidentiality must be enforced by access controls. When the subject requesting access has been authorised to access particular processes, it is necessary to enforce the defined security policy.
| 4.3.9 | GP&#8209;TM&#8209;30 | Ensure a context-based security and privacy that reflects different levels of importance.
| 4.3.9 | GP&#8209;TM&#8209;31 | Measures for tamper protection and detection. Detection and reaction to hardware
| 4.3.9 | GP&#8209;TM&#8209;32 | Ensure that the device cannot be easily disassembled and that the data storage medium is encrypted at rest and cannot be easily removed.
| 4.3.9 | GP&#8209;TM&#8209;33 | Ensure that devices only feature the essential physical external ports (such as USB) necessary for them to function and that the test/debug modes are secure, so they cannot be used to maliciously access the devices. In general, lock down physical ports to only trusted connections.
| 4.3.10 | GP&#8209;TM&#8209;34 | Ensure a proper and effective use of cryptography to protect the confidentiality, authenticity and/or integrity of data and information (including control messages), in transit and in rest. Ensure the proper selection of standard and strong encryption algorithms and strong keys, and disable insecure protocols. Verify the robustness of the implementation.
| 4.3.10 | GP&#8209;TM&#8209;35 | Cryptographic keys must be securely managed.
| 4.3.10 | GP&#8209;TM&#8209;36 | Build devices to be compatible with lightweight encryption and security techniques.
| 4.3.10 | GP&#8209;TM&#8209;37 | Support scalable key management schemes.
| 4.3.11 | GP&#8209;TM&#8209;38 | Guarantee the different security aspects -confidentiality (privacy), integrity, availability and authenticity- of the information in transit on the networks or stored in the IoT application or in the Cloud.
| 4.3.11 | GP&#8209;TM&#8209;39 | Ensure that communication security is provided using state-of-the-art, standardised security protocols, such as TLS for encryption.
| 4.3.11 | GP&#8209;TM&#8209;40 | Ensure credentials are not exposed in internal or external network traffic.
| 4.3.11 | GP&#8209;TM&#8209;41 | Guarantee data authenticity to enable reliable exchanges from data emission to data reception. Data should always be signed whenever and wherever it is captured and stored.
| 4.3.11 | GP&#8209;TM&#8209;42 | Do not trust data received and always verify any interconnections. Discover, identify and verify/authenticate the devices connected to the network before trust can be established, and preserve their integrity for reliable solutions and services.
| 4.3.11 | GP&#8209;TM&#8209;43 | IoT devices should be restrictive rather than permissive in communicating.
| 4.3.11 | GP&#8209;TM&#8209;44 | Make intentional connections. Prevent unauthorised connections to it or other devices the product is connected to, at all levels of the protocols.
| 4.3.11 | GP&#8209;TM&#8209;45 | Disable specific ports and/or network connections for selective connectivity.
| 4.3.11 | GP&#8209;TM&#8209;46 | Rate limiting. Controlling the traffic sent or received by a network to reduce the risk of automated attacks.
| 4.3.12 | GP&#8209;TM&#8209;47 | Risk Segmentation. Splitting network elements into separate components to help isolate security breaches and minimise the overall risk.
| 4.3.12 | GP&#8209;TM&#8209;48 | Protocols should be designed to ensure that, if a single device is compromised, it does not affect the whole set.
| 4.3.12 | GP&#8209;TM&#8209;49 | Avoid provisioning the same secret key in an entire product family, since compromising a single device would be enough to expose the rest of the product family.
| 4.3.12 | GP&#8209;TM&#8209;50 | Ensure only necessary ports are exposed and available.
| 4.3.12 | GP&#8209;TM&#8209;51 | Implement a DDoS-resistant and Load-Balancing infrastructure.
| 4.3.12 | GP&#8209;TM&#8209;52 | Ensure web interfaces fully encrypt the user session, from the device to the backend services, and that they are not susceptible to XSS, CSRF, SQL injection, etc.
| 4.3.12 | GP&#8209;TM&#8209;53 | Avoid security issues when designing error messages.
| 4.3.13 | GP&#8209;TM&#8209;54 | Data input validation (ensuring that data is safe prior to use) and output filtering.
| 4.3.14 | GP&#8209;TM&#8209;55 | Implement a logging system that records events relating to user authentication, management of accounts and access rights, modifications to security rules, and the functioning of the system. Logs must be preserved on durable storage and retrievable via authenticated connections.
| 4.3.15 | GP&#8209;TM&#8209;56 | Implement regular monitoring to verify the device behaviour, to detect malware and to discover integrity errors.
| 4.3.15 | GP&#8209;TM&#8209;57 | Conduct periodic audits and reviews of security controls to ensure that the controls are effective. Perform penetration tests at least biannually.

<script src="/scripts/tablefilter.js"></script>
