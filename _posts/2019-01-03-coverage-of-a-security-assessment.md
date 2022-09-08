---
layout: post
title: "Coverage of a security assessment"
thumbnail: umbrellas-480.jpg
date: 2019-03-13
---

When performing a typical security assessment, what percentage of all security vulnerabilities is found?

## Estimating coverage

A security assessment does not find _all_ security vulnerabilities in an application. The security assessment is limited by time, and the assessor may not discover all functionality within the application.

What the actual coverage is depends on the application and the specifics of the security assessment. I typically have one week to perform an test on a web application, where I get credentials but not the source code.

Although I could not find studies that research this particular question, there are some studies on automated vulnerability scanners which gives us some information. Take a look at the following Venn-diagrams, which compare several vulnerability scanners:

<img src="/images/venn-antunes2009.png" width="300">
<img src="/images/venn-finifter2011.png" width="300">
<img src="/images/venn-fonseca2007.png" width="680">

What is interesting is that in the first diagram the "experts" found all issues, in the second their findings overlap partially with the automated tool, and in the last diagram the manual scan does not overlap at all. These varying numbers make it hard to draw an obvious conclusion. Furthermore, we are interested in what part of _all_ vulnerabilities were found, and the Venn diagrams only provide numbers for discovered vulnerabilities.

Nevertheless, we can try to get some numbers from the last diagram. We'll combine the numbers from the manual scan with a vulnerability scanner, since in a typical security assessment, the assessor does not rely on manual scanning alone, but combines manual testing with automated testing. From the venn diagram above we can see that if we combine the manual scans with the results from vulnerability scanner 1, we find 68 of the 117 issues, or about 58%. For vulnerability scanner 2 this is 38%, and for 3 it is 77%.

## Coverage per class

For a security assessment, you don't have to find all instances of a vulnerability. If a form is vulnerable to CSRF, it is likely that all forms are vulnerable to CSRF. If you only test a couple of forms and report CSRF, the software developer can solve CSRF throughout the application and all instances are solved. In that case, it doesn't matter that you didn't reach 100% coverage.

## Invisible security issues

Many issues are impossible to test without the source code. For example, the way passwords are stored is important to web application security. Passwords should be properly hashed using a slow hash function. If this is not done properly, it is definitely a security vulnerability. However, most of the time it is not possible to see how passwords are hashed by just hands-on testing the application without the source code.

## Influences on coverage

* Time. Taking more time for an assessment increases the coverage.
* Application size and complexity. If the application is very big or complex, vulnerable functionality may be hidden deep in the application.
* Source code. Having source code available increases the visibility of security vulnerabilities.
* Environment. Testing on production reduces the possible tests that can be done, lowering the coverage. A test environment that is different from production may in turn hide vulnerabilities that are present on the production environment and not in the test environment. An acceptance environment that is similar to production gives the highest coverage.

## Conclusion

A security assessment may find only half of the present security vulnerabilities. 

## Read more

* [Testing and comparing web vulnerability scanning tools for SQL injection and XSS attacks](http://bdigital.ipg.pt/dspace/bitstream/10314/3533/1/Fonseca-CompSQLXSS.pdf), Fonseca, Vieira, Madiera, 2007
* [A quantitative evaluation of vulnerability scanning](https://www.diva-portal.org/smash/get/diva2:545791/FULLTEXT01.pdf), Holm, Sommestad, Almroth, Persson, 2011
* [One Technique is Not Enough: A Comparison of Vulnerability Discovery Techniques](/papers/2011/one-technique-is-not-enough-a-comparison-of-vulnerability-discovery-techniques-austin-williams.pdf), Austin, Williams, 2011
* [Comparing the Effectiveness of Penetration Testing and Static Code Analysis on the Detection of SQL Injection Vulnerabilities in Web Services](https://www.researchgate.net/profile/Marco_Vieira/publication/224095815_Comparing_the_Effectiveness_of_Penetration_Testing_and_Static_Code_Analysis_on_the_Detection_of_SQL_Injection_Vulnerabilities_in_Web_Services/links/00b7d52c72ec1cd659000000/Comparing-the-Effectiveness-of-Penetration-Testing-and-Static-Code-Analysis-on-the-Detection-of-SQL-Injection-Vulnerabilities-in-Web-Services.pdf), Antunes, Vieira, 2009
* [Hackers vs. Testers: A Comparison of Software Vulnerability Discovery Processes](https://rud.is/dl/ieee-sp-2018/435301a134.pdf), Votipka et. al. 2018
* [A comparison of the efficiency and effectiveness of vulnerability discovery techniques](/papers/2013/a-comparison-of-the-efficiency-and-effectiveness-of-vulnerability-discovery-techniques-austin.pdf), Austin, Holmgreen, Williams, 2013
* [Exploring the Relationship Between Web Application Development Tools and Security](https://www.usenix.org/legacy/event/webapps11/tech/final_files/Finifter.pdf), Finifter, Wagner, 2011
