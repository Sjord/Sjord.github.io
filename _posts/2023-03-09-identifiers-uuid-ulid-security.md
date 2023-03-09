---
layout: post
title: "Security of identifiers"
thumbnail: tree-tag-480.jpg
date: 2023-04-19
---

*This UUID has one weird trick to remain globally unique - database indices hate him!*

## Risks

There are two risks when using identifiers:

* guessable - an identifier of a resource can be guessed without too much effort. In some cases, it may be possible to retrieve a resource knowing just the identifier.
* information disclosure - the identifier can expose how many items are in the database, the state of the random number generator, the network address of the machine it was generated on, or the time it was generated.

### Guessability

### Information disclosure

#### German tank problem

#### Information contained in identifiers

## Other considerations

### Asynchronous generation

### Database indices
