---
layout: post
title: "Git submodules update to default branch, except when it's changed"
thumbnail: olive-tree-480.jpg
date: 2021-10-15
---

Git submodules by default update to the remote default branch. However, when you change the default branch, the submodule does not automatically switch to the new default branch.

<!-- Photo source: https://pixabay.com/photos/olive-tree-old-tree-tree-branches-3579922/ -->

## Submodule updated to cached remote HEAD

When running `git submodule update --remote`, the upstream changes in each submodule are pulled, updating the submodules to their latest versions. The branch to pull can be specified in `.gitmodules` or `.git/config`, and defaults to the remote HEAD if it's not specified.

The remote HEAD is more or less the default branch on the remote. So in GitHub or GitLab you probably have `main` or `master` configured to be the default branch. This can be configured for each repository. When you clone the repository, the remote HEAD points to this branch. Running `git branch -a` confirms this:

```
$ git branch -a
...
remotes/origin/HEAD -> origin/master
```

After cloning, however, this reference is stored in the local repository and no longer updated. If you change the default branch from `master` to `develop` in GitHub, the `remotes/origin/HEAD` ref in every checked out repository will still point to `origin/master`, not to `origin/develop`. This means that when someone updates their submodules with `git submodule update --remote`, they will fetch the latest changes from `master`, not from `develop`.

## Updating the local remote HEAD

To retrieve and update the `remotes/origin/head` reference to the default upstream branch again, run:

```
git remote set-head origin -a
```

To update the `remotes/origin/head` reference in all submodules, run the above on each submodule:

```
git submodule foreach git remote set-head origin -a
```

## Read more

* [Git submodule update remote keeps using previous default branch](https://lore.kernel.org/git/CAA1vfca+kPSsitsZad-bmrd+o1ay60NXZrH2zGLpwN69Px-rtw@mail.gmail.com/T/#u)
* [git - How does origin/HEAD get set? - Stack Overflow](https://stackoverflow.com/questions/8839958/how-does-origin-head-get-set)