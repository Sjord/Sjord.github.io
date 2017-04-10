---
layout: post
title: "HTTPS does not provide privacy"
thumbnail: opening-letter-240.jpg
date: 2016-11-21
---

If you visit a web site over HTTPS, all traffic between the client and the server is encrypted. This does not mean that somebody that intercepts the connection can't see which pages you are visiting. This post describes what is visible over an HTTPS connection and why.

## Situation description

Assume that in dictatorial Carpathia it is frowned upon to visit pages with republican content. The Carpathian government did not block any web site, but closely monitors all Internet traffic in the country. They only listen in and perform no active attacks. Can the government find out if one of the citizens retrieves a republican page? Even if it is served over HTTPS?

## The host name is not encrypted

If you type an URL in the address bar, the browser first has to resolve the DNS name of the host to find out which server to talk to. Your computer will ask the DNS server the IP address for the host you want to visit. Since this DNS request is not encrypted, anyone listening in on the network traffic will see the host name you try to connect to.

After this step, your browser connects to the IP address. Whether the host name can be determined depends on whether there are multiple sites hosted on the IP address. If there is only one site on the IP address, it is pretty clear that you visited that site.

Next, if the web site supports HTTPS a TLS handshake is performed. Modern browsers will sent the host name again as plaintext in this handshake. This gives the opportunity for the server to use the correct certificate. Any passive listener can see which hostname you are connecting to.

So it is clear that the citizens cannot hide the host name from the Carpathian intelligence agency by using HTTPS.

## The rest of the URL is encrypted

The host name is sent in plain text, but after that all communication is encrypted. This includes the URL, so any intercepting attacker can not read it. However, there may be other ways to determine which pages you visit on a particular web site. After all, the attacker can still see how much traffic you send and when you send it. The [TLS specification](https://tools.ietf.org/html/rfc5246#section-6) even explicitly states that it does not protect everything:

>  Note in particular that type and length of a record are not protected
   by encryption.  If this information is itself sensitive, application
   designers may wish to take steps (padding, cover traffic) to minimize
   information leakage.

Is it possible to determine which page you visited from TLS traffic alone?

## Metadata analysis

Let's try to determine which page someone is visiting just by looking at the size of the encrypted traffic. First, we build a reference by visiting each page on the site and noting the size of the response. In this case, I used Wireshark to plot a graph while I visited this web site:

![A graph showing several numbered peaks, one for each page](/images/traffic-analysis-reference.png)

The peaks here correspond to specific pages. For example, peak 0 corresponds to the [home page](/), peak 1 corresponds to [the article about certificate transparency](/2016/11/14/economics-of-certificate-transparency/), and so on. We now know the size of each page, and can intercept some traffic. Assume we see this traffic of a Carpathian citizen:

![A graph showing several lettered peaks](/images/traffic-analysis-actual.png)

We see four peaks, some of which we can identify using our reference graph:

* Peak a could correspond to peak 0, even though the sizes differ quite a bit.
* Peak b and d probably correspond to one of the smaller peaks, 1, 2, 5 or 9.
* Peak c, about 150.000 bytes, could correspond to peak 3.

This visitor actually visited pages 0, 2, 3 and 9. So all of the above statements we inferred from the traffic are true.

The method of looking at response sizes is already effective by using Wireshark and looking at graphs, but it can be greatly improved. In 2013, [several researchers](https://arxiv.org/pdf/1403.0297v1.pdf) created statistical models for several sites to determine which page was visited by sniffing encrypted data. With one method, they attained 89% accuracy on average. [Another study](https://www.microsoft.com/en-us/research/wp-content/uploads/2016/02/WebAppSideChannel-final.pdf) could determine what you typed in a search field of an encrypted site, by looking at the response size of the autocomplete functionality.

## Conclusion

Even with HTTPS encrypted traffic, passive attackers can still determine which pages a user visits just by looking at traffic properties. HTTPS does not sufficiently protect the privacy of site visitors. 

## Learn more

* [Identifying HTTPS-Protected Netflix Videos in Real-Time](http://www.mjkranch.com/docs/CODASPY17_Kranch_Reed_IdentifyingHTTPSNetflix.pdf), 2017
* [I Know Why You Went to the Clinic: Risks and Realization of HTTPS Traffic Analysis](https://arxiv.org/pdf/1403.0297v1.pdf), 2013
* [Side-Channel Leaks in Web Applications: a Reality Today, a Challenge Tomorrow ](https://www.microsoft.com/en-us/research/wp-content/uploads/2016/02/WebAppSideChannel-final.pdf), 2009
* Video of the talk [Calm down, HTTPS is not a VPN](https://www.youtube.com/watch?v=6ZiJvlMeb-E) by Dirk Wetter.
* The book [Bulletproof SSL and TLS](https://www.amazon.com/gp/product/1907117040/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&tag=sjoerdlangkem-20&camp=1789&creative=9325&linkCode=as2&creativeASIN=1907117040&linkId=3471bbb4e27a1556cfc083a8699545fd)
