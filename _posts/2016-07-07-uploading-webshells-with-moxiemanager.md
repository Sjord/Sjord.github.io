---
layout: post
title: "Uploading webshells using .NET MoxieManager"
thumbnail: pandora-240.jpg
date: 2016-09-15
---

MoxieManager is a file manager for web applications. By using the built-in unzip functionality we can bypass the file extension filter in the .NET version of MoxieManager.

## Bypassing the file extension restrictions with unzip

MoxieManager allows only files with certain file extensions to be uploaded. Uploading a JPG is allowed, but an ASPX is not. MoxieManager also has functionality to zip and unzip files. This is useful when uploading or downloading a large amount of files. By using the unzip functionality, we can bypass the file extension filter and upload files with any extension we want.

This only works in the .NET version of MoxieManager, not in the PHP version.  I tested MoxieManager for .NET version 1.1, released in 2013. MoxieManager will not show the file in the interface, but the file will still be accessible if uploaded within the webroot.

Upload a zip file and extract it:

![Unzip webshell.zip](/images/moxiemanager-unzip-mywebshell.png)

And open the webshell to run anything on the server:

![Resulting webshell](/images/moxiemanager-run-mywebshell.png)

## Why does this happen

First I assumed that MoxieManager simply ignores the extension filter and calls `unzip.exe mywebshell.zip`, but this is not the case. Both in the PHP as in the .NET version each file in the zip-file is checked against the filter. The following examples are obtained by disassembling the .NET DLLs using [dotPeek](https://www.jetbrains.com/decompiler/):

    BasicFileFilter fileFilter = BasicFileFilter.CreateFromConfig(config);
    foreach (ZipArchiveEntry entry in new ZipArchive(...).Entries)
    {
        IFile file = FileSystemManager.GetFile(PathUtils.Combine(destinationDir.Path, entry.FullName));
        if (!file.Exists && fileFilter.Accept(file))
        {
            // Write file
        }
    }

As you can see the BasicFilterFilter is consulted for every file in the zip. Its job is to check the extension of each file, but it doesn't. It doesn't even consider them files to begin with:

    public LocalFile(FileSystem fileSystem, string path)
    {
        this.fileInfo = new FileInfo(PathUtils.ToSystemPath(path));
        if (this.fileInfo.Exists) {
            return;
        }
        this.dirInfo = new DirectoryInfo(PathUtils.ToSystemPath(path));
        this.fileInfo = (FileInfo) null;
    }

    public bool IsFile()
    {
        return this.fileInfo != null;
    }

The constructor for this file class assumes that anything in the filesystem is either a file or a directory. If it isn't an existing file, then it must be a directory. This does not hold up for files that do not exist yet, such as those we are extracting from a zip-file. These get marked as a directory, and the BasicFileFilter will not do any extension checking on them because that makes no sense for directories.

## Disabling zip upload doesn't work

To protect against this vulnerability, you may think that blocking zip-files from being uploaded solves this problem. However, MoxieManager will also happily unzips files with any other extension. By renaming a zip-file to image.jpg and invoking the unzip-command on it, an attacker could still upload arbitrary files.

## Conclusion

Because different components make different assumptions on files, it is possible to upload web shells using MoxieManager.

## Timeline

* 15 June 2016, Sjoerd → Ephox: found this vulnerability.
* 15 June 2016, Ephox → Sjoerd: when can we contact you about your editor requirements?
* 20 June 2016, Ephox → Sjoerd: you can configure MoxieManager to upload ASPX files
* 23 June 2016, Ephox → Sjoerd: how many servers are you planning on deploying TinyMCE on?
* 23 June 2016, Sjoerd → Ephox: no, I have found a vulnerability.
* 23 June 2016, Ephox → Sjoerd: thanks, we'll look into it.
* 07 July 2016, Sjoerd → Ephox: any update?
* 07 July 2016, Ephox → Sjoerd: we're working on it. Which version did you use?
* 08 July 2016, Sjoerd → Ephox: this version.
* 30 August 2016, Sjoerd → Ephox: any update?
* 13 September 2016, Sjoerd → Ephox: any update?
* 16 September 2016, Ephox → Sjoerd: sorry it took so long. The issue will be fixed in the next release.
