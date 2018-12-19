---
layout: post
title: "USB to UART serial"
thumbnail: ch340-480.jpg
date: 2019-05-22
---

TODO

<!-- photo source: own work -->

## Introduction

TODO
These chips are sometimes called USB to UART bridges

## Applications

From a hacking perspective, the most interesting application of UARTs is in embedded devices. Most embedded devices have a UART header on the board. The device sends console output and accepts commands over the UART interface. Often, this gives direct access to a root shell.

UART communication is sometimes also needed to interact with a development board, such as an Arduino or ESP8266, although most of these boards have a USB toq serial converter on board.

Some other protocols are built upon UART communication, such as [IrDA](https://en.wikipedia.org/wiki/Infrared_Data_Association), [DMX](https://en.wikipedia.org/wiki/DMX512), [MIDI](https://en.wikipedia.org/wiki/MIDI) and [smart meter P1 ports](https://www.netbeheernederland.nl/_upload/Files/Slimme_meter_15_a727fce1f1.pdf). These can also be used with USB to UART bridges, but require some further hacking to get working.

## UART protocol

When two devices communicate using UART, they are connected with at least three wires:

* Common ground, or 0V, or the negative lead of the power supply.
* The transmitting pin (Tx) of one device is connected to the receiving pin (Rx) of the other device.
* Similarly, the Rx is connected to the Tx.

Now, the devices can send data to each other by varying the voltage on the Tx lines, and read data by checking the voltage on the Rx line. UART uses a binary protocol, so there are only two voltage levels: high and low.

There is no clock signal and no negotiation between the two devices. To correctly communicate, both devices must be configured beforehand to use the same speed of communication, called the baud rate. 

### Baud rate

The baud rate is a term for the number of bits per second that are transmitted over the wire. A common baud rate is 9600. In that case, one bit takes up <sup>1</sup>&#8725;<sub>9600</sub> of a second, or 104µs. 

The sending party flips the signal every 104µs, and the receiving party checks the voltage on the line every 104µs. It will still work if this is off by a couple of per cent. This sometimes happens with microcontrollers, that have trouble keeping an exact clock.

The most common baud rates in use are 9600 and 115200. Then there are a handful of standard baud rates, such as 19200 and 38400. In theory you can use any baud rate, but some interfaces only support the standard baud rates.

<img src="/images/uart-logic-680.png" alt="A pulse is 104µs long">

### Start, stop and parity bits

When sending data, the transmitting party usually first sends a start bit, then 8 data bits, followed by a stop bit.
UART frames consist of a start bit, 7 or 8 data bits, optionally a parity bit and one or two stop bits. By far the most common configuration is to use 8 data bits, no parity bit and one stop bit, or 8N1.

### Voltage levels

On a UART line, high voltage indicates a 1 bit and low voltage indicates a 0 bit. What voltage exactly is used depends on the device.

* RS232 serial ports use negative and positive voltages, up to -15V and 15V.
* Some devices use 0V and 5V, such as an Arduino Uno that runs on 5V.
* Most devices use 0V and 3.3V.
* Some devices use 0V and 1.8V.

Voltage that ranges between 0 and power supply voltage (Vcc) is also called TTL voltage levels. To avoid frying your device, it is important to use the correct voltage.

## USB to UART converter module

To let your computer talk UART, you need a device that converts computer bytes to UART signals; a USB to UART converter module. This is a small device that plugs into your USB port and has at least ground, Rx and Tx outputs. It pretends to be a serial port to your computer. The computer sends data to this serial port and the module converts it to UART signals.

<img src="/images/ch340-480.jpg" width="480">

### Chip differences

A USB to UART bridge has a chip on it specifically for this purpose. There are several commonly used chips:

* WCH CH340
* Silicon Labs CP2102
* Prolific PL2303
* FTDI FT232

The FTDI is around the longest and was previously the only implementation available for USB to UART bridges. They were so common that a bridge was sometimes called a FTDI, after the company name that made the converter chip. Nowadays, they are quickly taken over by much cheaper Chinese converter chips.

CH340 €0.25
CP2102 €0.75
PL2303 €0.25
FTDI FT232 €3.50

* Arduino has custom chip
    Makes it possible to identify as Arduino to the computer
    Makes it possible to change the USB device later

#### Fake FTDI chips

FTDI FT232 chips are pretty expensive (€3.50) and became popular quickly. This led to the rise of fake Chinese knock-offs. These imitations have the FTDI logo on them and work correctly, and are [hard to tell from fakes](https://zeptobars.com/en/read/FTDI-FT232RL-real-vs-fake-supereal).

FTDI was not happy with this. In 2014 they pushed a driver update that only works with real FTDI chips, and [bricked counterfeit chips](https://en.wikipedia.org/wiki/FTDI#Driver_controversy). Despite that, fake FT232 chips are still widely available.

#### Buyer's guide

When picking a USB to UART bridge, keep these things in mind:

* Voltage level. Determine the voltage level you want to use. Some bridges support both 3.3V and 5V, and there is a little jumper to switch voltage.
* Drivers. Check whether the bridge you want to buy has drivers for your platform.
* Blinking LEDs. They look cool and they indicate what is going on.
* USB connector. Some bridges plug right into your computer, but it is often nice to have a USB cable between your computer and your bridge so that you have some more room on your desk. Bridges with mini-USB sockets are pretty common, but I prefer micro-USB sockets.
* Features. Do you need special features such as inverted signals? Check the data sheet of the chip.
* Speed. Do you need particularly fast or uncommon speeds? Check the data sheet of the chip.

I think the best chip is the FTDI FT232. It is also the most expensive and it is hard to determine whether you have a legitimate chip or a cheap knock-off.

For normal UART usage, any chip is fine. With the cheapest bridge on AliExpress (€0.500 you can talk to embedded devices just fine.

### Drivers

Your computer needs to know how to talk to the module, and for that you need drivers. If you plug the module into your computer, you should gain a serial port. If this doesn't happen, you probably need a driver.

On Linux the drivers ship with the kernel. The most common chips are supported from kernel version 2.6 and up, and the drivers are still being improved in the latest versions.


brew cask install nogwat?
[Serial](https://www.decisivetactics.com/products/serial/) for macOS

### Finding the serial port

E.g. COM9 of /dev/ttyUSB0

* Unplug the bridge, list all devices (ls /dev), plug the bridge in again, and compare the two.

### Talking to the serial port

Using screen or putty.

### Finding an UART interface on a device

* Find debug pins
* Use headphones
* Use a logic analyzer

<img src="/images/uart-hg655.jpg" width="680">

### Connecting to the device

* Find ground
* Don't connect VCC

### Finding the baud rate

Is it possible to use arbitrary baud rates?
Max baud rate is up to 2M or 3M

Screen zegt soms niks ook al klopt de baud rate niet. Gebruik stty. En dan nog klopt het vaak niet.

<img src="/images/uart-logic-680.png" alt="A pulse is 104µs long">

<img src="/images/uart-baud-rate-incorrect.png" alt="Incorrect baud rate will give gibberish">

### Troubleshooting

The first step is to pinpoint where the problem is: is it between your computer and the bridge or between the bridge and the device you want to connect to?

#### Locating the problem

* Check the LEDs on the bridge.
    * Is the Tx LED lighting up when you are sending data? Then the connection between your computer and the bridge is OK.
    * Is the Rx LED lighting up but you don't see anything in your terminal application? Then the the connection between the bridge and the device is OK and the problem is between your computer and the bridge.
* Disconnect the bridge from the device and connect the Rx and Tx of the bridge together. Is your typing reflected in the terminal? Then the connection to the bridge is OK.

#### USB problems

* Check whether your operating system recognizes the USB device. 
    * On Linux: lsusb
    * On macOS: ioreg -p IOUSB
    * On Windows: check device manager.
* View the logs (using dmesg) when putting the device into your computer.
* Unplug the bridge, list all devices (ls /dev), plug the bridge in again, and compare the two.
* Try another known good USB cable.

#### Remote problems

List the plugged in USB devices.

On MacOS: ioreg -p IOUSB

Diff between ls /dev with and without device plugged in

Watch the LEDs

Connect RX and TX together with a jumper wire.

Ground not connected? Baud rate off?


Is your data line an "open collector"? Do you need a pull-up resistor? Does the converter has a pull-up resistor?
Is the data inverted? This is common in smart meters. This can be solved in software by programming the USB to UART chip. CH340 heeft R232 pin.

### OS differences



Verkoopt op Ali:
CH340
CP2102
PL2303

Bij Qbit hebben we:
PL2303
CH340


FTDI FT232


## Conclusion

TODO

## Read more

### Chips

* [ASIX MCS7810](https://www.asix.com.tw/products.php?op=pItemdetail&PItemID=262;74;109&PLine=74)
* Atmel ATMEGA8U2 or 16U2 with [Arduino USBSerial Firmware](https://github.com/arduino/ArduinoCore-avr/tree/master/firmwares/atmegaxxu2/arduino-usbserial)
* Cypress [CY7C65211](http://www.cypress.com/?mpn=CY7C65211-24LTXI) or [CY7C65213](http://www.cypress.com/?mpn=CY7C65213-28PVXI)
* [Microchip MCP2200](https://www.microchip.com/MCP2200)
* [Prolific PL2303HX](http://www.prolific.com.tw/us/ShowProduct.aspx?p_id=156&pcid=41)
* [Silicon Labs CP210x](http://www.silabs.com/products/interface/usbtouart/Pages/usb-to-uart-bridge.aspx)
* [Texas Instruments TUSB3410](http://www.ti.com/product/TUSB3410)
* [WCH-IC CH340](http://wch-ic.com/product/usb/ch340.asp)

### Articles

* [Buggy CP2102N Replaced](https://www.crowdsupply.com/pylo/muart/updates/buggy-cp2102n-replaced)
* [Using the Hardware Serial Ports](https://www.pjrc.com/teensy/td_uart.html)
* [How high of a baud rate can I go (without errors)?](https://arduino.stackexchange.com/questions/296/how-high-of-a-baud-rate-can-i-go-without-errors)
* [Review: CP2102N USB-UART Bridge](https://www.element14.com/community/roadTestReviews/2451/l/SILICON-LABS-USB-TO-UART-Bridge-Controller-EVM)
* [Getting started with UART: the 60yr old protocol that's still alive and kicking](https://blog.conduitlabs.com/uart-serial-debugging/)
* [Linux-custom-baud-rate](https://github.com/jbkim/Linux-custom-baud-rate)
* [Setting Arbitrary Baud Rates On Linux](https://blog.ploetzli.ch/2014/setting-arbitrary-baud-rates-on-linux/)
* [FTDI FT232RL: real vs fake](https://zeptobars.com/en/read/FTDI-FT232RL-real-vs-fake-supereal)
* [µArt by Karoly Pados](https://www.crowdsupply.com/pylo/muart)

### Tweets

* [@az6667: i don’t have a fast enough UART](https://twitter.com/az6667/status/1069916077574914050)
* [@TubeTimeUS: j10 looks like a UART header](https://twitter.com/TubeTimeUS/status/1070835644224565249)
* [@KristinPaget: don't buy FT232 - the onboard 3v3 regulator can only supply 50mA](https://twitter.com/KristinPaget/status/963258450724634624)
* [@hamityanik: I think there is an issue with CP2102N MacOS drivers](https://twitter.com/hamityanik/status/841379724253376516)
* [@stefandz: Just found a second error in the @siliconlabs CP2102N datasheet](https://twitter.com/stefandz/status/1022844368783331329)
* [@protosphere_: PL2303 is so flaky I want to flip a table](https://twitter.com/protosphere_/status/769501797677268992)
