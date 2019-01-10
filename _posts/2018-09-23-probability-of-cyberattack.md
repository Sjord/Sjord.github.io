---
layout: post
title: "Probability of cyber attacks"
thumbnail: probability-480.jpg
date: 2019-01-16
---

In a risk assessment, total risk is often calculated as a product of probability and impact. To make a proper risk assessment of cyber attacks, companies need to know both the probability and the impact of cyber attacks. This article explores the probability. How likely is it for a company to get hacked?

<!-- photo source: https://commons.wikimedia.org/wiki/File:High_School_Probability_and_Statistics_Cover.jpg -->

## Introduction

A proper risk assessment takes into account the chance and impact of a certain risk and the cost of securing against that risk. Maybe you lock your door, because it gives great security benefits at little expense. Maybe you don't own a bulletproof car, because the expense doesn't weigh up against the small chance of getting shot at. For some people this balance tips the other way: if you are rich and likely to get shot at, maybe you do buy an armored car.

The same principles hold for cyber security. It makes little sense to spend more money on preventive measures than that can be lost in an attack. To correctly make decisions about how much to invest in securing and mitigating risk, companies need information on the risk of getting hacked.

## Uncertainty

Although there are studies on the risk of cyber attacks, these provide little certainty on the probability. Different studies produce different results, sometimes differing by a factor of 100 in the chance of being hacked. One of the more conservative studies puts the risk fairly low [1]:

> It follows that the unconditional probability of a cyberattack in a given year for a firm in our sample is extremely low, as it is 0.32%.

So each year, one in 312 companies get hacked. This seems consistent with my experiences.

On the other end of the spectrum, studies say one in two companies get hacked yearly. Especially studies done or sponsored by security companies seem to artificially increase the risk.

> Just under half (46%) of all UK businesses identified at least one cyber security breach or attack in the last 12 months. This rises to two - thirds among 
medium firms (66%) and large firms (68%). [18]

These numbers are for attacks, successful or not, and can therefore expected to be much higher than numbers for successful hacks. And then there are studies that totally blow up the results. There are three kinds of lies: lies, damned lies, and statistics.
    
> You’re more likely to experience a data breach of at least 10,000 records (27.9 percent) than you are to catch the flu this winter (5–20 percent, according to WebMD). [12]

This is just plain false and Ponemon knows this. The Ponemon study [21] is done under companies that already had a data breach, which makes it more likely they will have another. Furthermore, the 27.9% is the chance over two years, not over one winter.

I would say the chances of a company getting hacked are more in the order of 1% yearly, but this may be higher for specific companies.

## Factors influencing probability

The chance of getting hacked depends on the type and size of your company. If you are a cryptocoin broker hackers have more incentive to attack your company than if you are a hobby shop. Studies [1, 17] identified the following factors that influence the chance of cyber attacks:

