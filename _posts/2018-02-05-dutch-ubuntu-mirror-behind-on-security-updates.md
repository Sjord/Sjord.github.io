---
layout: post
title: "Ubuntu mirror NLUUG behind on security updates"
thumbnail: mirror-240.jpg
date: 2018-02-07
---

Ubuntu retrieves packages from a package repository. There repositories are mirrored by several hosts on the internet. I discovered that one mirror was running behind, resulting in missing security updates for over six months.

## Installation problems

When I tried to install perl in a Docker environment, it complained that the packages `perl` and `perl-base` were incompatible versions. At first I thought this to be [a bug in perl](https://bugs.launchpad.net/ubuntu/+source/perl/+bug/1746799?comments=all), but closer inspection made it clear to be a problem with the security repository.

I have some [Docker images](https://github.com/Sjord/docker-images) which I use for development. By base Ubuntu is based on the [Ubuntu image from Docker Hub](https://hub.docker.com/_/ubuntu/). When I build my Apache image, this happens:

1. Start with the upstream Ubuntu image. This has security updates installed from the security repository. It has `perl-base` installed but not `perl`.
2. Change the `/etc/apt/sources.list` to a local mirror to speed things up. I used the [NLUUG mirror](https://launchpad.net/ubuntu/+mirror/ftp.nluug.nl-archive).
3. Install apache, which depends on perl. This failed because `perl-base` was an incompatible version.

So `perl-base` is installed from another source as the `perl` package. That these two packages were incompatible indicated that the mirror did not serve the correct version. Switching mirrors solved the problem.

## No security updates

When I looked at the `xenial-security` repository, it seemed that the lasted update was in June 2017. 

<img src="/images/nluug-xenial-security.png" alt="Last modified on 19 June 2017">

This means that users of this mirror didn't receive security updates since June 2017. This has been resolved on February 2nd 2018.

## Conclusion

Even if you upgrade regularly, you may miss security updates if the upstream mirror doesn't work correctly. This went unnoticed for quite some time.
