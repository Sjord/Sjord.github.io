---
layout: post
title: "MCH 2022 hacker camp - technical talks and workshops"
thumbnail: mch-speaker-480.jpg
date: 2022-07-29
---

I followed about 15 talks and workshops on the MCH 2022 hacker camp. This post briefly describes each talk, my opinion on it, and key points.

<!-- Photo source: https://pixabay.com/photos/speaker-talk-woman-microphone-6377629/ -->

## ⚠️ May Contain Hackers 2022 Opening

* &#9733; &#9733; &#9734; &#9734; &#9734;
* [talk description](https://program.mch2022.org/mch2022/talk/JBNXAX/)
* [video recording](https://media.ccc.de/v/mch2022-109--may-contain-hackers-2022-opening)

General announcements about the camp. Also, give it up for the power team! Give it up for the network team! etc.

## Introduction to GraphQL hacking

* &#9733; &#9733; &#9733; &#9734; &#9734;
* [talk description](https://wiki.mch2022.org/Session:Introduction_to_GraphQL_hacking)
* [dolevf/graphw00f: GraphQL Server Engine Fingerprinting utility](https://github.com/dolevf/graphw00f)
* [nikitastupin/clairvoyance: Obtain GraphQL API schema despite disabled introspection!](https://github.com/nikitastupin/clairvoyance)
* [doyensec/inql: InQL - A Burp Extension for GraphQL Security Testing](https://github.com/doyensec/inql)

This was one of the sessions in a village, instead of centrally organized.

GraphQL is an API where you can perform queries. In contrast to a REST API, all queries are posted to a single endpoint. It's particularly interesting to test for authorization issues, also on mutations. But normal injection is also possible, particularly if the GraphQL source is a legacy system. Also, try to find a GraphiQL or other frontend.

Pretty basic stuff if you are familiar with GraphQL. There was one thing that was not mentioned during the talk, but that I read somewhere is sometimes possible: authorization bypass by creating links through other objects. For example, maybe you are not authorized to request users&#8594;location, but you can request me&#8594;group&#8594;members&#8594;location. This happens when the authorization is performed on the link between objects, but there is another way to get to the same objects. I don't know the specifics about this or how often this works. 

## Detecting Log4J on a global scale using collaborative security

* &#9733; &#9734; &#9734; &#9734; &#9734;
* [talk description](https://program.mch2022.org/mch2022/talk/DWKYMM/)
* [video recording](https://media.ccc.de/v/mch2022-135-detecting-log4j-on-a-global-scale-using-collaborative-security)

An advertisement for the speakers' company. They have a product which is basically a firewall that shares firewall rules between clients. If one client detects an attack by 1.2.3.4, that IP is also blocked on all other clients.

Perhaps this works against automated attacks, but I think they were fooling themselves a little bit with the metrics. Anyone with an SSH server knows that it receives many password brute force attempts, which will never succeed because password authentication is disabled. Now, if the speaker claims that their product blocked many malicious SSH requests, I wonder if that makes any difference in the actual security in the end.

I think it does make sense to collaborate more, or use open data about networks to determine the risk level of requests. But I thought this talk was a little disappointing.

## <s>May</s> Will Contain Climate Change

* &#9733; &#9733; &#9733; &#9734; &#9734;
* [talk description](https://program.mch2022.org/mch2022/talk/U8AEE9/)
* [video recording](https://media.ccc.de/v/mch2022-278-m-a-y-will-contain-climate-change)

A depressing talk about the state of the world. Takeaways are:

* Keep living your live. We are trying to save the environment so we can live happily ever after, and it is of no use to be intentionally miserable right now.
* Don't stock up on beans, toilet paper and water, but do prepare for extreme weather.
* Keep a sense of scale in mind. Petroleum distilleries in Rotterdam use about 1000 MW of thermal energy, so removing your charger from the socket after charging doesn't make a dent in the total energy usage.

## Bug hunting for normal people

* &#9733; &#9734; &#9734; &#9734; &#9734;
* [talk description](https://program.mch2022.org/mch2022/talk/HVQDNE/)
* [video recording](https://media.ccc.de/v/mch2022-180-bug-hunting-for-normal-people#t=1512)
* [pywinauto - Windows GUI Automation with Python](https://pywinauto.readthedocs.io/en/latest/)
* [SkyLined/BugId: Detect, analyze and uniquely identify crashes in Windows applications](https://github.com/SkyLined/BugId)
* [MozillaSecurity/lithium: Line-based testcase reducer](https://github.com/MozillaSecurity/lithium/)
* [posidron/dharma: Generation-based, context-free grammar fuzzer.](https://github.com/posidron/dharma)

Chaotic and very technical presentation, resulting in many bugs in Adobe Reader's JavaScript implementation.

Definitely not for normal people. The title convinced my sales and manager colleagues to join, but the speaker mentioned things like how 0xF0F0F0F0 in EAX is a sign of a buffer overlow.

Knud (the speaker) mainly described how to overcome practical obstacles. He describes how to generate semi-valid JavaScript by using [Dharma](https://github.com/posidron/dharma). These JavaScripts are put in a PDF and opened by Adobe Reader. Adobe Reader crashes, which could indicate a vulnerability. He then tries to identify interesting crashes, and to reduce the payload size. It's a little bit a hack job, but he finds many bugs with it.

Knud didn't report his vulnerabilities, and they are still in there. I thought that was pretty interesting.

## “You give me fever, fever all through the night": Hack attacks against wireless medical devices and the virtual patient

* &#9733; &#9733; &#9733; &#9733; &#9734; ?
* [talk description](https://program.mch2022.org/mch2022/talk/EUMTS7/)

[Isabel Straw](https://twitter.com/IsabelStrawMD/status/1551851704743772161) explained in 15 minutes about how medical devices can be hacked, and after that there was a threat modeling workshop for medical devices.

We arrived 15 minutes late to this workshop, so we missed all of the explanation. The medical devices were a blood sugar sensor, a heart rate sensor, and a muscle stimulator. The participants had to think about how these can be hacked and what the impact of it would be.

One attack method was to just replace the phone app by a malicious app that looks similar. In this case, it doesn't matter how well secured the device or protocol is. The malicious app wouldn't use any of those, but just show fake results. So the attacker wouldn't have to break any security.

<a href="https://twitter.com/IsabelStrawMD/status/1551851704743772161/photo/1"><img src="/images/mch-medical-devices-isabel-straw.jpeg" style="width: 100%"></a>

## Electronic Locks: Bumping and Other Mischief

* &#9733; &#9733; &#9733; &#9733; &#9734; ?
* [talk description](https://program.mch2022.org/mch2022/talk/KBVXRU/)
* [video recording](https://media.ccc.de/v/mch2022-264-electronic-locks-bumping-and-other-mischief)

I was walking around on the terrain, and a talk was going on about locks inside a tent. I stepped in and attended this talk for a while, but I missed the first 20 minutes or so.

The speaker has many electronic locks, where the weak spot is often in the physical field. By hitting locks, placing a magnet on them, or by using a hammer drill, they open multiple electronic locks in a manner that looks way too easy.

This was particularly a lesson to keep the end goal in sight. If you see an electronic lock, your first thought may be to hack the electronics. But the end goal is to get the door open, and that is achieved much easier with physical means.

## Power of Play! How to relearn playfulness.

* &#9733; &#9733; &#9733; &#9733; &#9733;
* [talk description](https://program.mch2022.org/mch2022/talk/QTSAFP/)
* [Happy Game Changers - Serious Play and Gamification Expert](https://www.happygamechangers.com/)
* [Global Play Brigade - Play it Forward. Change our World](https://www.globalplaybrigade.org/)
* [#play14](https://play14.org/)

A workshop where the energetic Nancy Beers explains collaborative games and how to use play and games in a professional setting.

I mostly joined this because I expected this would be fun, and would be joined by fun people, and it was. But it was also informative and gave me some ideas.

For example, it could be fun to guess vulnerabilities with yes/no questions. It would force participants think about what questions to ask to find out what the vulnerability is.

One game was about "hacking" a group of people. The group agreed on two rules on how to answer to yes/no questions. Me and a fellow hacker must figure out the algorithm by asking the questions and see how everyone responds. I asked loads of questions, but in the end my companion hacker solved the puzzle, while I was still in the dark. I had discarded the correct solution as impossible, because the game behaved slightly different then I expected. This is perhaps also a lesson when hacking, to not quickly discard a possibility.

## My journey to find vulnerabilities in macOS

* &#9733; &#9733; &#9733; &#9733; &#9733;
* [talk description](https://program.mch2022.org/mch2022/talk/973QGG/)
* [video recording](https://media.ccc.de/v/mch2022-291-my-journey-to-find-vulnerabilities-in-macos)

A cool hacking talk, where downloading a ZIP file is sufficient to take over a system. The ZIP file contains an application with an alias file, which is basically a symbolic link. This link points to a SMB share, which is by default mounted in /Volumes. But due to a path traversal bug, it can be mounted in any location on the file system. By mounting it in a zsh config directory location, code is executed when a shell is started. This gives remote code execution, but MacOS security controls reduce the impact. So the speaker also bypasses TCC, SIP and GateKeeper to have an actual RCE.

After finding the vulnerability, this researcher spends much time perfecting the exploit, which is a pattern that we also see in other talks.

## Hacking the pandemic's most popular software: Zoom

* &#9733; &#9733; &#9733; &#9733; &#9734;
* [talk description](https://program.mch2022.org/mch2022/talk/QVXXUP/)
* [video recording](https://media.ccc.de/v/mch2022-152-hacking-the-pandemic-s-most-popular-software-zoom#t=1053)

Cool hacking talk, where a heap overflow in Zoom is combined with several low-risk vulnerabilities to result in remote code execution. Notably, the researchers force the allocation on the low-fragmentation heap, which was conventionally said to be hard to exploit.

These guys spend two weeks on finding the original vulnerability, and six weeks on getting the exploit to work. It's impressive work, but most of us don't have two months to spend on developing exploits.

## Finding 0days in Enterprise Web Applications

* &#9733; &#9733; &#9733; &#9734; &#9734;
* [talk description](https://program.mch2022.org/mch2022/talk/EF7VSC/)
* [video recording](https://media.ccc.de/v/mch2022-99-finding-0days-in-enterprise-web-applications)

[Shubham](https://shubs.io/) reviews code of enterprise applications to find vulnerabilities. He obtains the source code from cloud marketplaces, docker hub, or by asking sales for a demo.

He presents a couple of cool vulnerabilities, but these are found by just reviewing code for a long time. His tips are: don't give up, take enough breaks.

Shubham's girlfriend was in the front with a sign. On the side facing the public it said "Shubhma is great" or something like that, but on Shubham's side it said "Don't fuck it up". She got asked to take down her sign because it was blocking the camera view.

## What to do when someone close to you takes their life and you are not Tech-Savvy

* &#9733; &#9733; &#9733; &#9734; &#9734;
* [talk description](https://program.mch2022.org/mch2022/talk/7PZANM/)
* [video recording](https://media.ccc.de/v/mch2022-219-0-what-to-do-when-someone-close-to-you-takes-their-life-and-you-are-not-tech-savvy)

An emotional talk about postmortum data forensics, to provide answers to relatives. This talk is more about ethics, trust, privacy and mental health, and that makes it interesting.

Key takeaway was to prepare things in the event of your death.

While I was away on MCH, the internet went out at home. My girlfriend had no idea what to do. She called the internet provider helpdesk, which reset the Wi-Fi router, which fixed some things and made other things worse. Point is, I made the home network too complicated for my girlfriend to manage on her own. This gives problems when I am away for a couple of days, but will cause a real headache when I am death or otherwise indefinitely unavailable.

Their clients trusted the speakers to handle sensitive jobs. That's a level of professionalism that I rarely see.

When someone mentions suicide to you, even as a joke, you should engage the conversation and not laugh it away. This is pretty hard for most people, but there's [online training (Dutch)](https://vraagmaar.113.nl/online-training/) to help with this.

## OpenStreetMap for Beginners

* &#9733; &#9733; &#9733; &#9733; &#9734;
* [talk description](https://program.mch2022.org/mch2022/talk/EWVPDA/)
* [MapComplete](https://mapcomplete.osm.be/?language=en)
* [uMap](https://umap.openstreetmap.fr/en/)
* [OpenWhateverMap](https://openwhatevermap.xyz/#3/27.99/17.93)

Just an intro into [OpenStreetMap](https://www.openstreetmap.org/).

This workshop was supposed to be held in Dutch. However, several people showed up who didn't spoke Dutch, and it was held in English.

I already knew about OpenStreetmap, and regularly annoy my girlfriend during hikes by stopping to add a bench to the map. So what was told was pretty familiar, but still interesting and fun.

## First Privacy, Now Safety: An Anthology of Tales from the Front Lines of Cyber Physical Security

* &#9733; &#9734; &#9734; &#9734; &#9734;
* [talk description](https://program.mch2022.org/mch2022/talk/QYAUZT/)
* [Triton (malware) - Wikipedia](https://en.wikipedia.org/wiki/Triton_(malware))

Hackers can hack industrial control systems to blow up factories. To a cybersecurity attack can have physical impact, which we already knew from Stuxnet. This talk is more about the general trend, and quite low on technical details.

## ffuf the web - automatable web attack techniques

* &#9733; &#9733; &#9734; &#9734; &#9734; ?
* [workshop description](https://program.mch2022.org/mch2022/talk/FACWJG/)
* [amass — Automated Attack Surface Mapping - Daniel Miessler](https://danielmiessler.com/study/amass/)
* [ffuf/ffuf: Fast web fuzzer written in Go](https://github.com/ffuf/ffuf)
* [slides](https://io.fi/ffuf-workshop/)
* [joohoi/ffuf-workshop](https://github.com/joohoi/ffuf-workshop)

This workshop started pretty slow, and I walked out after half an hour.

The workshop would have been about using automated tools to scan thousands of websites.

## Project TEMPA - Demystifying Tesla's Bluetooth Passive Entry System

* &#9733; &#9733; &#9733; &#9733; &#9734;
* [talk description](https://program.mch2022.org/mch2022/talk/DCTJDE/)
* [video recording](https://media.ccc.de/v/mch2022-235-project-tempa-demystifying-tesla-s-bluetooth-passive-entry-system)

Hacking Teslas. Tesla has several ways to open and drive the car, such as NFC card and phone presence. Technical talks with some cool hacks.

What was particularly interesting are the attack scenarios and threat models. For example, the phone unlocking is vulnerable to denial of service. This is generally just annoying and not very interesting. But in practice it would cause the user to use other methods to unlock their car, which in turn are vulnerable to other things.

Another example is that Teslas emit a Bluetooth signal that identifies the card, which makes automatic scanning for cars possible. Of course it is also possible to look for Teslas just by looking around, or by license plate recognition. So the impact of this is not really clear to me.

## Single Sign-On: A Hacker's Perspective

* &#9733; &#9733; &#9733; &#9734; &#9734;
* [talk description](https://program.mch2022.org/mch2022/talk/MTTAXV/)
* [video recording](https://media.ccc.de/v/mch2022-201-single-sign-on-a-hacker-s-perspective)

A talk about SAML, OAuth and OpenID. Some interesting attack scenarios, like login CSRF and malicious service providers.

SSO works with three systems that communicate, and OpenID and SAML use conflicting and confusing terms for these systems. This makes the protocol and the talk confusing.

I had hoped to gain more information about how to check the actual tokens. JWTs contain fields like `exp`, `aud`, `iat`, etc. Should the application check these? This was given no attention.

## Conclusion

Many of the talks describe a cool hack, but not really how it was found or how you can find similar bugs quicker. In some cases, researchers just put an extraordinary amount of time in finding a vulnerability, which doesn't really help me to find my own bugs.

After they find the vulnerability, many researchers then spent some more time on creating an impactful exploit, so the vulnerability looks more serious.

The non-technical talks or less technical talks were actually the most interesting. These give you insights outside of the hacker bubble.

### Next time

* Research which speakers are great before the event.
* Come on time for workshops. It matters less for talks.
* Join more non-technical workshops, or talks about atypical subjects.
