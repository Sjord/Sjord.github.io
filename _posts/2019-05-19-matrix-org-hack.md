---
layout: post
title: "Matrix.org hack"
thumbnail: matrix-480.jpg
date: 2020-01-01
---

Matrix.org develops standards and software for messaging and other online communication. In April 2019, Matrix.org was hacked. Starting from a public Jenkins with a months-old bug, the attacker quickly gained full access to all servers the developers could access. Both Matrix.org and the attacker reflected on the attack, making this an interesting hack to learn from.

<!-- photo source: https://pixabay.com/photos/cube-digital-matrix-green-447989/, https://pixabay.com/illustrations/matrix-computer-hacker-code-2354492/ -->

## Compromise

Even though the initial point of compromise is obvious, the attacker took some more steps to gain more privileges and keep those privileges. But first let's discuss how the attacker gained a foothold in the network.

### Entry through Jenkins

> We were using Jenkins for continuous integration (automatically testing our software). The version of Jenkins we were using had a vulnerability ([CVE-2019-1003000](https://nvd.nist.gov/vuln/detail/CVE-2019-1003000), [CVE-2019-1003001](https://nvd.nist.gov/vuln/detail/CVE-2019-1003002), [CVE-2019-1003002](https://nvd.nist.gov/vuln/detail/CVE-2019-1003002)) which allowed an attacker to hijack credentials (forwarded ssh keys), giving access to our production infrastructure.

Jenkins is a build server, that is meant to run tests and deploy code. It is meant to run custom code (such as unit tests), so <abbr title="remote code execution">RCE</abbr> is one of its features. To run custom code somewhat securely, all execution is performed in a sandbox. Code is not supposed to be able to access things outside of the sandbox. However, the CVEs mentioned above are vulnerabilities in the sandbox functionality, making it possible to access files on the Jenkins server itself.

These vulnerabilities were disclosed in January 2019, and were exploited in April 2019. This would have been enough time to update Jenkins to the latest version, but Matrix didn't perform regular updates and were only aware of the vulnerabilities when a security researcher pointed them out in April 2019. At this point they updated their Jenkins and checked whether the vulnerabilities had been exploited, which they were.

Since Jenkins is a complex environment which runs untrusted code for a living, it is hard to secure. Most Jenkins user have their build server only exposed internally, and not on the internet, thus greatly reducing the attack surface. Matrix was aware that exposing Jenkins on the internet was a risk, and they were willing to take that risk:

> Separately, we also made the controversial decision to maintain a public-facing Jenkins instance. We did this deliberately, despite the risks associated with running a complicated publicly available service like Jenkins...

### Compromise of Flywheel

> This allowed them to further compromise a Jenkins slave (Flywheel, an old Mac Pro used mainly for continuous integration testing of Riot/iOS and Riot/Android).

Jenkins has functionality to run jobs on other computers, for example if you want to test software on another operating system. Since the Jenkins SSH keys were compromised by the attacker, these could be used to connect to a Jenkins slave.

Even though the attacker now also had access to this computer, this was only as the Jenkins user. The next step was gaining access to other systems as root.

### Setting a trap

The attacker set up a script on the Jenkins slave to hijack any SSH agent forwarding whenever someone logs in as the Jenkins user.

SSH agent forwarding is a feature where the SSH keys on your computer can be used to set up SSH connections, even when you are working on a remote host. If you SSH to another host, the keys in ~/.ssh/ are used to identify you. However, if you want to perform another hop and SSH from that host to another host, you need agent forwarding to transfer your keys across connections. So when you use agent forwarding, you always have your keys on hand, even when you are connected to a remote host.

On 4 April 2019, a developer connected with SSH to the Jenkins slave. The attacker's script triggered, and could now perform actions using the SSH keys of the developer. Even though the script could not *read* the private key, it could still *use* the private key. Since this developer had root access on all systems, the script could connect to every system and add the attacker's SSH key everywhere.

### Total compromise

Now the attacker had root access to all systems. To keep access, he used an alternative to SSH's `authorized_keys` file. The `authorized_keys` file lists which keys can SSH into a system. As discussed above, the attacker had already added his own key to this list. However, these files were centrally managed in Matrix.org's network, just like all other configuration files. When a developer changes the configuration, Ansible uploads a new `authorized_keys` to every server, overwriting the attacker's key. The hacker solved this by storing his key in `authorized_keys2`, an alternative key-file still active from when OpenSSH transitioned from SSH1 to SSH2. By storing his key in this file, it wouldn't be overwritten by the central configuration.

### Domain name takeover

By this time, Matrix.org had become aware that they were compromised. They started to revoke access to the attacker. They also started to rebuild their infrastructure. Once an attacker has been root on a system, nothing on it can't be trusted anymore. Maybe the attacker replaced /bin/ls by something else. The only way to recover is to start from scratch, which Matrix.org did. However, when they thought they had locked the attacker out, he still managed to deface the Matrix.org website.

> the attacker used a cloudflare API key to repoint DNS for matrix.org to a defacement website ... The API key was known compromised in the original attack

Even though the attacker no longer had access to the servers, he still had access to the Cloudflare interface. He used this to point the matrix.org domain no another server, which served the attacker's website.

## Lessons learned

This was an actual attack, with much information about it available. It's pretty clear how the attacker gained access in this case, and even what made it hard for him. Here are some of the lessons that can be learned from this attack.

### Limit access from the internet

Not only the Jenkins server was available on the internet, but all other servers had their SSH port exposed to the outside. This made it pretty convenient for the attacker, as he could now SSH into every server from his own location. 

> I was able to login to all servers via an internet address. There should be no good reason to have your management ports exposed to the entire internet. Consider restricting access to production to either a vpn or a bastion host.

The solution is to connect using a VPN, or a [jump server](https://en.wikipedia.org/wiki/Jump_server). This way, only the VPN server or jump server is exposed to the internet, which makes it easier to centrally manage and audit login attempts.

### Avoid agent forwarding

An important step in the attack was the compromise of a forwarded SSH agent. A developer logged in to a compromised host, and a script running on that host could use the developer's SSH keys. This is why enabling SSH agent forwarding can be pretty dangerous.

> I would like to take a moment to thank whichever developer forwarded their agent to Flywheel. Complete compromise could have been avoided if developers were prohibited from using ForwardAgent yes or not using -A in their SSH commands.

The solution to this depends on why agent forwarding was enabled in the first place. Sometimes, the solution is to simply use more keys. Instead of using one keypair for everything, multiple keypairs can give access to multiple services.

Sometimes agent forwarding is enabled to perform multiple hops: SSH into host A, and then from host A to host B. An alternative to agent forwarding in this case would be to use ProxyJump. This is a SSH feature that is specifically made for this case.

### Use two-factor authentication

In the attack, the obtained keys were directly usable to gain root on other systems. By requiring another authentication step, this wouldn't be possible.

> 2FA is often touted as one of the best steps you can take for securing your servers, and for good reason!

### Restrict privileges

The developers had root access to all servers. This greatly increases the impact of a compromised account.

> Escalation could have been avoided if developers only had the access they absolutely required and did not have root access to all of the servers.

Even if developers need access to the server to perform administration, it may help to logically split up accounts. For example, Joe can have a joe-develop account for this normal day-to-day development work, and a joe-admin account to perform administration.

### Encrypt passwords

Once a system is compromised, the first step for an attacker if often to obtain API keys and passwords to gain access to other services. Matrix.org used [Passbolt](https://passbolt.com/), a password manager that securely stores credentials. The attacker could not access the credentials, even though he was root on multiple servers. This mitigated the impact of the attack.

> Kudos on using Passbolt.

### Manage configuration centrally

Even though Matrix.org used Ansible to centrally manage configuration, the attacker could add his own SSH key as authorized key to all servers. The central configuration manager should be the only way that keys can be added, and restricting the files used for authorized keys makes it harder on the attacker.

### Update your software

The attacker gained access through a publicly known Jenkins bug. This was once a zero-day vulnerability, but was now several months old, prompting the attacker to add a new word to vulnerability lingo:

> Well okay, and that jenkins 0ld-day.

Keeping software up to date and keeping in the loop about security vulnerabilities in software you use is very important for a secure infrastructure.

### Detect hacks

Matrix.org only noticed that they had been hacked when they were notified by an external security researcher. [Someone](https://twitter.com/JaikeySarraf) notified Matrix.org through Twitter that their Jenkins contained several critical bugs. This started an investigation as to whether Matrix.org has been hacked, which turned out to be the case. At this time, the attacker was already root on many systems and had extracted most of the production database, without triggering any alarm bells.

> This could have been detected by better monitoring of log files and alerting on anomalous behavior.

## Conclusion

Even though Jenkins gave a foothold into the Matrix.org network, the attack really gained steam with the hijack of the forwarded SSH agent. The lack of detection and defense-in-depth gave the attacker opportunity to gain root and extract confidential information without any problems. Certainly a case to learn from, so that it can be prevented in the future.

## Read more

About the hack:

* [Post-mortem and remediations for Apr 11 security incident](https://matrix.org/blog/2019/05/08/post-mortem-and-remediations-for-apr-11-security-incident)
* [Matrix.org hacked? #9435](https://github.com/vector-im/riot-web/issues/9435)

About agent forwarding:

* [SSH Agent Hijacking](https://www.clockwork.com/news/2012/09/28/602/ssh_agent_hijacking/)
* [SSH Agent Forwarding Vulnerability and Alternative](https://blog.wizardsoftheweb.pro/ssh-agent-forwarding-vulnerability-and-alternative/)
* [The Problem with SSH Agent Forwarding](https://defn.io/2019/04/12/ssh-forwarding/)
* [SSH Agent Forwarding considered harmful](https://heipei.io/2015/02/26/SSH-Agent-Forwarding-considered-harmful/)
