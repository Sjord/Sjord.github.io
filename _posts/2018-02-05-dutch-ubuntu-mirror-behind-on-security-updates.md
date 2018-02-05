---
layout: post
title: "Ubuntu mirrors behind on security updates"
thumbnail: mirror-240.jpg
date: 2018-02-07
---

Ubuntu retrieves packages from a package repository. These repositories are mirrored by several hosts on the internet. Even though the security pocket is also mirrored, you aren't supposed to use it to avoid mirror delays in your security updates. I wasn't aware of this, and I discovered that I was using a mirror that was running behind, resulting in missing security updates for over six months.

## Installation problems

When I tried to install perl in a Docker environment, it complained that the packages `perl` and `perl-base` were incompatible versions. At first I thought this to be [a bug in perl](https://bugs.launchpad.net/ubuntu/+source/perl/+bug/1746799?comments=all), but closer inspection made it clear to be a problem with the security repository.

I have some [Docker images](https://github.com/Sjord/docker-images) which I use for development. By base Ubuntu is based on the [Ubuntu image from Docker Hub](https://hub.docker.com/_/ubuntu/). When I build my Apache image, this happens:

1. Start with the upstream Ubuntu image. This has security updates installed from the security repository. It has `perl-base` installed but not `perl`.
2. Change the `/etc/apt/sources.list` to a local mirror to speed things up. I used the [NLUUG mirror](https://launchpad.net/ubuntu/+mirror/ftp.nluug.nl-archive).
3. Install apache, which depends on perl. This failed because `perl-base` was an incompatible version.

So `perl-base` is installed from another source as the `perl` package. That these two packages were incompatible indicated that the mirror did not serve the correct version. Switching mirrors solved the problem.

## No security updates

When I looked at the `xenial-security` repository, it seemed that the last update was in June 2017. 

<img src="/images/nluug-xenial-security.png" alt="Last modified on 19 June 2017">

This means that when using this mirror for the security pocket, you didn't receive security updates since June 2017. This has been resolved on February 2nd 2018.

## Finding more dusty mirrors

Are there any other mirrors that are behind in their security updates? Let's write [a script](https://github.com/Sjord/mirrorcheck) to find out. Using Python we retrieve the [mirror list](https://launchpad.net/ubuntu/+archivemirrors) and using [BeautifulSoup](https://www.crummy.com/software/BeautifulSoup/bs4/doc/) we extract all links with the text "http":

    def get_http_mirrors():
        response = requests.get("https://launchpad.net/ubuntu/+archivemirrors")
        soup = bs4.BeautifulSoup(response.content, 'html5lib')
        return [a['href'] for a in soup.find_all('a', text='http')]

Then, we walk through this list and get the directory listing for the xenial-security directory. We use the [htmllistparse](https://github.com/gumblex/htmllisting-parser) module to parse the page:

    def check_xenial_security_date(mirror):
        cwd, listing = htmllistparse.fetch_listing(mirror + "/dists/xenial-security/main/binary-amd64/")
        latest = max(listing, key=lambda x: x.modified)
        print(mirror, latest.modified)

This results in quite a few out-of-date mirrors. The following were last updated in 2016:

* [Université du Québec à Montréal - clibre](http://mirror.clibre.uqam.ca/ubuntu/dists/xenial-security/main/binary-amd64/)
* [Northeastern University, China](http://mirror.neu.edu.cn/ubuntu/dists/xenial-security/main/binary-amd64/)
* [Tuxinator, Germany](http://mirror2.tuxinator.org/ubuntu/dists/xenial-security/main/binary-amd64/)
* [Faraso Samaneh Pasargad, Iran](http://mirror.faraso.org/ubuntu/dists/xenial-security/main/binary-amd64/)
* [NeowizGames, Korea](http://ftp.neowiz.com/ubuntu/dists/xenial-security/main/binary-amd64/)
* [Gulf University for Science & Technology, Kuwait](http://repo.gust.edu.kw/ubuntu/dists/xenial-security/main/binary-amd64/)
* [Net Access, United States](http://ubuntuarchive.mirror.nac.net/dists/xenial-security/main/binary-amd64/)
* [DigiStar, Vietnam](http://mirror.digistar.vn/ubuntu/dists/xenial-security/main/binary-amd64/)

## Solution

To avoid this situation, it is recommended to use `security.ubuntu.com` for the security pocket, and not any of the mirrors. Still, I would feel better if the mirrors either didn't mirror the security pocket, or kept up to date.

## Conclusion

Even if you upgrade regularly, you may miss security updates if the mirror you use doesn't work correctly. This can remain unnoticed for quite some time. That's why you should use security.ubuntu.com and not some mirror for the security pocket, as described in the [Ubuntu Security Team FAQ](https://wiki.ubuntu.com/SecurityTeam/FAQ):

> While packages are copied from security to updates frequently, it is recommended that systems always have the security pocket enabled, and use security.ubuntu.com for this pocket.

