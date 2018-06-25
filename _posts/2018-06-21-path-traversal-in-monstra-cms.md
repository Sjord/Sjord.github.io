---
layout: post
title: "Path traversal in Monstra CMS"
thumbnail: path-fork-240.jpg
date: 2018-07-18
---

In the administration interface of Monstra CMS, there is an option to download a backup file. This request can be modified to download any file on the server.

<!-- photo source: http://www.geograph.org.uk/photo/2677765 -->

## Backups in Monstra

Monstra is a CMS written in PHP, that stores its data in XML files instead of a database. It has the functionality to create and download backup files:

<img src="/images/monstra-backup-listing.png" alt="Monstra backup listing">

When we click the link, it performs a request to:

http://server/admin/index.php?id=backup&download=2018-06-21-11-00-30.zip&token=b3efc3d30fefd83f90dcfc59550b13be025b304a

The `download` parameter looks like a filename. The corresponding [source code](https://github.com/monstra-cms/monstra/blob/dev/plugins/box/backup/backup.admin.php#L70) looks pretty straightforward:

    File::download($backups_path . DS . Request::get('download'));

Serve the file from the `download` parameter for download. It looks as it does no further checking or cleaning of the parameter.

Looking at Monstra's [directory layout](https://github.com/monstra-cms/monstra), backup files seem to be in the `backups` directory. We want to break out of that directory and download a file in another directory, such as README.md in the webroot. Let's change the filename to `../README.md`:

<img src="/images/monstra-error.png" alt="Monstra error message">

It doesn't work.

## Bypassing sanitation

It seems our `download` parameter is modified after all, just not in the place we expect. After some searching, I found the code in the [Security class](https://github.com/monstra-cms/monstra/blob/dev/engine/Security.php#L198):

    public static function sanitizeURL($url)
    {
        $url = trim($url);
        $url = rawurldecode($url);
        $url = str_replace(array('--', '&quot;', '!', '@', '#', '$', '%', '^', '*', '(', ')', '+', '{', '}', '|', ':', '"', '<', '>',
                                  '[', ']', '\\', ';', "'", ',', '*', '+', '~', '`', 'laquo', 'raquo', ']>', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8211;', '&#8212;'),
                            array('-', '-', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''),
                            $url);
        $url = str_replace('--', '-', $url);
        $url = rtrim($url, "-");

        $url = str_replace('..', '', $url);
        $url = str_replace('//', '', $url);
        $url = preg_replace('/^\//', '', $url);
        $url = preg_replace('/^\./', '', $url);

        return $url;
    }

    public static function runSanitizeURL()
    {
        $_GET = array_map('Security::sanitizeURL', $_GET);
    }

So the `$_GET` array containing all query parameters is cleaned up before use. Specifically, `..` is removed from it.

However, this can be easily bypassed. What we need is that the parameter does not contain `..` when entering `sanitizeURL`, but does contain `..` afterward. We can use one of the last three replacements for that. For example, if we put `.//.` in our parameter, the `//` will be removed and we will be left with `..`.

In the last `preg_replace`, the first dot is removed. Thus we need one more dot to prevent this, again escaping it with the double slash so that it doesn't get removed. Our filename becomes:

    .//.//./README.md

which will become `../README.md` after sanitation. When we pass this in the download parameter:

<img src="/images/monstra-download-readme.png" alt="Download dialog for README.md">

## Conclusion

With this vulnerability, the administrator can download files from the web server. The sanitation that is specifically meant to prevent this can be bypassed.
