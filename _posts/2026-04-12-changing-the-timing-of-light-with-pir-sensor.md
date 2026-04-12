---
layout: post
title: "Changing the timing of light with pir sensor"
thumbnail: pir-light-480.jpg
date: 2026-04-22
---

The "Brain" of the Operation: Identify the BISS0001 (or variant) as the standard PIR controller IC. Almost all cheap motion-sensor lights use this chip, making this a very "universal" hack. The LJ3405 IC is a dedicated PIR signal processor. 

The Timing Logic: Explain the RC (Resistor-Capacitor) network concept. Specifically, that the "ON" time is governed by the discharge rate of components connected to Pins 3 and 4. The 100k Cycle Rule: The chip counts 100,000 clock cycles before turning the light off. To stay on longer, you simply have to make the clock "tick" slower.

Component Identification: Describe how you traced the circuit from the IC pins to find R2 and C1. This encourages readers to look at traces rather than just following a "paint-by-numbers" guide. Explain how to read capacitor codes (e.g., how a "103" green Mylar cap equals 10nF).

The "Add-on" Method vs. Replacement: Highlight why adding a capacitor in parallel is easier than replacing an SMD component. It requires less precision and is much harder to "break" the original board.

Tools Used: Mention that for this level of work, a fine-tip soldering iron and some basic tweezers are essential for working with the tiny SMD pads.
