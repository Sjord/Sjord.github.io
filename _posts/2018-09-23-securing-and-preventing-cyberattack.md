---
layout: post
title: "Securing against cyber-attacks"
thumbnail: firefighters-480.jpg
date: 2019-02-13
---

In the [previous](/2019/01/16/probability-of-cyberattack/) [two](/2019/01/30/impact-of-cyberattack/) articles, we explored the risk of cyber-attacks. This article explores how you can reduce that risk.

<!-- photo source: http://kentuckyguard.dodlive.mil/2016/07/11/fighting-fire-with-cooperation/ -->

## Solving vulnerabilities is insufficient

In the [previous post](/2019/01/16/probability-of-cyberattack/), we saw that there is no silver bullet for security. Finding and solving vulnerabilities in your software, while a good idea, doesn't solve all your problems. There may simply be more vulnerabilities that you haven't found yet, or the attacker enters through a system that was not tested or patched.

For particularly vulnerable systems, solving one vulnerability does not significantly reduce the number of vulnerabilities in the system:

> Rescorla has argued that for software with many latent vulnerabilities (e.g., Windows), removing one bug makes little difference to the likelihood of an attacker finding another one later [1].

Systems can be too complex or too buggy to find and fix all the vulnerabilities. In that case, it is a good idea to depend on other layers: if the attacker breaches the first defense, can we do anything to reduce the impact of the attack?

## Assume you get hacked

Besides preventing against a cyber-attack, it is also a good idea to mitigate the impact of a cyber-attack. Given that you got hacked, what can you do to minimize the impact and cost of the hack?

> rather than focusing only on finding zero-day vulnerabilities, defenders may be able to shift the balance in their favor by starting from the assumption of compromise, investigating ways to improve system architecture design to contain the impact of compromise, and adopting different techniques to identify vulnerabilities. [9]

Risk consists of both probability and impact, so it is a good idea to reduce both. Reducing impact begins by assuming that a hacker has a foothold in the network. How do you detect him early, reduce the reach of his attack, and recover from the attack as soon as possible?

## Invest in detection

After the hackers have intruded the company systems, it can take months before they are detected [8]. With all this time they can widen the scale of the attack and copy confidential data out of the company network. Detecting a hack in time can significantly reduce its destructive impact. Furthermore, a large part of the costs of an attack consists of investigating the hack. Performing monitoring and logging correctly can greatly reduce the cost of the investigation into the hack.

## Make a recovery plan

Once you get hacked, you need to recover from it both technically and corporately as an organization. In both cases, it helps if you thought of solutions ahead of time. It may help if you have a backup solution for when the primary network is offline or have a security consulting company on speed dial that can help you recover from a hack.

## Make a communication plan

As a company, you may need to issue a formal apology or explain the hack to your customers. This is a decisive marketing moment. Your reputation as a company is dependent on how you communicate the hack to your customers. This is a critical moment that can make or break your company. It is a good idea to put the company marketeers through a fire drill and let the marketing department think ahead of time about what to communicate after a potential hack.

## Treat data as a liability

It may seem that data is cheap to keep around, but it can be a liability in the case of a hack. Information that remains unused, for example from users that delete their accounts, is better deleted than kept. If the data is not there, it can't be compromised in an attack. Therefore, it is a good idea to critically evaluate which data to keep.

## Defend in depth

Defenses are mostly built for the outer shell of a network. Everybody on the local network is trusted. This makes it easy for hackers to move through the network environment once they get a foothold on a system in your network. You don't want people on your guest WiFi to be able to attack the production servers. It is better to have separate security zones and defend in depth so that a single leak doesn't result in total compromise.

## Have a team responsible for security

In a company, people often want to deploy new services as quickly as possible without considering security. To avoid this, you need a security stake holder with in-depth security knowledge. This can take the form of a CISO, a risk management committee on the board or an cyber-incidence response team. Having a team responsible for the cyber security of the company makes sure that security gets adequate attention in your company.

Furthermore, the security team should report to the board and not a project manager who is accountable for making projects a reality. The goals of the security team may be at odds with functional goals, but the security team should feel free to do their job without being reprimanded for it. 

## Conclusion

Securing against cyber-attacks consists of finding and fixing vulnerabilities, but a large part in effective cyber-security is reducing the impact when a hack does happen. Have a discussion with the staff in your company about measures to take when you get hacked.

## Read more

1. [The Economics of Information Security](https://citeseerx.ist.psu.edu/viewdoc/download?doi=10.1.1.477.2090&rep=rep1&type=pdf)
1. [Is finding security holes a good idea?](https://www.miralishahidi.ir/resources/Is%20finding%20security%20holes%20a%20good%20idea.pdf)
1. [A framework for using insurance for cyber-risk management](https://citeseerx.ist.psu.edu/viewdoc/download?doi=10.1.1.705.9851&rep=rep1&type=pdf)
1. [Protecting against spear-phishing](https://www.faronics.com/assets/CFS_2012-01_Jan.pdf)
1. [We're sorry but it's not our fault: Organizational apologies in ambiguous crisis situations](https://onlinelibrary.wiley.com/doi/pdf/10.1111/1468-5973.12169)
1. [Game of information security investment: Impact of attack types and network vulnerability](https://www.sciencedirect.com/science/article/pii/S0957417415002274)
1. [Get the basics right: risk management principles for cyber security](https://www.ncsc.gov.uk/guidance/get-basics-right-risk-management-principles-cyber-security)
1. [Sony Pictures hack](https://en.wikipedia.org/wiki/Sony_Pictures_hack)
1. [Zero Days, Thousands of Nights, The Life and Times of Zero-Day Vulnerabilities and Their Exploits](https://paper.seebug.org/papers/Security%20Conf/Blackhat/2017_us/us-17-Ablon-Bug-Collisions-Meet-Government-Vulnerability-Disclosure-Zero-Days-Thousands-Of-Nights-RAND.pdf)