Higher risk for companies that:
* are relatively large
* have lower [leverage](https://en.wikipedia.org/wiki/Leverage_(finance))
* have poor past stock performance
* have more intangible assets

Lower risk for companies:
* with a risk committee on the board

I think the main factor is whether hackers have a motive to target you in an attack. If you are a notable company, engage in ethically disputable operations, have a lot of personal information on your customers, or are involved in financial transactions, hackers will have reason to pick your company for a targeted attack.

What also contributes is how well you have secured your software and network. However, I think this plays a relatively small part for most companies. It is hard to perfectly secure your company from targeted attacks.

## There is little you can do

While it is a good idea to secure your software and networks, it may not be enough to totally prevent all attacks.

> If an organisation is connected to the internet, it is vulnerable. The incidents in the public eye are just the tip of the iceberg. [14]

Security assessments typically scan for known issues. However, there is a chance that a dedicated attacker finds a previously unknown issue, a so called zero-day.

> There is almost no defense against a zero-day attack: while the vulnerability remains unknown, the software affected cannot be patched and anti-virus products cannot detect the attack through signature-based scanning. [6]

And if the attacker isn't capable of finding a zero-day, he can still buy one from another hacker.

> At the most basic level, any serious attacker can always get an affordable zero-day for almost any target. [8]

Then there are attacks that are pretty hard to defend against. Phishing, for example, is surprisingly effective. While you may not believe a prince in Nigeria has money for you, you may believe an email pretending to be from a coworker, targeted just for you. This type of targeted phishing attacks are called spear phishing.

So the probability of getting hacked depends for a great deal on whether you are targeted, and not so much on whether your systems have vulnerabilities.

## Many vulnerabilities are not exploited

When performing a security assessment on a client's product, I often find serious security vulnerabilities that have been present in production for years. Most of the time, this vulnerability did not seem to be exploited by hackers. SQL injection or path traversal vulnerabilities remain undetected for years, without any bad consequences. So it is not the case that if you have a vulnerability, that you are going to get hacked. There doesn't seem to be a clear correlation between the amount of vulnerabilities and the exploitation of them.

## Conclusion

There is much uncertainty about the probability of getting hacked, but I would put it in the order of 1% yearly. Several factors influence the probability, especially the size of the company. The presence of vulnerabilities does not necessarily correlate with exploitation by malicious actors.

## Read more

1. [What is the Impact of Successful Cyberattacks on Target Firms?](https://www.nber.org/papers/w24409)
1. [Glenn Greenwald: how the NSA tampers with US-made internet routers](https://www.theguardian.com/books/2014/may/12/glenn-greenwald-nsa-tampers-us-internet-routers-snowden)
1. [Photos of an NSA “upgrade” factory show Cisco router getting implant](https://arstechnica.com/tech-policy/2014/05/photos-of-an-nsa-upgrade-factory-show-cisco-router-getting-implant/)
1. [Protecting against spear-phishing](http://faronics.com/assets/CFS_2012-01_Jan.pdf)
1. [Who Falls for Phish? A Demographic Analysis of Phishing 
Susceptibility and Effectiveness of Interventions](http://lorrie.cranor.org/pubs/pap1162-sheng.pdf)
1. [Before We Knew It, An Empirical Study of Zero-Day Attacks In The Real World](https://users.ece.cmu.edu/~tdumitra/public_documents/bilge12_zero_day.pdf)
1. [Detecting and Preventing Cyber
Insider Threats: A Survey](http://www.nsclab.org/yang/publications/08278157.pdf)
1. [Zero Days, Thousands of Nights, The Life and Times of Zero-Day Vulnerabilities and Their Exploits](https://paper.seebug.org/papers/Security%20Conf/Blackhat/2017_us/us-17-Ablon-Bug-Collisions-Meet-Government-Vulnerability-Disclosure-Zero-Days-Thousands-Of-Nights-RAND.pdf)
1. [Hack Back, A DIY Guide](http://pastebin.com/raw/0SNSvyjJ)
1. [Broken Browser](https://www.brokenbrowser.com/)
1. [Sony Pictures hack](https://en.wikipedia.org/wiki/Sony_Pictures_hack)
1. [Calculating the Cost of a Data Breach in 2018, the Age of AI and the IoT](https://securityintelligence.com/ponemon-cost-of-a-data-breach-2018/)
1. [Foreign spies stealing US economic secrets in cyberspace](https://www.dni.gov/files/documents/Newsroom/Reports%20and%20Pubs/20111103_report_fecie.pdf)
1. [ACSC 2015 Threat Report](https://www.acsc.gov.au/publications/ACSC_Threat_Report_2015.pdf)
1. [Secret Code Found in Juniper&#x27;s Firewalls Shows Risk of Government Backdoors](https://www.wired.com/2015/12/juniper-networks-hidden-backdoors-show-the-risk-of-government-backdoors/)
1. [The Year's 11 Biggest Hacks, From Ashley Madison to OPM](https://www.wired.com/2015/12/the-years-11-biggest-hacks-from-ashley-madison-to-opm/)
1. [The Impact of Cybercrime on Belgian Businesses](http://www.belspo.be/belspo/fedra/BR/BCC_ImpactCybercrimeBelgianBusinesses.pdf)
1. [Cyber security breaches survey 2017](https://assets.publishing.service.gov.uk/government/uploads/system/uploads/attachment_data/file/609186/Cyber_Security_Breaches_Survey_2017_main_report_PUBLIC.pdf)
1. [Cyber security breaches survey 2018](https://assets.publishing.service.gov.uk/government/uploads/system/uploads/attachment_data/file/702074/Cyber_Security_Breaches_Survey_2018_-_Main_Report.pdf)
1. [Spionage, Sabotage, Datendiebstahl: Deutscher Wirtschaft entsteht jährlich ein Schaden von 55 Milliarden Euro](https://www.verfassungsschutz.de/de/oeffentlichkeitsarbeit/presse/pm-20170721-bfv-bitkom-vorstellung-studie-wirtschaftsspionage-sabotage-datendiebstahl)
1. [Cost of a Data Breach Study](https://www.ibm.com/security/data-breach)
