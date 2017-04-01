---
layout: post
title: "Downloading an exposed Mercurial .hg directory"
thumbnail: mercury-240.jpg
date: 2017-04-12
---

Version control systems sometimes create a hidden directory in the repository. Git creates a .git directory, and Mercurial creates a .hg directory. If the contents of a web site are managed with Git or Mercurial, this directory is sometimes exposed on the web. In this post we look at a way to download these directories.

## Exposed version control directories

Source code is often managed in a version control system like Git or Mercurial. This also makes it easy to deploy. Just run `git pull` in the `/var/www` directory on the server and the source code is updated. When using this method, a hidden directory is created by the source control software. In many configurations, this directory can be accessed over the Internet, by simply browsing to `http://example.com/.git` or `http://example.com/.hg`.

## Downloading the contents

If directory listing is enabled on the server, all files under .git or .hg are viewable and can be downloaded with a mirroring tool like `wget`. If directory listing is disabled, it is still possible to download all the contents, but this needs a bit of work.

The hard thing is finding out the file names even though we don't have a list of available files. Luckily, the .git and .hg directories contain a list of files in a format specific for the repository. After downloading a few files with fixed names, we can ask the version control software for a list of files.

For example, .hg directories have a defined [structure](https://www.mercurial-scm.org/wiki/Repository#Structure). They always contain `.hg/store/00manifest.i`, `.hg/store/00changelog.i` and some other files. Once we download these, we can run `hg --debug manifest` to get a list of files under source control. Then we can download the Mercurial information for each of these files.

If all files succesfully downloaded, `hg update -C` restores all files. If some files did not download, the repository can be repaired by using the [convert extension](https://www.mercurial-scm.org/wiki/RepositoryCorruption#Recovery_using_convert_extension).

I have created [a tool](https://github.com/Sjord/sprengel) that will automatically download the files in a .hg directory. There is also a [tool for Git](https://github.com/internetwache/GitTools).

## Conclusion

Don't expose your source control directory. A good way to make sure you don't is by using a subdirectory of your repository as web root.

When a site has its .hg directory exposed, you can use [sprengel](https://github.com/Sjord/sprengel) to download it. For Git, use [GitTools](https://github.com/internetwache/GitTools).
