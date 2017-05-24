---
layout: post
title: "Burp intruder attack types"
thumbnail: pitchfork-240.jpg
date: 2017-04-12
---

Burp is an intercepting proxy that can be used to test web sites. It has a fuzzing feature called *intruder* that can replace parameters in a request with values from one or more payload lists. It has several attack types that determine how the payloads are used in the request parameters. This post explains how the different attack types work.

## Sniper

The sniper attack uses one payload at a time and places it in each position. Only one position is changed each request. The other positions remain unchanged.

* Replaces one position at a time.
* Uses one payload set, regardless of the number of positions.
* Uses the original values for all positions that have no payload.
* Does *positions* × *payload size* requests.

For example, consider a URL with two positions. First, the first position is replaced by values from the payload set and the second position is left alone. After all values are exhausted, the second position is used and the first position is left alone.

![Sniper example](/images/burp-intruder-sniper.png)

## Battering ram

The battering ram attack places each payload in all defined positions.

* Uses one payload set, regardless of the number of positions.
* Replaces all positions with the same payload.
* Does *payload size* requests.

Using the same example URL with the two positions, you can see that the same payload is put in all positions.

![Battering ram example](/images/burp-intruder-battering-ram.png)

## Pitchfork

The pitchfork uses one payload set for each position. It places the first payload in the first position, the second payload in the second position, and so on.

* Uses as many payload sets as there are positions.
* Replaces each position with its respective payload.
* Does as many requests as the maximum payload set size.
* First payload set goes into first position, etc.

In this example you can see that it uses the first payload from each set in the first request, and the second payload from each set in the second request.

![Pitchfork example](/images/burp-intruder-pitchfork.png)

## Cluster bomb

The cluster bomb attack tries all different combinations of payloads.

* Combines all payload sets.
* Does *payload size*<sup>*positions*</sup> requests.
* First payload set goes into first position, etc.

The cluster bomb tries all possible combinations, while still keeping the first payload set in the first position and the second payload set in the second position.

![Cluster bomb example](/images/burp-intruder-cluster-bomb.png)
