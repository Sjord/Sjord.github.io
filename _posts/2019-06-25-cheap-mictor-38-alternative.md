---
layout: post
title: "A cheap alternative for Mictor 38 debugging connectors"
thumbnail: mictor-480.jpg
date: 2019-10-23
---

At work we often have student interns working on their graduation project. One of these interns, Nick, works on replacing firmware of hard drives. These hard drives have a series of empty pads to attach a debug connector during development. These pads normally take a [Mictor](https://en.wikipedia.org/wiki/MICTOR)-38 connector, a high frequency connector often used for debugging ARM systems. The connector exposes a JTAG interface, which makes it possible to manipulate the firmware on the hard disk.

Mictor-38 connectors are quite expensive and we don't have any devices that come equiped with a Mictor-38 connector, so going the Mictor path was not obvious. In the end, Nick just soldered little wires to the hard drive.

Even though Nick had a simple solution, I thought it would be useful to have an easy way to connect to pads like this. This would be especially useful if we come across more Mictor-38 pads in the future. I searched for a connector that would be useable with these pads.

<img src="/images/mictor-blank.jpg" style="width: 100%">

The Mictor connectors have 38 pins with a pitch (distance between pins) of 0.635 mm. This is quite an unusual pitch, and it took me some time to find a matching connector. Eventually I found [this BTB connector](https://nl.aliexpress.com/item/10-stuks-BTB-0-635mm-Pitch-40-Pin-Boord-Vrouwelijke-Mannelijke-SMT-Surface-Mount-BTB-Mezzanine/32967755498.html), which has 40 pins with the correct pitch.

<img src="/images/mictor-connector.jpg" style="width: 100%">
<img src="/images/mictor-harddisk.jpg" style="width: 100%">

Soldering the little wires is quite a challenge. The best way is to first put solder on the whole connector, and then remove it again using solder wick. This leaves a little bit of solder under each pin, without the precision needed when soldering each pin individually.

To connect easily to the other connector I made a little breakout board. I drew a PCB in Kicad and had it made by a PCB manufacturer.

<img src="/images/mictor-kicad.png" style="width: 100%">
<img src="/images/mictor-pcb.jpg" style="width: 100%">
<img src="/images/mictor-attached.jpg" style="width: 100%">

In the end, it didn't totally work. The JTAG boundary scan didn't turn up any CPU's. I am not sure why. I checked all connections, but couldn't find a problem.

Even though the actual JTAG connection failed, this project was still educational for me. For me, it was the first time I designed and prodered a PCB, but I found it pretty easy.