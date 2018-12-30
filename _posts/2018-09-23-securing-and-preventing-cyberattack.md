---
layout: post
title: "Securing against cyber attacks"
thumbnail: firefighters-480.jpg
date: 2019-02-13
---

In the previous two articles, we explored the risk of cyber attacks. This artice explores how you can reduce that risk.

<!-- photo source: http://kentuckyguard.dodlive.mil/2016/07/11/fighting-fire-with-cooperation/ -->

## Solving vulnerabilities is insufficient

In the [previous post](/2019/01/16/probability-of-cyberattack/) we saw that there is no silver bullet for security. Finding and solving vulnerabilities in your software, while a good idea, doesn't solve all your problems. There may simply be more vulnerabilities that you haven't found yet, or the attacker enters through a system that was not tested or patched.

For some systems, solving one vulnerability does not significantly reduce the number of vulnerabilities in the system:

> Rescorla has argued that for software with many latent vulnerabilities (e.g., Windows), removing one bug makes little difference to the likelihood of an attacker finding another one later [1].

Some systems are too complex or too buggy to find and fix all the vulnerabilities. In that case, it is a good idea to defend on other layers as well: if the attacker breaches the first defenses, can we do anything to reduce the impact of the attack?

## Assume you get hacked

Besides preventing against a cyber attack, it is also a good idea to mitigate the impact of a cyber attack. Given that you got hacked, what can you do to minimize the impact and cost of the hack?

> rather than focusing only on finding zero-day vulnerabilities, defenders may be able to shift the balance in their favor by starting from the assumption of compromise, investigating ways to improve system architecture design to contain the impact of compromise, and adopting different techniques to identify vulnerabilities. [9]

Risk consists of both probability and impact, so it is a good idea to reduce both. Reducing impact begins by assuming that a hacker got a foothold in the network. How do you make sure you detect him early, reduce the reach of his attack, and recover from the attack as soon as possible?

## Invest in detection

After the hackers have intruded the company systems, it can take months before they are detected [8]. All this time they can widen their attack and copy confidential data out of the company network. Detecting a hack in time can significantly reduce the duration and the impact of a hack. Furthermore, a large part of the costs consists of investigating the hack. Performing monitoring and logging correctly can greatly reduce the cost of the investigation into the hack.

## Make a recovery plan

Once you get hacked, you need to recover from it both technically and as a company. In both cases, it helps if you thought of solutions ahead of time. Technically, it may help if you have some backup solution for when the primary network is offline. Have a consulting company on speed dial that can help you recover from the hack.

As a company, you may need to issue a formal apology or explain the hack to your customers. This is a decisive marketing moment. You just got bad reputation because of the hack, and how you communicate about it can make or break your company. It is a good idea to put the company marketeers through a fire drill and let the marketing department think ahead of time about what to communicate about a potential hack.

## Treat data as a liability

It may seem that data is cheap to keep around, but it can be a liability in the case of a hack. Information that remains unused, for example from users that delete their accounts, is better deleted than kept. If the data is not there it can also not be compromised in an attack. Therefore, it is a good idea to critically evaluate which data to keep.

## Defend in depth

Often all defenses are targeting the outer shell of a network. Everybody on the local network is trusted. This can make it easy for hackers to move through the environment once they get a foothold in some system. You don't want people on your guest WiFi to be able to attack the production servers. Better to have separate security zones and defense in depth, so that a single leak doesn't result in total compromise.

## Conclusion

Securing against cyber attacks consists in part of finding and fixing vulnerabilities, but also for a large part in reducing the impact when a hack does happen. Talk in your company about what you would do when you get hacked.

## Read more

1. [The Economics of Information Security](http://citeseerx.ist.psu.edu/viewdoc/download?doi=10.1.1.477.2090&rep=rep1&type=pdf)
1. [Is finding security holes a good idea?](http://www.dtc.umn.edu/weis2004/rescorla.pdf)
1. [A framework for using insurance for cyber-risk management](http://ns2.dpix.pestiest.hu/~mfelegyhazi/courses/EconSec/readings/09_Gordon2003FrameworkUsingCyberInsurance.pdf)
1. [Protecting against spear-phishing](http://faronics.com/assets/CFS_2012-01_Jan.pdf)
1. [We're sorry but it's not our fault: Organizational apologies in ambiguous crisis situations](https://onlinelibrary.wiley.com/doi/pdf/10.1111/1468-5973.12169)
1. [Game of information security investment: Impact of attack types and network vulnerability](https://www.sciencedirect.com/science/article/pii/S0957417415002274)
1. [Get the basics right: risk management principles for cyber security](https://www.ncsc.gov.uk/guidance/get-basics-right-risk-management-principles-cyber-security)
1. [Sony Pictures hack](https://en.wikipedia.org/wiki/Sony_Pictures_hack)
1. [Zero Days, Thousands of Nights, The Life and Times of Zero-Day Vulnerabilities and Their Exploits](https://paper.seebug.org/papers/Security%20Conf/Blackhat/2017_us/us-17-Ablon-Bug-Collisions-Meet-Government-Vulnerability-Disclosure-Zero-Days-Thousands-Of-Nights-RAND.pdf)