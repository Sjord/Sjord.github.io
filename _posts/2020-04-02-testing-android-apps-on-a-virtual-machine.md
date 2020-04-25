---
layout: post
title: "Testing Android apps on a virtual machine"
thumbnail: lab-480.jpg
date: 2020-05-06
---


## Obtaining an Android virtual machine

OSBoxes has pre-built [Android VM images](https://www.osboxes.org/android-x86/), for VMWare and VirtualBox.

Alternatively, you can build your own virtual machine from an ISO:

1. Download an ISO from [Android-x86](https://www.android-x86.org/). 
    There are several versions such as 7.1-r3, which indicates the third release of Android 7.1.
    Pick a 64-bit ISO. Some versions have an ISO marked "k49". This means kernel version 4.9, and this is recommended for VMWare installations.
2. Create a new VM. Choose the just downloaded ISO as the disk image to install from. Use the following settings:
      *  OS: Linux kernel 64-bit
      *  Memory: at least 2.5 GB
      *  Display: Accelerate 3D Graphics 
      *  TODO disable suspend
      *  Hard disk: at least 7 GB
3. Boot the VM. Select "Installation" to start the installation process, and follow the steps in the [installation howto](https://www.android-x86.org/installhowto.html).
4. Reboot after installation.
5. Finish installation within Android.
6. Create a snapshot, so that you can revert to a clean installation if you want.

## Troubleshooting

* Android turns the screen off after several minutes. Use "Send Ctrl-alt-delete" in VMWare, which behaves as the power button, to wake up Android.
* If you have trouble accessing VMWare's disk, change the disk type from SCSI to IDE.
* Connecting to your virtual machine over the virtual network may not work if you also have a VPN application running.
* If the screen stays black when booting, enable graphics hardware acceleration. Or add the following kernel options when booting: `nomodeset xforcevesa`.

## Connect with ADB

The tool `adb`, the Android debug bridge, is useful to let the Android VM do what we want. First, you have to put Android in developer mode. Open the system information in Android's settings and click the build number several times. This will enable developer mode and make it possible to connect with `adb`.

Of course, we want `adb` to connect over the network, since we have no USB cable to our virtual machine. Find out the IP address of the Android VM by checking the Wi-Fi preferences. Alternatively, open the terminal emulator and type `ip a` or `ifconfig`. When you know the IP address, you can connect `adb` with this command:

    $ adb connect 172.16.122.232

## Useful ADB commands

Install apps from an APK file with the following command:
TODO what if you have two apks?

    adb install app.apk

The following command sets a proxy server. This does not show up in Android's Wi-Fi settings. This setting is applied immediately, and persists after reboot. Apps that have certificate pinning, such as the Play Store, no longer work after settings a proxy.

    adb shell settings put global http_proxy 172.16.122.1:8008

These commands unsets the proxy server. It is only applied after reboot, which is performed by the last command.

    adb shell settings delete global http_proxy
    adb shell settings delete global global_http_proxy_host
    adb shell settings delete global global_http_proxy_port
    adb shell reboot

Enter text. The following command types "hello" in the currently focussed input field. The parameter containing the text to type needs to be escaped (TODO how?).

    adb shell input text "hello"

Reboot Android:

    adb shell reboot

Retrieve a APK of an installed app:

    adb shell pm list packages
    adb shell pm path nl.ns.android.activity
    adb pull /data/app/nl.ns.android.activity-1/base.apk

## Frida

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

## Todo

* Chrome zegt dat certificaat te lang geldig is. Is specifiek voor Chrome.
* ShinePhone doesn't show up in VM Play Store
* Errors in Dashboard Event Log in Burp als het cert niet goed is.
