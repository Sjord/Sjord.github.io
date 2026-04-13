---
layout: post
title: "Changing the timing of light with passive infrared sensor"
thumbnail: pir-light-480.jpg
date: 2026-04-15
---

At our scouting clubhouse we have lights in the toilet that turn on automatically when you enter. However, they only stay on for 30 seconds, which means that you have to wave around your arms while you are peeing to turn on the lights. I couldn't find affordable small lights with either better sensors or longer timeouts, so I tried to modify the lights we did have, to increase the time.

I modified two lights. In both the approach was roughly the same:

1. Identify the IC. Most passive infrared (PIR) lights have a dedicated PIR IC that reads the sensor and controls the timing. In one of the lights this was even on a separate PCB, with one output pin to turn the light on or off.
2. In the datasheet of the IC, determine how the timing is set. This is usually made up of a resistor and a capacitor. This RC circuit is charged and discharged a certain number of times, which determines the time the light stays on.
3. Add a capacitor. The timing can be changed either by increasing the resistor or increasing the capacitor. Putting two resistors in parallel decreases the resistance, but putting two capacitors in parallel increases the capacitance. By adding a capacitor, I didn't have to remove any components from the board.

One light had a LJ3405 IC, and the other had a BISS0001. Both are ICs specifically for PIR lights. The datasheet specifies which components determine the timing, and to which IC pins these should be connected. By following the traces and measuring continuity from these pins, I could determine which components on the PCB I should change. In both cases, it was easiest to solder the capacitor directly to the IC, instead of to the original capacitor. I added 10nF capacitors to the lights, extending the time from 30 seconds to 5 minutes.

Below are images of the unmodified and modified light. The PIR IC and timing logic is on the top board. I assumed that the top board was plugged in the bottom board, and only discovered it was soldered to it when I pulled it off. The green wire is a fix to reattach the top board. The green blob component is a 10nF capacitor to increase the timing.

<img src="/images/pir-lamp-unmodified.jpg" style="width: 100%" alt="unmodified PIR light, with the PIR IC on the top PCB">

<img src="/images/pir-lamp-modified.jpg" style="width: 100%" alt="modified light, with additional 10nF capacitor">

## Read more

* [2pcs/lot Radar Led Downlight 5W 7W 9W 220V Human Sensor Ceiling Light 2.5/3/4 inch Round Led Panel Down Light Spotlight Lighting - AliExpress](https://www.aliexpress.com/item/1005008592327680.html)
