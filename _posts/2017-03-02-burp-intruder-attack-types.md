---
layout: post
title: "Burp intruder attack types"
thumbnail: pitchfork-240.jpg
date: 2017-08-02
---

Burp is an intercepting proxy that can be used to test web sites. It has a fuzzing feature called *intruder* that can replace parameters in a request with values from one or more payload lists. It has several attack types that determine how the payloads are used in the request parameters. This post explains how the different attack types work.

## Intruder introduction

Burp Intruder makes it possible perform a number of automatically modified requests. If you load a login request and a list of usernames and passwords in the intruder, for example, it can perform a brute-force attack. There are several ways to configure an intruder attack:

* the base request, as shown on the Positions tab. This is the template for the request.
* the attack type, on the Positions tab, determines the way payloads are put in positions and is the subject of this post.
* the positions within the requests, also shown on the Positions tab. The positions are marked using § characters. Anything between two § characters is replaced by a payload.
* the payload sets on the Payload tab contain the data that is inserted into the positions. Each payload set has some way to generate payloads, which are strings to use in the request.

After clicking the "Start attack" button, the intruder will perform a number of requests, replacing the marked positions with payloads in each request. Exactly which payloads it puts in which position depends on the attack type. This also determines how many requests it will perform.

## Sniper

The sniper attack replaces only one position at a time. It only uses one payload set. It loops through this payload set, first replacing only the first marked position with the payload and leaving all other positions to their original value. After its done with the first position, it continues with the second position.

This attack type is most useful when fuzzing, for example to find XSS or SQL injection. The payload is tried in each position while leaving the other parameters intact, making a successful request more likely.

* Replaces one position at a time.
* Uses one payload set, regardless of the number of positions.
* Uses the original values for all positions that have no payload.
* Does *positions* × *payloads* requests.

For example, consider a URL with two positions. First, the first position is replaced by values from the payload set and the second position is left alone. After all values are exhausted, the second position is used and the first position is left alone.

![Sniper example](/images/burp-intruder-sniper-table.png)

## Battering ram

The battering ram attack type places the same payload value in all positions. It uses only one payload set. It loops through the payload set and replaces all positions with the payload value.

* Uses one payload set, regardless of the number of positions.
* Replaces all positions with the same payload.
* Does *payload size* requests.

Using the same example URL with the two positions, you can see that the same payload is put in all positions.

![Battering ram example](/images/burp-intruder-battering-ram-table.png)

## Pitchfork

The pitchfork attack type uses one payload set for each position. It places the first payload in the first position, the second payload in the second position, and so on.

It then loops through all payload sets at the same time. The first request uses the first payload from each payload set, the second request uses the second payload from each payload set, and so on.

This attack type is useful if you have data items that belong together. For example, you have usernames with corresponding passwords and want to know whether they work with this web application. In this case you want to replace both the username and the password in the login request. Load the usernames in the first payload set, and the corresponding passwords in the second payload set. Now only one request for each username/password combination is done.

* Uses as many payload sets as there are positions.
* Replaces each position with its respective payload.
* Does as many requests as the maximum payload set size.
* First payload set goes into first position, etc.

In this example you can see that it uses the first payload from each set in the first request, and the second payload from each set in the second request.

![Pitchfork example](/images/burp-intruder-pitchfork-table.png)

## Cluster bomb

The cluster bomb attack tries all different combinations of payloads. It still puts the first payload in the first position, and the second payload in the second position. But when it loops through the payload sets, it tries all combinations.

This attack type is useful for a brute-force attack. Load a list of commonly used usernames in the first payload set, and a list of commonly used passwords in the second payload set. The cluster bomb attack will then try all combinations.

Note that the number of requests can grow very quickly. If you have 100 usernames and 100 passwords, this attack will perform 10,000 requests. This becomes exponentially worse when using more positions, so this attack is only feasible with a relatively small number of payloads and positions.

* Combines all payload sets.
* Does *payloads*<sup>*positions*</sup> requests.
* First payload set goes into first position, etc.

The cluster bomb tries all possible combinations, while still keeping the first payload set in the first position and the second payload set in the second position.

![Cluster bomb example](/images/burp-intruder-cluster-bomb-table.png)

## Conclusion

The Burp intruder has a couple of attack types which differ in how they loop over positions and payloads. Hopefully this post helps you to use the right tool for the right job.
