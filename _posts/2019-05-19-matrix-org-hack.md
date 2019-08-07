---
layout: post
title: "Matrix.org hack"
thumbnail: matrix-480.jpg
date: 2020-01-01
---

<!-- photo source: https://pixabay.com/photos/cube-digital-matrix-green-447989/, https://pixabay.com/illustrations/matrix-computer-hacker-code-2354492/ -->

> We were using Jenkins for continuous integration (automatically testing our software). The version of Jenkins we were using had a vulnerability (CVE-2019-1003000, CVE-2019-1003001, CVE-2019-1003002) which allowed an attacker to hijack credentials (forwarded ssh keys), giving access to our production infrastructure.

> Separately, we also made the controversial decision to maintain a public-facing Jenkins instance. We did this deliberately, despite the risks associated with running a complicated publicly available service like Jenkins...

> I was able to login to all servers via an internet address. There should be no good reason to have your management ports exposed to the entire internet. Consider restricting access to production to either a vpn or a bastion host.

hole punching / port knocking

> Complete compromise could have been avoided if developers were prohibited from using ForwardAgent yes or not using -A in their SSH commands.

ProxyJump

> 2FA is often touted as one of the best steps you can take for securing your servers, and for good reason!

> Escalation could have been avoided if developers only had the access they absolutely required and did not have root access to all of the servers. 

> I would like to take a moment to thank whichever developer forwarded their agent to Flywheel.

> This allowed them to further compromise a Jenkins slave (Flywheel, an old Mac Pro used mainly for continuous integration testing of Riot/iOS and Riot/Android).



> Kudos on using Passbolt.

> But sshd_config allowed me to keep keys in authorized_keys2 and not have to worry about ansible locking me out.

> Well okay, and that jenkins 0ld-day.



> This could have been detected by better monitoring of log files and alerting on anomalous behavior.


> the attacker used a cloudflare API key to repoint DNS for matrix.org to a defacement website ... The API key was known compromised in the original attack


* [SSH Agent Hijacking](https://www.clockwork.com/news/2012/09/28/602/ssh_agent_hijacking/)
* [SSH Agent Forwarding Vulnerability and Alternative](https://blog.wizardsoftheweb.pro/ssh-agent-forwarding-vulnerability-and-alternative/)
* [The Problem with SSH Agent Forwarding](https://defn.io/2019/04/12/ssh-forwarding/)
* [SSH Agent Forwarding considered harmful](https://heipei.io/2015/02/26/SSH-Agent-Forwarding-considered-harmful/)
* [Post-mortem and remediations for Apr 11 security incident](https://matrix.org/blog/2019/05/08/post-mortem-and-remediations-for-apr-11-security-incident)
* [Matrix.org hacked? #9435](https://github.com/vector-im/riot-web/issues/9435)