---
layout: post
title: "Bitflip effect on encryption operation modes"
thumbnail: flipswitch-240.jpg
date: 2018-04-25
---

<!-- Photo source: https://www.flickr.com/photos/nasarobonaut/5456219255 -->

EBC: garbles same block
CBC: garbles same block, flips bit in next block
PCBC: garbles same and all following blocks
CFB: flips bit in same block, garbles next block
OFB: flips bit in same block
CTR: flips bit in same block

* [Bit-flipping attack](https://en.wikipedia.org/wiki/Bit-flipping_attack)
