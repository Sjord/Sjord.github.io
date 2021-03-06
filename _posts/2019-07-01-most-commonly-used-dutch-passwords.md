---
layout: post
title: "Most commonly used Dutch passwords"
thumbnail: finish-480.jpg
date: 2019-11-06
---

Online brute force attacks use a list of commonly used passwords. Which passwords are common can vary for different countries. In this article, we look into how passwords differ for Dutch users.

<!-- photo source: https://commons.wikimedia.org/wiki/File:Stage_2_finish,_Tour_de_France_1966_(cropped).jpg -->

### Creating a country specific list

In 2012, LinkedIn leaked 117 million password hashes. Since these are unsalted SHA1, we can see from the password hashes alone which are used most often. The breach also contain the corresponding email addresses. This can be used to filter a specific country, by filtering on top level domain of the email address. After filtering and selecting the most commonly used passwords, the hashes can be cracked. It is quite easy to crack all the hashes, since these are all insecure passwords. The top 10 looks like this:

| password   | count |
|------------|-------|
| 123456     | 637   |
| welkom     | 505   |
| linkedin   | 382   |
| welkom01   | 339   |
| geheim     | 295   |
| amsterdam  | 254   |
| wachtwoord | 224   |
| Welkom01   | 221   |
| vakantie   | 181   |
| willem     | 169   |

### Use a language specific list

Of the top 10 passwords, eight are Dutch. This scales to the rest of the list: of the top 100, 80 words are obviously Dutch. When doing a brute force attack on a Dutch web application, you should definitely use a Dutch password list. A normal, English password list misses out of the most likely passwords for the application.

Similarly, when defending, you should block common passwords in the language of your user. 68 of the first 100 commonly used passwords by Dutch people are not in the zxcvbn common password list. The word "netwerk" on place 15 is the first word that doesn't occur in the zxcvbn wordlist. You can't trust zxcvbn to prevent common passwords if you have non-English users. 

### Use Pwned Passwords

If you want to block weak passwords in your application, you may want to use the [Pwned Passwords](https://haveibeenpwned.com/Passwords) API. This service isn't focused on Dutch users, but because it contains so many passwords, that doesn't matter. It contains all passwords from the LinkedIn breach, so that includes all Dutch passwords. The API can be used in a way that doesn't disclose the password you want to check.

### Conclusion

Most common password lists do not contain most common Dutch passwords. When defending or attacking a Dutch application, it is important to use a Dutch password list. This probably goes for any language other than English.

### Resources

* [Dutch top 1134 of LinkedIn passwords](/wordlists/linkedin-dutch-top-1134.txt)
* [Richelieu - List of the most common French passwords](https://github.com/tarraschk/richelieu)
* [LinkedIn password breach torrent](magnet:?xt=urn:btih:07e8619ccd43aba5208ab8f66204ebf6c4c58838&dn=LinkedIn.rar)
* [Dropbox password breach torrent](magnet:?xt=urn:btih:e1a1e35e65454a08cde363cb5ee42fa56841a0e7&dn=dropbox.zip)
* [MySpace password breach torrent](magnet:?xt=urn:btih:17e6fc94dae0a3168301012c290a53a2bd314a28&dn=Myspace.com.rar), password is `KLub8pT&iU$8oBY(*$NOiu`
* [Leaks collection torrent](magnet:?xt=urn:btih:85f39f1d94917d61277725e7da85d8177a5c12eb&dn=leaks)
