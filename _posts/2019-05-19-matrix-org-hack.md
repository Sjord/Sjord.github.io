---
layout: post
title: "Matrix.org hack"
thumbnail: matrix-480.jpg
date: 2020-01-01
---

<!-- photo source: https://pixabay.com/photos/cube-digital-matrix-green-447989/, https://pixabay.com/illustrations/matrix-computer-hacker-code-2354492/ -->

## Compromise

### Entry through Jenkins

> We were using Jenkins for continuous integration (automatically testing our software). The version of Jenkins we were using had a vulnerability ([CVE-2019-1003000](https://nvd.nist.gov/vuln/detail/CVE-2019-1003000), [CVE-2019-1003001](https://nvd.nist.gov/vuln/detail/CVE-2019-1003002), [CVE-2019-1003002](https://nvd.nist.gov/vuln/detail/CVE-2019-1003002)) which allowed an attacker to hijack credentials (forwarded ssh keys), giving access to our production infrastructure.

Jenkins is a build server, that is meant to run tests and deploy code. It is meant to run custom code (such as unit tests), so <abbr title="remote code execution">RCE</abbr> is one of its features. To run custom code somewhat securely, all execution is performed in a sandbox. Code is not supposed to be able to access things outside of the sandbox. However, the CVEs mentioned above are vulnerabilities in the sandbox functionality, making it possible to access files on the Jenkins server itself.

These vulnerabilities were disclosed in January 2019, and were exploited in April 2019. This would have been enough time to update Jenkins to the latest version, but Matrix didn't perform regular updates and were only aware of the vulnerabilities when a security researcher pointed them out in April 2019. At this point they updated their Jenkins and checked wheter the vulnerabilities had been exploited, which they were.

Since Jenkins is a complex environment which runs untrusted code for a living, it is hard to secure. Most Jenkins user have their build server only exposed internally, and not on the internet, thus greatly reducing the attack surface. Matrix was aware that exposing Jenkins on the internet was a risk, and they were willing to take that risk:

> Separately, we also made the controversial decision to maintain a public-facing Jenkins instance. We did this deliberately, despite the risks associated with running a complicated publicly available service like Jenkins...

### Compromise of Flywheel

> This allowed them to further compromise a Jenkins slave (Flywheel, an old Mac Pro used mainly for continuous integration testing of Riot/iOS and Riot/Android).

### Setting a trap



### Domain name takeovers

> the attacker used a cloudflare API key to repoint DNS for matrix.org to a defacement website ... The API key was known compromised in the original attack


## Lessons learned

### Limit access from the internet

> I was able to login to all servers via an internet address. There should be no good reason to have your management ports exposed to the entire internet. Consider restricting access to production to either a vpn or a bastion host.

hole punching / port knocking

### Avoid agent forwarding

> I would like to take a moment to thank whichever developer forwarded their agent to Flywheel.

> Complete compromise could have been avoided if developers were prohibited from using ForwardAgent yes or not using -A in their SSH commands.

ProxyJump

### Use two-factor authentication

> 2FA is often touted as one of the best steps you can take for securing your servers, and for good reason!

### Restrict privileges

> Escalation could have been avoided if developers only had the access they absolutely required and did not have root access to all of the servers. 

### Encrypt passwords

> Kudos on using Passbolt.

### Manage configuration centrally

> But sshd_config allowed me to keep keys in authorized_keys2 and not have to worry about ansible locking me out.

### Update your software

> Well okay, and that jenkins 0ld-day.

### Detect hacks

> This could have been detected by better monitoring of log files and alerting on anomalous behavior.

## Conclusion


* [SSH Agent Hijacking](https://www.clockwork.com/news/2012/09/28/602/ssh_agent_hijacking/)
* [SSH Agent Forwarding Vulnerability and Alternative](https://blog.wizardsoftheweb.pro/ssh-agent-forwarding-vulnerability-and-alternative/)
* [The Problem with SSH Agent Forwarding](https://defn.io/2019/04/12/ssh-forwarding/)
* [SSH Agent Forwarding considered harmful](https://heipei.io/2015/02/26/SSH-Agent-Forwarding-considered-harmful/)
* [Post-mortem and remediations for Apr 11 security incident](https://matrix.org/blog/2019/05/08/post-mortem-and-remediations-for-apr-11-security-incident)
* [Matrix.org hacked? #9435](https://github.com/vector-im/riot-web/issues/9435)