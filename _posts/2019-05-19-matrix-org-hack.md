---
layout: post
title: "Matrix.org hack"
thumbnail: matrix-480.jpg
date: 2020-01-01
---

> We were using Jenkins for continuous integration (automatically testing our software). The version of Jenkins we were using had a vulnerability (CVE-2019-1003000, CVE-2019-1003001, CVE-2019-1003002) which allowed an attacker to hijack credentials (forwarded ssh keys), giving access to our production infrastructure.

> I was able to login to all servers via an internet address. There should be no good reason to have your management ports exposed to the entire internet. Consider restricting access to production to either a vpn or a bastion host.

hole punching / port knocking

> Complete compromise could have been avoided if developers were prohibited from using ForwardAgent yes or not using -A in their SSH commands.

ProxyJump

> 2FA is often touted as one of the best steps you can take for securing your servers, and for good reason!

> Escalation could have been avoided if developers only had the access they absolutely required and did not have root access to all of the servers. 

> I would like to take a moment to thank whichever developer forwarded their agent to Flywheel.




> Kudos on using Passbolt.

> But sshd_config allowed me to keep keys in authorized_keys2 and not have to worry about ansible locking me out.

> Well okay, and that jenkins 0ld-day.



> This could have been detected by better monitoring of log files and alerting on anomalous behavior.


> the attacker used a cloudflare API key to repoint DNS for matrix.org to a defacement website ... The API key was known compromised in the original attack
