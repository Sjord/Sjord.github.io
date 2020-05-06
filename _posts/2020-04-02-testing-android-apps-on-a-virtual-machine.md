---
layout: post
title: "Testing Android apps on a virtual machine"
thumbnail: lab-480.jpg
date: 2020-05-06
---

A virtual machine running Android is useful when hacking Android apps. In this post I describe my experiences with setting up a virtual machine and intercepting traffic from Android apps.

## Introduction

Occasionally I test web applications that also have an Android client. Intercepting the traffic from an Android app gives insight in what APIs the app uses, which in turn can expose vulnerabilities.

Testing on a virtual machine (VM) has some disadvantages. Testing on an actual Android phone is more reliable. Android is meant to run on ARM phones and not on x86 virtual machines, so things may randomly break when using a VM. Apps that ship with native libraries may not run at all in the VM, or they may run perfectly but don't show up in the Play store.

The advantage is that you don't need an actual phone to analyze an Android app. Restoring snapshots and interacting with the app are easier than with a phone. With the VM you get full access without "rooting" the device. A virtual machine is much faster than an emulator, since it doesn't need to translate the machine instructions.

So if it works, it's great, but it doesn't always work.

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
      *  Hard disk: at least 7 GB
3. Boot the VM. Select "Installation" to start the installation process, and follow the steps in the [installation howto](https://www.android-x86.org/installhowto.html).
4. Reboot after installation.
5. Finish installation within Android.
6. Create a snapshot, so that you can revert to a clean installation if you want.

## Troubleshooting

* Android turns the screen off after several minutes. Use "Send Ctrl-alt-delete" in VMWare, which behaves as the power button, to wake up Android. Or use `adb shell input keyevent KEYCODE_POWER` to virtually press the power button.
* If you have trouble accessing VMWare's disk, change the disk type from SCSI to IDE.
* Connecting to your virtual machine over the virtual network may not work if you also have a VPN application running.
* If the screen stays black when booting, enable graphics hardware acceleration. Or add the following kernel options when booting: `nomodeset xforcevesa`.

## Connect with ADB

The tool `adb`, the Android debug bridge, is useful to let the Android VM do what we want. First, you have to put Android in developer mode. Open the system information in Android's settings and click the build number several times. This will enable developer mode and make it possible to connect with `adb`.

Of course, we want `adb` to connect over the network, since we have no USB cable to our virtual machine. Find out the IP address of the Android VM by checking the Wi-Fi preferences. Alternatively, open the terminal emulator and type `ip a` or `ifconfig`. When you know the IP address, you can connect `adb` with this command:

    $ adb connect 172.16.122.232

## Useful ADB commands

Install apps from an APK file with the following command:

    adb install app.apk

The following command spawns an interactive shell. Any arguments after `adb shell` are executed within the VM.

    adb shell

The following command sets a proxy server. This does not show up in Android's Wi-Fi settings. This setting is applied immediately, and persists after reboot. Apps that have certificate pinning, such as the Play Store, no longer work after settings a proxy.

    adb shell settings put global http_proxy 172.16.122.1:8008

These commands unsets the proxy server. It is only applied after reboot, which is performed by the last command.

    adb shell settings delete global http_proxy
    adb shell settings delete global global_http_proxy_host
    adb shell settings delete global global_http_proxy_port
    adb shell reboot

Enter text. The following command types "hello" in the currently focussed input field. The parameter containing the text to type needs to be escaped twice. That's because you type it in a shell, and adb passes it to the shell within Android. This puts *hello* within single quotes twice:

    adb shell input text \''hello'\'

To press certain buttons, use `keyevent`. A list of valid keys can be found in the [Android KeyEvent documentation](https://developer.android.com/reference/android/view/KeyEvent). To press the volume up button:

    adb shell input keyevent KEYCODE_VOLUME_UP

To reboot Android:

    adb shell reboot

Retrieve a APK of an installed app:

    adb shell pm list packages
    adb shell pm path nl.ns.android.activity
    adb pull /data/app/nl.ns.android.activity-1/base.apk

## Disable certificate checks

To intercept traffic from the Android app, we need it to communicate with our intercepting proxy. If the app has any security at all, it will not trust the proxy's certificate and refuse to connect. There are two ways to solve this:

* [Install the proxy's certificate as system certificate](https://blog.jeroenhd.nl/article/android-7-nougat-and-certificate-authorities). Installing it as user certificate may not be sufficient to intercept app traffic.
* Disable certificate checks altogether using Frida and Objection. This also bypasses any certificate pinning.

When working with certificates, keep in mind that the policy may differ between apps. Chrome on Android trusts user certificates, but not certificates that have a long validity. However, this is specific to Chrome and doesn't apply to other apps. So testing whether your setup works with Chrome is not reliable.

### Disable certificate checks

Frida can hook into apps and change the implementation of one or more functions. We are going to use this to change the certificate check function, to always return that the certificate is trusted. This disables any certificate pinning, and the app will trust our proxy's certificate.

First, set up Frida. Download the correct [frida-server release](https://github.com/frida/frida/releases), and run it on the phone as root

    wget https://github.com/frida/frida/releases/download/12.8.20/frida-server-12.8.20-android-x86_64.xz -d frida-server-12.8.20-android-x86_64.xz
    adb push frida-server-12.8.10-android-x86_64 /data/local/tmp
    adb shell
    su
    /data/local/tmp/frida-server-12.8.10-android-x86_64 -l 0.0.0.0

The `-l` argument is needed because we want to connect over the network, and Frida normally only listens on localhost. Next, we install the client tools. I like to do that in a virtualenv:

    python3 -m venv venv
    . venv/bin/activate
    pip3 install frida-tools objection

Test it by running frida-ps. Remember that you need to pass the IP address every time you invoke Frida:

    frida-ps -H 172.16.122.225

Next, start objection on an app, and disable pinning:

    objection --network -h 172.16.122.225 -g nl.ns.android.activity explore
    # android sslpinning disable

Now, you are good to intercept some traffic.

## Conclusion

Testing Android apps in a virtual machine is possible. If it works it is pretty easy, but since Android is not really supported on x86, things may break in unexpected ways. Testing on an actual device is more reliable.
