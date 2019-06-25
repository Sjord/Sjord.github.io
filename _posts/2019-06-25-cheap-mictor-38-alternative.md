---
layout: post
title: "A cheap alternative for Mictor 38 debugging connectors"
thumbnail: mictor-480.jpg
date: 2019-10-23
---

At work I supervise an intern, Nick, who works on replacing firmware of hard drives. These hard drives have a series of empty pads to attach a debug connector during development. These pads normally take a [Mictor](https://en.wikipedia.org/wiki/MICTOR)-38 connector, a high frequency connector often used with ARM systems.

Mictor-38 connectors are quite expensive and we don't have any devices that come equiped with a Mictor-38 connector, so going the Mictor path was not obvious. In the end, Nick just soldered little wires to the hard drive, while I started to think about an alternative.

<img src="/images/mictor-blank.jpg" style="width: 100%">

The Mictor connectors have 38 pins with a pitch (distance between pins) of 0.635 mm. This is quite an unusual pitch, and it took me some time to find a matching connector. Eventually I found [this BTB connector](https://nl.aliexpress.com/item/10-stuks-BTB-0-635mm-Pitch-40-Pin-Boord-Vrouwelijke-Mannelijke-SMT-Surface-Mount-BTB-Mezzanine/32967755498.html), which has 40 pins with the correct pitch.

<img src="/images/mictor-connector.jpg" style="width: 100%">


<img src="/images/mictor-harddisk.jpg" style="width: 100%">
<img src="/images/mictor-kicad.png" style="width: 100%">
<img src="/images/mictor-pcb.jpg" style="width: 100%">
<img src="/images/mictor-attached.jpg" style="width: 100%">
