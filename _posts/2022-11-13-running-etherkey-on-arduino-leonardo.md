---
layout: post
title: "Running Etherkey on a Arduino Leonardo clone to emulate a keyboard"
thumbnail: leonardo-keyboard-480.jpg
date: 2022-11-16
---


## Computer to keyboard interface

Besides my work laptop, I have a couple of client laptops. The clients I perform code reviews for arranged a developer laptop for me so that I can access the source code. These laptops are managed by the client and are sometimes pretty restricted in what software can be installed on them. Even so, I want to automate stuff. I want to use the password manager on my work laptop to store passwords for these client laptops. I don't want to manually copy these passwords from one laptop to another, so I came up with a solution to emulate an USB keyboard.

My work laptop sends instructions over UART to a microcontroller that pretends to be a USB keyboard, and this way I can automatically enter data into the client laptop without installing any additional software on there. It works great.

## Current setup with Teensy

At my last job I got a [Teensy 3.2](https://www.pjrc.com/store/teensy32.html) as a gift. This is a pretty powerful microcontroller that can also pretend to be a USB keyboard. As software I use [Etherkey](https://github.com/Flowm/etherkey), which is made for this job. It converts input on the serial port to key presses on the virtual keyboard. To connect my laptop to the Teensy I use a [cheap USB to UART serial converter](/2019/03/20/usb-to-serial-uart/).

<img src="/images/etherkeysetup.png" width="100%">

## Problems with the Teensy

This setup worked great and I used it regularly, primarily to enter passwords in the right places. Except when it didn't anymore. My Teensy seemed dead, and no longer presented itself as a USB device. This triggered me to search for a replacement.

Later I discovered the problem with my Teensy was its USB connector. I soldered a new USB cable to it and it worked again. 

## Arduino Leonardo clone

I bought two cheap [development boards on AliExpress](https://www.aliexpress.com/item/32617886318.html?spm=a2g0o.order_list.0.0.5d0b1802NLLDnr). I paid about €11 per piece. These have a ATmega32u4 microcontroller on board, which has the functionality to emulate a USB keyboard.

Arduino also provides a board with a ATmega32u4, the [Arduino Leonardo](https://docs.arduino.cc/hardware/leonardo). So these ATmega32u4 development boards are pretty well supported by the Arduino software. The pinout is different from the official board though.

## Getting Etherkey to work

I already used Etherkey on the Teensy. It's job is to read commands on the serial UART interface, and press the corresponding keys on the virutal keyboard. Etherkey thus far only supported Teensy 3.2. I had no idea how hard it would be to make it work on my Leonardo clone, but it turned out to be pretty easy. There were three steps:

1. Inlude the `Keyboard.h` header file.
2. Map Leonardo key names to Teensy key names. For example, Teensy uses `KEY_ENTER` and Leonardo uses `KEY_RETURN`.
3. Disable the detection of keyboard LEDs. The Teensy has support to detect whether num-lock is on or off, but unfortunately Arduino [doesn't](https://github.com/arduino-libraries/Keyboard/issues/43) [have](https://github.com/arduino-libraries/Keyboard/issues/40) [this](https://github.com/arduino/ArduinoCore-avr/pull/446) [yet](https://github.com/arduino-libraries/Keyboard/pull/61).

All in all, the following shows the changes:

```c
#ifdef ARDUINO_AVR_LEONARDO
#include "Keyboard.h"

#define KEY_UP KEY_UP_ARROW
#define KEY_DOWN KEY_DOWN_ARROW
#define KEY_RIGHT KEY_RIGHT_ARROW
#define KEY_LEFT KEY_LEFT_ARROW

#define KEYPAD_PLUS KEY_KP_PLUS
#define KEYPAD_0 KEY_KP_0

#define KEY_ENTER KEY_RETURN
#define KEY_SPACE ' '

#define MODIFIERKEY_CTRL KEY_LEFT_CTRL
#define MODIFIERKEY_ALT KEY_LEFT_ALT
#define MODIFIERKEY_SHIFT KEY_LEFT_SHIFT
#define MODIFIERKEY_GUI KEY_LEFT_GUI

#define keyboard_leds 0
#endif
```
