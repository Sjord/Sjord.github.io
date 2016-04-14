---
layout: post
title: "XSS in user-agent header in Bolt CMS"
thumbnail: bolt-240.jpg
date: 2016-04-14
---

Recently I tried to find vulnerabilities in Bolt CMS 2.2. Bolt has a public section that shows the web site, and a backend for admins to modify the web site. I was looking for a way to get into the backend without valid credentials.

First thing I did was look at the authentication code in [Users.php](https://github.com/bolt/bolt/blob/release/2.2/src/Users.php), and try logging in and resetting my password. This seemed to be quite secure. However, when I tried this a couple of times I noticed that my actions were logged and shown in the admin backend:

![Some logging information in Bolt CMS](/images/bolt-latest-system-activity.png)

I tried logging in with user `<b>bold</b>`, in the hope that the backend would show this username and interpret it as HTML. This turned out not to be the case, but I found another place that an attacker can use to store some HTML. The backend shows the user-agent in the list of current sessions, and the contents of the tooltip are interpreted as HTML. This allows for an attacker to log in and send a malicious user-agent header:

    User-Agent: Mozilla 5.0 <b>bold</b>

This results in the tooltip rendering HTML:

![Tooltip shows bold styled text](/images/bolt-current-sessions.png)

Besides bold text, this can also be used to include iframes or run Javascript. However, this only works if the attacker has a valid login, in which case he already has access to the backend.

I notified the Bolt developers on April 13 and they [fixed it](https://github.com/bolt/bolt/pull/5179) the same day.
