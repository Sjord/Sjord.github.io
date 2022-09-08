---
layout: post
title: "Discovering subdomains"
thumbnail: discovering-240.jpg
date: 2018-06-20
---

Early in a pentest it may be helpful to enumerate all the subdomains of a domain in scope. This article lists some tools that do that.

<!-- photo source: http://www.mildenhall.af.mil/News/Article-Display/Article/273410/digging-up-bones-archeologists-discover-human-remains/ -->

## Tools

* [altdns](https://github.com/infosec-au/altdns)
* [amass](https://github.com/caffix/amass)
* [anubis](https://github.com/jonluca/Anubis)
* [aquatone](https://github.com/michenriksen/aquatone)
* [bluto](https://github.com/darryllane/Bluto)
* [censys-subdomain-finder](https://github.com/christophetd/censys-subdomain-finder)
* [Cleveridge Subdomain Scanner](https://github.com/Cleveridge/cleveridge-subdomain-scanner)
* [ct-exposer](https://github.com/chris408/ct-exposer)
* [DMitry](https://mor-pah.net/software/dmitry-deepmagic-information-gathering-tool/)
* [dnscan](https://github.com/rbsec/dnscan)
* [dnsenum.pl](https://github.com/fwaeytens/dnsenum)
* [dnsrecon](https://github.com/darkoperator/dnsrecon)
* [Domain analyzer](https://github.com/eldraco/domain_analyzer)
* [DomainRecon](https://github.com/x73x61x6ex6ax61x79/DomainRecon)
* [Fierce](https://github.com/davidpepper/fierce-domain-scanner)
* [Fierce](https://github.com/mschwager/fierce)
* [findomain](https://github.com/Edu4rdSHL/findomain)
* [gobuster](https://github.com/OJ/gobuster)
* [Knockpy](https://github.com/guelfoweb/knock)
* [ldns-walk](https://linux.die.net/man/1/ldns-walk)
* [massdns](https://github.com/blechschmidt/massdns)
* [nmap dns-brute](https://nmap.org/nsedoc/scripts/dns-brute.html)
* [nsec3walker](https://dnscurve.org/nsec3walker.html)
* [recon-ng](https://bitbucket.org/LaNMaSteR53/recon-ng)
* [subbrute](https://github.com/TheRook/subbrute)
* [subEnum](https://github.com/itsKindred/subEnum)
* [SubFinder](https://github.com/ice3man543/subfinder)
* [Sublist3r](https://github.com/aboul3la/Sublist3r)
* [subquest](https://github.com/skepticfx/subquest)
* [SubScraper](https://github.com/m8r0wn/subscraper)
* [xray](https://github.com/evilsocket/xray)

## Websites

* [crt.sh](https://crt.sh/)
* [DNSDumpster](https://dnsdumpster.com/)
* [Entrust Certificate Transparency Search Tool](https://www.entrust.com/ct-search/)
* [FindSubDomains](https://findsubdomains.com/)
* [Robtex](https://www.robtex.com/)
* [SecurityTrails](https://securitytrails.com/)
* [VirusTotal](https://www.virustotal.com/)

## Datasets

* [Certificate transparency logs](https://www.certificate-transparency.org/known-logs)
* [SecLists brute-force lists](https://github.com/danielmiessler/SecLists/tree/master/Discovery/DNS)
* [Sonar forward DNS](https://opendata.rapid7.com/sonar.fdns_v2/)

## Discovering domains

I most often use Sublist3r to enumerate subdomains. If I want something custom, I write a loop in bash:

    for i in `seq 1 100`; do host server$i.example.com; done

## Keeping secret domains secret

If you are working for a client, keep in mind that some tools may expose the found domain names. Earlier versions of Anubis, for example, would send all found domains to a central database. Also, querying for a domain on a website such as VirusTotal may expose that domain to others.

## Don't write another tool

As you can see, the list above is pretty long. If you are thinking about building yet another tool, consider contributing to any of the listed projects intead.
