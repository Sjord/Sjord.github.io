---
layout: post
title: "When should a session time out?"
thumbnail: time-expired-480.jpg
date: 2023-08-16
---

<!-- Photo source: https://commons.wikimedia.org/wiki/File:Time_Expired_(237355699).jpeg -->

When logged into a web application, the session does not remain valid forever. Typically, the session expires after a fixed time after login, or after the user has been idle for some time, whatever that means. How long should these times be?

## Introduction

> Roses are red, \
> Violets are blue, \
> Sessions expire, \
> Just to spite you.

In some web applications, sessions expire. You are logged out after a while and need to authenticate. Current security advice is to use quite short session timeouts, such as after 15 minutes of inactivity. However, most big web applications such as Gmail or GitHub don't adhere to this. You can be logged in seemingly forever without reauthenticating. Are these insecure? Do Google and Microsoft know better than NIST and OWASP?

## Threat model

The threat model involves an attacker gaining unauthorized access to a user's active session. This could happen through various means, such as stealing session cookies, exploiting session fixation vulnerabilities, or by using the same device as the victim.

However, would session takeover be prevented by expiring the session after 15 minutes of inactivity?

* If an attacker steals a session cookie with XSS or session fixation, the attacker immediately gains access to a valid session and can keep performing requests to keep the session alive. An absolute timeout would limit the amount of time the attacker has, but realisticly this wouldn't really hinder any attacker.
* If an attacker sees an old session token in the logs, or on your hard drive after they steal your computer, the session timeout probably prevented session takeover.
* If the attacker gains access to the same device, the timeout could help. Unless the attacker is fast enough, the session has expired.

## Shared computers

Perhaps you used the shared computer in the library to access your web application, and forgot to log out. The next user of that computer could reopen the web application and take over your session.

Is this a thing? Are shared computers without user separation a thing? If so, these shouldn't be used to access web applications with sensitive information at all, no matter how short the session expiry time is.

Even if internet cafes still exist, some applications are used strictly within an company from company devices.

## The attacker has access to your device

You forgot to lock your computer when you went to lunch, and the attacker sat down at your desk and gained access to your machine.

In this case, session expiration may prevent them from gaining access to your session, if they weren't fast enough. However, they now have access to your email, Slack, password vault, SSH agent, and files. They don't need your active session, they can just create a new one. Either by using the password vault or using the "forgot password" to mail a password reset mail.

One situation in which access to the web application could still be prevented, if when 2FA is enabled and you took your phone or yubikey with you to lunch.

NIST says:

