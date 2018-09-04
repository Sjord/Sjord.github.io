---
layout: post
title: "Truncating strings with MySQL"
thumbnail: dolphins-240.jpg
date: 2018-10-10
---

Typical behavior in web applications is to validate user input before storing it in the database. However, in some cases the database may not store exactly what you put into it. This may be used to bypass input validation. This post describes some ways data can be altered when storing it in a MySQL database, and how to prevent it.

<!-- photo source: https://www.flickr.com/photos/bike/2380021517 -->

## Know what you store in the database

Most web applications are backed by a relational database. The web application accepts user input and stores it in the database after validating it. In some cases the database doesn't store the value precisely as-is, and this can introduce a security problem. Consider for example a file upload form that checks the extension of the file:

    if (preg_match("/.php$/", $_POST['filename']) {
        die("You are trying to upload a webshell!");
    } else {
        saveFile($_POST);
    }
    
If the filename ends with .php it won't save the file. Now, if the value of the filename is altered when saving it to the database, this can cause a security issue. If the user supplies a filename like hacker.php&#x1f4a9;.jpeg, the filename may have ended in .jpeg while running the PHP validation, but a filename that ends in .php is saved in the database.

## Truncation in MySQL

In MySQL, there are a couple of reasons why data may be truncated before saving. How data is saved also depends on the configuration of both the web application and the database server.

### Column length

Every column in the database has a specified type. For some data types, it is mandatory to supply a maximum length. For the VARCHAR type, for example, you specify the maximum length that can be stored in the column. If you try to insert a longer value, MySQL will either give an error, or just truncate the value and save part of it.

For example, if our filename column has data type VARCHAR(10), it can store a maximum of 10 characters. If we then specify abcdef.php.jpeg as our filename, the PHP code will accept it as an image, but abcdef.php gets saved.

### Four byte emojis

When MySQL implemented UTF8, they didn't anticipate for UTF8 characters that take up four bytes. This means that the "utf8" charset is incompatible with many Unicode characters. For some configurations, this means that MySQL simply truncates the string and saves everything before the Unicode character.

So if we supply hack.php&#x1f4a9;.jpeg as our filename, the PHP code will see this as ending in .jpeg, but hack.php will be saved to the database.

### Trailing spaces

Nobody wants to use valuable disk spaces for storing white space, so MySQL helpfully removes it:

> For VARCHAR columns, trailing spaces in excess of the column length are truncated prior to insertion and a warning is generated, regardless of the SQL mode in use.

This means that is we supply `webshel.php ` as our filename, it ends in white space and not in .php, but still `webshel.php` gets saved to the database.

## Recommendations

Do the following to reduce unexpected behavior of the database:

* Use the utf8mb4 charset instead of utf8.
* Strip trailing spaces before validating user input.
* Enable the following [SQL modes](https://dev.mysql.com/doc/refman/8.0/en/sql-mode.html):
    * STRICT_TRANS_TABLES
    * STRICT_ALL_TABLES
    * ONLY_FULL_GROUP_BY
    * NO_ZERO_IN_DATE
    * NO_ZERO_DATE
    * ERROR_FOR_DIVISION_BY_ZERO
    * NO_ENGINE_SUBSTITUTION

## Conclusion

Unexpected behavior of the database can introduce security issues even if the validation logic is correct. Be aware of the behavior of your database and configure your database correctly in order to avoid this.
