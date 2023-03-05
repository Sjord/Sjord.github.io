---
layout: post
title: "Quickly set up a test mail server"
thumbnail: improvised-mailbox-480.jpg
date: 2019-04-10
---

If you are setting up a web application on your own computer you sometimes need a simple mail server that just lets you view emails, for example for a password reset mail. This article lists several solutions to quickly set up a test mail server.

<!-- photo source: https://pixabay.com/en/scouting-tools-nature-camping-1146330/ -->

### Using Python

The easiest way is to use Python's built-in smtpd module. You can start a SMTP server that simply prints the received messages with this command:

    $ python -m smtpd --nosetuid --class DebuggingServer

That opens a SMTP server on port 8025, and simply prints messages that look like this:

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

A more modern SMTP module is [aiosmtpd](https://aiosmtpd.readthedocs.io/en/latest/aiosmtpd/docs/cli.html), which can be run to print email messages with the following command:

    $ python -m aiosmtpd -n

The modern aiosmtpd is prefered over the deprecated smtpd. The only disadvantage is that you have to install it, whereas smtpd comes bundled with Python.

### Using MailSlurper

[MailSlurper](https://www.mailslurper.com/) is a tool programmed in Go that opens a mail server and shows all mails through a web interface. The ports it uses are specified in a configuration file, `config.json`. It is pretty straightforward and easy to use, and it handles HTML emails better than the Python solution described above.

<img src="/images/mailslurper.png" width="100%" alt="MailSlurper screenshot">

### Using application configuration

If you are using a .NET application, you can configure mail to be stored in a specific directory instead of being sent out over SMTP. Using the [specifiedPickupDirectory](https://docs.microsoft.com/en-us/dotnet/framework/configure-apps/file-schema/network/specifiedpickupdirectory-element-network-settings) directive you can configure the directory to store your mails in.

Similarly, for Django there is a [console email backend](https://docs.djangoproject.com/en/dev/topics/email/#console-backend). Putting the following in the settings.py will display emails on the console instead of sending them:

    EMAIL_BACKEND = 'django.core.mail.backends.console.EmailBackend'

### More tools 

These tools set up a temporary email server on your own computer:

* [smtp4dev](https://github.com/rnwood/smtp4dev)
* [Dummy SMTP Server](https://github.com/enbiso/dummy-smtp-server)
* [Papercut](https://github.com/ChangemakerStudios/Papercut-SMTP)
* [DevNull SMTP](http://www.aboutmyip.com/AboutMyXApp/DevNullSmtp.jsp)
* [MailDev](https://maildev.github.io/maildev/)
* [MailCrab](https://tweedegolf.nl/en/blog/86/introducing-mailcrab)
* [Mailpit](https://github.com/axllent/mailpit)
* [Inbucket](https://github.com/inbucket/inbucket)

### Cloud services

These services provide a cloud-based email server for testing purposes:

* [Mailtrap](https://mailtrap.io/)
* [Mailosaur](https://mailosaur.com/)

### Conclusion

You can have your own mail server running within minutes, making it possible to test password resets and other functionality that sends email without using your actual mail server.