* [reauthentication of the subscriber SHOULD be repeated at least once per 30 days during an extended usage session](https://pages.nist.gov/800-63-3/sp800-63b.html#aal1reauth)
* [Reauthentication of the subscriber SHALL be repeated following any period of inactivity lasting 30 minutes or longer.](https://pages.nist.gov/800-63-3/sp800-63b.html#aal2reauth)
* [following any period of inactivity lasting 15 minutes or longer. Reauthentication SHALL use both authentication factors.](https://pages.nist.gov/800-63-3/sp800-63b.html#aal3reauth)

PCI DSS 3.1 in item 8.1.8 provides specific guidance on this

> 8.1.8 If a session has been idle for more than 15 minutes, require the user to re-authenticate to re-activate the terminal or session.

Websites that persist any user-specific state (either a login capability or, say, a basket & checkout purchasing system) tend to have a deliberately expiring session that lasts a few minutes or half an hour or so. I know these sessions are there to combat session-based attacks, and they are following official advice:

Insufficient session expiration by the web application increases the exposure of other session-based attacks ...

The session expiration timeout values must be set accordingly with the purpose and nature of the web application ...

Common idle timeouts ranges are 2-5 minutes for high-value applications and 15- 30 minutes for low risk applications.

(from OWASP)

And this advice is usually reiterated by the security community, e.g. in this related question.

However, these sessions are incredibly annoying. I'm sure everyone on the internet has wasted many many minutes and lost valuable work through expiring sessions. And this very poor user experience can have a very significant business impact, albeit one which is rarely recognised. Of course sessions shouldn't be ridiculously long, but the default session times are basically always short enough to be annoying to a large number of people.

And this is something that the big players have recognised. Twitter, Facebook, Google, Amazon, GitHub etc. all have sessions that basically never expire. I found one article addressing this directly, but which suggests the rest of us common folk should still maintain short session times:

Your business model is likely not geared toward keeping users engaged ...

these sites mitigate the risk of a hijacked session with several security enhancements

Remote Logout
Reauthentication Prompts
Login History
Notifications
(from Geoff Wilson)

I'm not particularly convinced by these arguments. Firstly, engagement is important to basically every website that has sessions. Whether you're trying to get someone to make a purchase or write more posts on your forum, if you have a site in the internet, you're in the engagement game. To pretend otherwise is to voluntarily cede a huge amount of power to the big players. Which is what this looks like - the megacorps are simply big enough to break the rules the rest of us are supposed to play by.

Secondly, the list of security features is nice, and things we should emulate if we can, but it hardly makes a huge difference. The number of people who check their list of logged in devices or look at their activity history must be a fraction of a percent (and re-authentication prompts are basically the same as session expiry and don't happen often in these big ecosystems, which is the point).

If you balance these arguments against the security risks, they in no way measure up. Access to your Google account is basically your whole life. You likely get someone's email (and therefore the ability to reset any of their passwords), their location, their calendar, quite possibly a Google wallet etc. etc. Facebook is only slightly less consequential. Twitter provides access to the political mouthpiece of many high-profile individuals. GitHub is the ability to manipulate software perhaps used by millions.

In comparison to that, stealing my shopping basket which I haven't even paid for yet, or the ability to pretend to be me on some esoteric hobby forum is ridiculously inconsequential. Of course, as a website's service becomes more consequential, their security should ramp up to match, using techniques like those listed above. But it seems to me that in fact the more important a sessions is, the more lax session expiry becomes. And the default advice of quick-expiring sessions hurts small players and benefits monopolies (who simply ignore it).

I've found one recent post publicly advocating extended security times

For low-risk engagements, you can provide better user experience by implementing a longer session limit, which could become the difference between keeping or losing a customer. While Auth0 focuses on making your applications more secure, we also understand the substantial value of your end-user experience. ...

You can configure session limits with up to 100 days of inactivity (idle timeout) and up to one year in total duration (absolute timeout).

(From Auth0)

My question is, is there a gradual trend here of the security community challenging the empirical value of short expiry times? Or are they still considered as important and unequivocal as they ever were?

I must have missed that time period when short expiry times were important and unequivocal. Use whatever session expiration times make sense for you and your business. – 

@ConorMancone thanks for responding! The OWASP post claims that "Common idle timeouts ranges are 2-5 minutes for high-value applications and 15- 30 minutes for low risk applications." If this is followed at all, 30 minutes is still very annoying. From my 10 years of web dev experience, this seems about right in terms of the defaults that most sites automatically follow, and from my experience of using the internet. It's possible that there are fewer short expiries out there nowadays, which is what I'm interested in. – 

All I can say is that my experience is quite differently: I rarely ever run into sites with such short expiration times, and I rarely have in the past either. I've certainly seen such sites, but (in my experience) they have always been the exception, not the norm. However it doesn't really matter what is common, and nor is there some standard "recommendation" for web security. It's just a matter of what makes the most sense for your particular application. Every company has their own risk tolerance. – 

Or to put it in practical purposes, if your website is a website to anonymously vote on cutest cat-pictures, you probably don't need it to ever timeout. If your website is used to launch nuclear strikes, then you should probably keep the timeout very short. Your needs probably lie somewhere in the middle here. – 

you are assuming that long sessions are used to persist data. This may not be the case. They could be storing things in a database, for instance. Long sessions will take up resources on the server and should be avoided as much as possible.... especially if you have a lot of traffic. – 

A simple "remember me" type cookie can be used to re-start a session... but always ask for the password again for more sensitive functions... (like changing e-mail, password, etc..) A cookie can be stolen so you should not trust it enough that it can be used to take over the account or access certain information. – 

@pcalkins Ah, well my question pertains specifically to security. As the designer of a website that makes use of sessions, I want to know if there remain strong security-based arguments for expiring user's sessions quickly (e.g. in less than a day / the active browser session) in way that may be detrimental to user experience, when the session is not hugely sensitive. I think it's best to keep this question separate from other concerns like resource constraints. I will balance those separately as needed. – 

the risk is stolen session. (which can happen via stolen cookie/cross-site attack) Just be sure to mitigate those risks. Make sure that session can't be used to gain control of the account. – 

btw, the resources thing is mainly concerning server-side session variables. There are other ways to persist a session. All are vulnerable to the risk of a stolen session. (token, cookie, etc...) – 



