---
layout: post
title: "Second order SQL injection in ZoneMinder"
thumbnail: cctv-480.jpg
date: 2019-09-25
---

While [searching for vulnerable projects](https://www.sjoerdlangkemper.nl/2017/06/07/finding-vulnerable-code-in-github-with-bigquery/) I encountered ZoneMinder, a video surveillance software system. It provides a web interface where I found several vulnerabilities, including second order SQL injection.

<!-- photo source: https://pixabay.com/photos/cctv-security-camera-1144366/ -->

## Setting up ZoneMinder

To test the web application I first set up a test environment. I first tried using a docker but later switched to a virtual machine, in order to run several services installed from one package on the same host. I used a netinst ISO to install Debian Buster and then installed the ZoneMinder deb from the default repository. After following more [installation instructions](https://zoneminder.readthedocs.io/en/stable/installationguide/debian.html#easy-way-debian-jessie) I got it running and could access the web interface with a browser.

## Searching for unauthenticated XSS

By default, authentication is disabled, which means no login is required to access the web application. After clicking through the application to see which options are available I noticed a simple feature to set the home URL and title. There is a link in the top-left corner, and the destination of this link is configurable in the application. This is a nice place to try stored XSS. Assuming that the `HOME_URL` value is put in a `<a href="…">` tag, maybe we can inject a HTML attribute by using a quote in our `HOME_URL`. And indeed, using the value `http://zoneminder.com" onmouseover="alert(1)` works and shows a JavaScript popup when we hover over the link.

<img src="/images/zoneminder-home-url-xss.png" width="100%">

However, XSS is particularly interesting if it can be used to attack higher privileged users. This is not applicable when authentication is disabled, so I enabled it. This gives a login screen when requesting the URL. After logging in and clicking through the application again, I noticed that login attempts are logged in the application's log. This can be interesting if this is also vulnerable to XSS.

We try to log in with `<h1 onmouseover="alert(1)">XSS</h1>`, and any password. Of course we get an error message that the credentials are incorrect, but now the username value is logged. When the administrator views the log, our HTML is rendered.

<img src="/images/zoneminder-login-xss-1.png" width="100%">

<img src="/images/zoneminder-login-xss-2.png" width="100%">

## Finding SQL injection

One thing I noticed while trying this is that the invalid login attempt is logged twice. In one our HTML is rendered, and in the other one the HTML is stripped. Let's look at the authentication function to see what is going on. We can find the function `userLogin` in the file [`web/includes/auth.php`](https://github.com/ZoneMinder/zoneminder/blob/1.32.3/web/includes/auth.php):

    function userLogin($username, $password='', $passwordHashed=false) {
      global $user;

      $sql = 'SELECT * FROM Users WHERE Enabled=1';
      …
      $_SESSION['username'] = $username;
      …
      if ( $dbUser = dbFetchOne($sql, NULL, $sql_values) ) {
        Info("Login successful for user \"$username\"");
        $_SESSION['user'] = $user = $dbUser;
        unset($_SESSION['loginFailed']);
        if ( ZM_AUTH_TYPE == 'builtin' ) {
          $_SESSION['passwordHash'] = $user['Password'];
        }
        session_regenerate_id();
      } else {
        Warning("Login denied for user \"$username\"");
        $_SESSION['loginFailed'] = true;
        unset($user);
      }
      if ( $close_session )
        session_write_close();
      return isset($user) ? $user: null;
    } # end function userLogin

Now, this doesn't show why two lines are logged, one which is vulnerable to XSS and one isn't. Our username is put unencoded in the warning object.

Another interesting thing here is that `$_SESSION['username']` is set early on, even before checking whether the username and password are correct. This means we can set the username in the session from the login form without authenticating. This can't be good. Let's see if this value is used anywhere.

    function getAuthUser($auth) {
      …
      if ( isset($_SESSION['username']) ) {
        # Most of the time we will be logged in already and the session will have our username, so we can significantly speed up our hash testing by only looking at our user.
        # Only really important if you have a lot of users.
        $sql = "SELECT * FROM Users WHERE Enabled = 1 AND Username='".$_SESSION['username']."'";
      } else {
      …
    } // end getAuthUser($auth)

It is used in `getAuthUser`, where it is used unescaped in the SQL query. This means that the application is vulnerable to unauthenticated second order SQL injection. We inject a SQL expression in the username field of the login form, and that gets executed when `getAuthUser` is called. Where does `getAuthUser` get called? In [`AppController.php`](https://github.com/ZoneMinder/zoneminder/blob/1.32.3/web/api/app/Controller/AppController.php):

    public function beforeFilter() {
      …
      $mAuth = $this->request->query('auth') ? $this->request->query('auth') : $this->request->data('auth');
      …
        } else if ( $mAuth ) {
          $user = getAuthUser($mAuth);
      …
    } # end function beforeFilter()

The `beforeFilter` function is called on every API call, so if we do any API call with an `auth` parameter, `getAuthUser` gets called and our injected SQL will get executed.

So first, we try to log in with the username `a' or SLEEP(3)='a`. This gets stored in the `$_SESSION['username']`. Then we call http://zoneminder.local/zm/api/index.php/logs.json?auth=a, and by the time it takes we know that our query got executed.

### Exploiting with sqlmap

Sqlmap is an excellent tool to exploit SQL injection, and can be used to exploit this particular vulnerability. This can be done with this command:

    ./sqlmap.py -r login.request.txt -p username --second-url='http://zoneminder.local/zm/api/index.php/logs.json?auth=a' --ignore-code=401 --dbms=mysql --level=3

I saved the login request to the file `login.request.txt`, and passed this to sqlmap using the `-r` parameter. This is an easy way to pass a request to sqlmap. However, one thing that is not included in the request is whether is should be done over HTTPS, so be sure to pass `--force-ssl` if you are testing a HTTPS site. We indicate that the payload should be in the `username` parameter with the `-p` flag. After injecting the payload, sqlmap should call another URL, which we pass with `--second-url`. The login form will return a 401 to indicate that we are not logged in, but that doesn't matter to us, so we'll ignore it. Then we provide sqlmap with the database system in use (MySQL) and how hard it should try (level). This succesfully finds the SQL injection:

    Type: AND/OR time-based blind
    Title: MySQL >= 5.0.12 AND time-based blind (query SLEEP)
    Payload: action=login&view=postlogin&postLoginQuery=&username=abc' AND (SELECT * FROM (SELECT(SLEEP(5)))sPHz)-- OYdS&password=abc

## Conclusion

While investigating an XSS issue we found a second order SQL injection vulnerability.

## Read more

* [#2542 - Second order SQL injection in login](https://github.com/ZoneMinder/zoneminder/issues/2542)
* [#2453 - Self - Stored Cross Site Scripting(XSS) - log.php](https://github.com/ZoneMinder/zoneminder/issues/2453)
