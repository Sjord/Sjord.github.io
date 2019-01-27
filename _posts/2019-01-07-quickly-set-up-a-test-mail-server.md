---
layout: post
title: "Quickly set up a test mail server"
thumbnail: improvised-mailbox-480.jpg
date: 2019-04-10
---

If you are setting up a web application on your own computer you sometimes need a simple mailserver that just lets you view emails, for example for a password reset mail. This article lists several solutions to quickly set up a test mailserver.

<!-- photo source: https://pixabay.com/en/scouting-tools-nature-camping-1146330/ -->

### Using Python

The easiest way is to use Python's built-in smtpd module. You can start a SMTP server that simply prints the received messages with this command:

    $ python -m smtpd --nosetuid --class DebuggingServer

That will open a SMTP server on port 8025, and simply print messages that look like this:

    ---------- MESSAGE FOLLOWS ----------
    To: test@example.com
    From: Sjoerd Langkemper <s.langkemper@itsec.nl>
    Subject: Test message
    Message-ID: <1706ee75-c447-f365-7513-06beea36488d@qbit.nl>
    Date: Sun, 27 Jan 2019 14:34:19 +0100
    User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:60.0)
    Gecko/20100101 Thunderbird/60.4.0
    MIME-Version: 1.0
    Content-Type: text/plain; charset=utf-8
    Content-Transfer-Encoding: 7bit
    Content-Language: nl
    X-Peer: 127.0.0.1

    Hello world
    ------------ END MESSAGE ------------

A more modern SMTP module is [aiosmtpd](https://aiosmtpd.readthedocs.io/en/latest/aiosmtpd/docs/cli.html), which can also be run on the command line to print email messages:

    $ python -m aiosmtpd -n

The modern aiosmtpd is prefered over the deprecated smtpd. The only disadvantage is that you have to install it, whereas smtpd comes bundled with Python.

* [Mailtrap](https://mailtrap.io/)
* [MailSlurper](http://mailslurper.com/)
* [smtp4dev](https://github.com/rnwood/smtp4dev)
* [Mailosaur](https://mailosaur.com/)
* [Dummy SMTP Server](https://github.com/enbiso/dummy-smtp-server)
* [Papercut](https://github.com/ChangemakerStudios/Papercut)
* [specifiedPickupDirectory Element](https://docs.microsoft.com/en-us/dotnet/framework/configure-apps/file-schema/network/specifiedpickupdirectory-element-network-settings)
* [MockSMTP.app](http://mocksmtpapp.com/)
* [DevNull SMTP](http://www.aboutmyip.com/AboutMyXApp/DevNullSmtp.jsp)
* [smtpd](https://docs.python.org/3/library/smtpd.html)
* [aiosmtpd](https://aiosmtpd.readthedocs.io/en/latest/)
* [MailDev](https://danfarrelly.nyc/MailDev/)
