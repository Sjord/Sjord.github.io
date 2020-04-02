---
layout: post
title: "Testing Android apps on a virtual machine
thumbnail: lab-480.jpg
date: 2020-05-06
---

Download VM image from https://www.osboxes.org/android-x86/

1. Download from https://www.android-x86.org/
    download 64-bit ISO
    android-x86_64
    r3 is recommended
    k49 is kernel 4.9 == Recommended for VMware users
    We download android-x86_64-8.1-r3-k49.iso
2. Create new VM
    Install from disk or image, the ISO we just downloaded.
    Cdustomize settings.
        OS: Other Linux kernel 64-bit
        Memory: at least 2.5 GB
        Display: Accelerate 3D Graphics
        Hard disk: at least 7 GB
3. Boot VM.
    Select Installation
    Do you want to install /system directory as read-write? Yes
4. Reboot after installation
5. Finish installation
6. Create snapshot.


Screen black? Send Ctrl-alt-delete
Lookup IP address under Wi-Fi preferences. 172.16.122.232
Tap build number several times to enable developer mode.
$ adb connect 172.16.122.232
$ adb install scouting_v35.apk
$ adb shell settings put global http_proxy 172.16.122.1:8008
    Dit is meteen van kracht, en dan doet je play store het dus niet meer. En blijft van kracht na reboot.
    Maar de ScoutingApp wel!
    Errors in Dashboard Event Log in Burp als het cert niet goed is.


"Send Ctrl-Alt-Del" werkt gewoon als power button
ADB werkt niet in samenwerking met VPN
adb connect 172.16.122.225

"Send Ctrl-Alt-Del" werkt gewoon als power button
ADB werkt niet in samenwerking met VPN

adb connect 172.16.122.225
adb shell reboot

* Text input:
    adb shell input text ""
    Dit moet dubbel geescaped worden.

* Pull package
    adb shell pm list packages
    adb shell pm path nl.ns.android.activity
    adb pull /data/app/nl.ns.android.activity-1/base.apk

* Frida
    https://github.com/frida/frida/releases
    frida-server-12.8.10-android-x86_64.xz
    adb push frida-server-12.8.10-android-x86_64 /data/local/tmp
    # adb shell "chmod 755 /data/local/tmp/frida-server"
    su
    /data/local/tmp/frida-server-12.8.10-android-x86_64
    Frida luistert normaal alleen op 127.0.0.1
    ./frida-server-12.8.10-android-x86_64 -l 0.0.0.0

    pip install frida-tools
    pip install objection
    frida-ps -H 172.16.122.225
    objection --network -h 172.16.122.225 -g nl.ns.android.activity explore

Chrome zegt dat certificaat te lang geldig is. Is specifiek voor Chrome.


* Kernel options: nomodeset xforcevesa

Proxy aan:
    Dit werkt meteen:
    adb shell settings put global http_proxy <address>:<port>
    adb shell settings put global http_proxy 192.168.2.1:8008
    adb shell settings put global http_proxy 172.16.122.1:8008


Proxy uit:
    Dit werkt alleen na reboot:
    adb shell settings delete global http_proxy
    adb shell settings delete global global_http_proxy_host
    adb shell settings delete global global_http_proxy_port
    adb shell reboot

* IP adres: Wi-Fi preferences, Advanced    
    172.16.122.232
    adb connect 172.16.122.232

* Install APK;
    adb install ShineWifi.apk

    ShinePhone doesn't show up in VM Play Store
