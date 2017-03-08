---
layout: post
title: "Burp intruder attack types"
thumbnail: pitchfork-240.jpg
date: 2017-04-12
---

## Sniper

* Replaces one position at a time.
* Uses one payload set, regardless of the number of positions.
* Uses the original values for all positions that have no payload.
* Does *positions* × *payload size* requests.

## Battering ram

* Uses one payload set, regardless of the number of positions.
* Replaces all positions with the same payload.
* Does *payload size* requests.

## Pitchfork

* Uses as many payload sets as there are positions.
* Replaces each position with its respective payload.
* Does as many requests as the maximum payload set size.
* First payload set goes into first position, etc.

## Cluster bomb

* Combines all payload sets.
* Does *payload size*<sup>*positions*</sup> requests.
* First payload set goes into first position, etc.

?a=a&b=b
111
222

hhh
jjj

Sniper:
Eén payload set
Alles in de lijst in alle posities, een voor een
?a=111&b=b
?a=222&b=b
?a=a&b=111
?a=a&b=222

Battering ram:
Eén payload set
Vul waarde in op alle posities
?a=111&b=111
?a=222&b=222

Pitchfork:
Twee payload sets
Synchroon lijst 1 en 2
?a=111&b=hhh
?a=222&b=jjj

Cluster bomb:
111 hhh
222 hhh
111 jjj
222 jjj

