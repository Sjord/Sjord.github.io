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

## Battering ram

The battering ram attack places each payload in all defined positions.

* Uses one payload set, regardless of the number of positions.
* Replaces all positions with the same payload.
* Does *payload size* requests.

## Pitchfork

The pitchfork uses one payload set for each position. It places the first payload in the first position, the second payload in the second position, and so on.

* Uses as many payload sets as there are positions.
* Replaces each position with its respective payload.
* Does as many requests as the maximum payload set size.
* First payload set goes into first position, etc.

## Cluster bomb

The cluster bomb attack tries all different combinations of payloads.

* Combines all payload sets.
* Does *payload size*<sup>*positions*</sup> requests.
* First payload set goes into first position, etc.
