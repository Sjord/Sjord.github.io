---
layout: post
title: "Should 2FA secrets be encrypted?"
thumbnail: double-key-480.jpg
date: 2023-02-01
---

<!-- Photo source: https://pixabay.com/nl/photos/berlijn-toets-sleutel-sluiten-zeker-96225/ -->

Time based OTP is commonly used for two-factor authentication. It works with a symmetric key. The server generates a key and stores it. It shows a QR code containing the key to the user, who then scans it and stores the key in their phone. The phone uses this key to generate a OTP, which the server can verify.

A client asked whether they should encrypt the OTP secret before storing it in the database. When the secret is not encrypted, anyone with access to the database can generate valid 2FA tokens. Does encrypting the 2FA token solve that? What threats and security considerations should be taken into account?

## Attack scenario

First, we came up with an attack scenario to reason about this question. The scenario is that someone has unauthorized access to the database, and wants to authenticate as a user to perform some action within the application.

Access to the database can be obtained with an SQL injection vulnerability in the application, or by access to a backup or database dump. Perhaps a developer debugging some production bug has part of the production database on his laptop, and leaves it laying around.

This attack scenario is already a bit unlikely. First of all, the attacker would also need passwords of users. He has the password hashes and can brute-force them offline, which is usually at least partially successful. Backups should be encrypted, and laptops should use disk-level encryption. In our situation, an SQL injection vulnerability would probably the most likely.

## Encrypting database fields

Assuming that there is a way to securely encrypt database fields, should that only be used on the 2FA secret? A much better candidate would be the password hash. Encrypting the password hash would stop the attack scenario described above. On top of that, the attacker can't recover passwords that they could reuse on other sites. If the password is reused between sites, it is more valuable than the 2FA secret, and thus a better candidate for encryption.

But why choose? Why shouldn't we encrypt all sensitive fields? Or all fields in the database? We could encrypt all fields in the database, and then make a layer that automatically decrypts fields. Seemingly, this would protect all data in the database. However, if there's a SQL injection vulnerability or something similar, the application would automatically decrypt the database contents, even if they are a result of an injected query.

Furthermore, it is pretty hard to store the decryption key somewhere safe. They application needs it to do its job, so it has to be around all the time.

## Asymmetric keys

Instead of using symmetric keys to generate OTPs, we could also use asymmetric keys. The phone stores the private key, and the server stores the public key. If the server is compromised, the public keys cannot be used to generate new OTPs, only to verify them. Unfortunately, with six-digit numeric OTPs, there is little distinction between these. An attacker can easily generate a valid OTP just by trying many different combinations and using the public key to verify them.

However, there are asymmetric solutions that are actually secure in this way. Hardware tokens (Yubikeys) can provide secure authentication, and are not compromised when the server's database is exposed. So this would be a far better solution than encrypting the TOTP secret.

## Password encryption

As said earlier, the application has to keep the encryption key around, so storing this in a secure manner is difficult. However, there is one secret that is available when the user authenticates, and can be forgotten soon after: the user's password. Using the user's password to encrypt the 2FA token would be a way to have encrypted 2FA secrets, without keeping the encryption key around.

Unfortunately, it does not prevent against our attack scenario. Remember, in our scenario, the attacker would have obtained the database dump, cracked some passwords, and generate the corresponding 2FA tokens to log in. When the 2FA secrets are encrypted with the passwords, this is still possible. The attacker can use the cracked passwords to decrypt the 2FA secrets.

## Securing the 2FA secret without encryption

To protect the 2FA secret, there are other options than encryption. For example, database accounts can be configured to segregate access between authentication and application data. The authentication functionality of the application could connect to the database with a different account than the rest of the application. If an SQL injection vulnerability is exploited elsewhere in the application, that user cannot access the credentials table.

Similarly, the authentication functionality can be implemented by another application component that has it's own database. We see this more and more where applications use third-party identity providers such as Okta or Auth0. But even an in-house solution can provide additional security by separating authentication from the rest of the application.

## Better opportunities for authentication security

There are many measures that can be taken that would improve security more than encrypting 2FA secrets. If users have secure passwords that are hashed with proper password hashing functions, the attacker will not be able to crack these and the 2FA secret doesn't even come into play. Encrypting 2FA secrets may be useful in some cases, but only after you have exhausted all other options to secure the authentication layer of the application.

## Conclusion

Time-based OTP was not designed to prevent access in the face of a database leak. Encrypting the OTP secrets does not really change that. In the end, it's better to take other measures to improve the security. Especially encouraging users to use secure passwords would go a long way. Here are some recommendations:

* Use drive-level encryption on all systems.
* Use proper password hashes and configure them to be sufficiently slow.
* When users configure their passwords, deny passwords that are known to be weak or are part of a previous breach. The [HIBP API](https://haveibeenpwned.com/API/v3) makes it possible to verify passwords without having to store gigabytes of lists.
* Support hardware keys (through [WebAuthn](https://en.wikipedia.org/wiki/WebAuthn)) to provide better authentication security than TOTP.
* Notify users of unusual logins.
