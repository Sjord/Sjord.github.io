---
layout: post
title: "How does git diff --ignore-matching-lines work"
thumbnail: compare-questions-480.jpg
date: 2021-08-13
---

Git diff does not display a hunk of changes, if all of the removed and added lines match any of the regexes specified by `--ignore-matching-lines` (`-I`).

## Introduction

During a code review, it is often useful to view the changes in a file. However, many changes are not very interesting. Renaming a class or namespace may result in differences in many files, but this is not important from a security perspective. Therefore, it is useful to ignore certain changes. Git has an option to ignore changes that match a certain regular expression, which can be used for this task.

## Matching lines in hunks

Git diff shows the differences in files between two commits. Instead of showing all differences, it can omit some differences and show only interesting changes. For example, `--ignore-all-space` or `-w` omits differences in white space. Additionally, it is possibly to specify which differences to ignore, by specifying one or more regular expressions with `--ignore-matching-lines` (`-I`).

A difference is only ignored when both the removed lines and the added lines match at least one of the supplied regular expressions. The lines in the hunk that are removed and added are checked against each regex. If all lines match, this difference is not shown. This is reevaluated for each hunk. Commits don't matter here; the diff is taken between the old version and the new version.

A hunk is a number of changed lines that are close toogether. Consecutive lines are always in the same hunk. But hunks can also contain several lines of unchanged text. This means that changes on lines 4 and 7 may belong to the same hunk. Only these changed lines are compared to the given regular expression, but both must match to ignore the hunk.

The `--ignore-matching-lines` and other similar flags only work when git is actually comparing the content of the files. When passing `--name-only` or `--name-status`, git only determines whether files are changed without looking at their contents. The ignore flags don't do anything in that case. They also don't affect binary files.

## Simple example

We have this text file:

```
A wonderful bird is the pelican,
His bill will hold more than his belican,
He can take in his beak
Enough food for a week
```

We fix the obvious error and change "belican" to "belly can". Then we request a diff, but we are not interested in belly-related changes:

```
$ git diff -I belly HEAD~1..HEAD
diff --git a/poem.txt b/poem.txt
index 215f7b0..b21a011 100644
--- a/poem.txt
+++ b/poem.txt
@@ -1,4 +1,4 @@
 A wonderful bird is the pelican,
-His bill will hold more than his belican,
+His bill will hold more than his belly can,
 He can take in his beak
 Enough food for a week
```

The difference is still shown. Even though the new line matches the regex, the old line does not. If want to ignore this change, we have to match both lines. Either:

* supply multiple regexes: `git diff -I belly -I belican …`
* match multiple things in one regex: `git diff -I 'bel(ly|ican)' …`

## Matching empty lines

Git runs each regex over each line. These lines end in a newline, so our regex is actually checked against:

```
His bill will hold more than his belican,\n
```

Where `\n` stands for a newline character. When we have a change that adds an empty line, the regex is ran against a single byte string consisting of `\n`. How do we match that?

It's easier to use something like `--ignore-blank-lines` to ignore blank lines. However, this does not work well together with other regular expressions that we want to ignore. If we want to ignore a change that performs both an uninteresting belly-related change and adds an uninteresting empty line, our regular expressions we give to `-I` need to match both for the change to be hidden. So we need a regular expression that matches an empty line, and  `--ignore-blank-lines` and other white space related options don't change that.

An empty line cannot be matched with `^$`. `^` matches both the beginning of the line and the beginning of the buffer. Similarly, `$` matches both the end of the line as the end of the buffer. All changed lines end in a newline, just before the end of the buffer. This means that `^$` matches every changed line. The newline at the end starts a new line, and is immediately followed by the end of the buffer.

```
… his belican,\n
                ↑
                ^ matches because \n starts a new line
                $ matches because the buffer ends here
```

To match more precisely, we can use ``\` `` to match the start of the buffer, and `\'` to match the end of the buffer. An empty line can thus be matched with:

    \`\n\'

Where `\n` is an actual newline, not backslash-n. This needs much escaping to enter correctly in a shell:

    git diff -I $'\\`\n\\\'' …
    
## Regex dialect

Git calls [regcomp](https://man7.org/linux/man-pages/man3/regcomp.3.html) and [regexec](https://man7.org/linux/man-pages/man3/regcomp.3.html) to handle regular expressions. However, it brings [its own version](https://github.com/git/git/tree/55194925e62b34a3f62b31034f73a6bcfb063bc5/compat/regex) of these functions instead of relying on the systems C library. Each system has its own dialect of regular expressions, and this way git can keep the same dialect across systems.

Git [passes](https://github.com/git/git/blob/55194925e62b34a3f62b31034f73a6bcfb063bc5/diff.c#L5237-L5238) the following flags to `regcomp`:

* REG_EXTENDED - [Extended](https://www.gnu.org/software/gnulib/manual/html_node/posix_002dextended-regular-expression-syntax.html#posix_002dextended-regular-expression-syntax) syntax. We don't have to put a backslash before modifiers, so 'a+b?' matches multiple *a*'s optionally followed by a *b*.
* REG_NEWLINE - Line-based matching, so `.` doesn't match newline, `^` matches the start of the line, and `$` matches the end of the line.

These features are supported:

* `\1`, `\2` … `\9` for backreferences: `bi(ll) wi\1` matches `bill will`, because `\1` references the first capture group.
* `\<` matches the beginning of words, `\>` matches the end of words, `\b` matches either.
* `\B` matches an empty string within a word.
* `\w` matches any word character, `\W` matches any non-word character.
* `\s` matches any white space, `\S` matches any non-space.
* ``\` `` matches the beginning of the buffer, `\'` matches the end of the buffer.
* `(…)` Parenthesis to mark capture groups.
* `*`, `+`, `?`, `{n,m}` for repetition.
* `[abc]` for character classes.
* `[[:alnum:]]` and similar named character classes.
* `^` matches either start of string or start of buffer, `$` matches the end of either.

These features don't work in git:

* `\d` or `\D` just match `d` and `D`, not digits.
* `\l` and `\u` don't match lowercase or uppercase letters.
* `\A` and `\z` don't work, use ``\` `` and `\'`.
* `\n`, `\x0a`, `\u000a` don't work. If you want to match a newline, you have to pass a literal newline in the parameter.
* `[:alnum:]`. It only works with two brackets: `[[:alnum:]]`.

## Advanced example

I have a C# project where I want to review changes to the code. However, they also recently changed some namespaces, and I am not interested in that, so we want to ignore lines starting with:

* `namespace …`, the namespace for a class.
* `using …`, the import of a namespace.

So, we'll use this command to ignore these words at the start of the line, followed by a single space.

    git diff -I '^using ' -I '^namespace '

However, the resulting diff still has namespace changes:

    -<U+FEFF>namespace SomeOldNameSpace
	+<U+FEFF>namespace SomeNewNameSpace^M

These are [byte order marks](https://en.wikipedia.org/wiki/Byte_order_mark) (BOM) that can appear at the start of the file. We want to ignore those too. And of course we want to ignore any empty lines that are added during the namespace changes:

    git diff -I $'^(\ufeff)?using ' -I '^(\ufeff)?namespace ' -I $'\\`\n\\\'' …

As you can see, it becomes quite complex quite fast.

## Conclusion

So, git diff --ignore-matching-lines:

* works on hunks,
* only hides a hunk if all the deleted lines and all the added lines match any of the given regular expressions,
* uses the glibc extended POSIX regex dialect, even on non-glibc systems.


## Read more

* [Exact behavior of diff --ignore-matching-lines = RE](https://memotut.com/diff-ignore-matching-lines=re-exact-behavior-d9ff4/)
* [GNU Gnulib: Regular expressions](https://www.gnu.org/software/gnulib/manual/html_node/Regular-expressions.html#Regular-expressions)
* [Diff manual - Suppressing differences whose lines all match a regular expression](https://www.gnu.org/software/diffutils/manual/html_node/Specified-Lines.html#Specified-Lines)
* [Hunks (Comparing and Merging Files)](https://www.gnu.org/software/diffutils/manual/html_node/Hunks.html)
* [Git - git-diff Documentation](https://git-scm.com/docs/git-diff)
* [xdl_mark_ignorable_regex in git source code](https://github.com/git/git/blob/55194925e62b34a3f62b31034f73a6bcfb063bc5/xdiff/xdiffi.c#L1028-L1054)
* [git commit 296d4a9: diff: add -I&lt;regex&gt; that ignores matching changes](https://github.com/git/git/commit/296d4a94e7231a1d57356889f51bff57a1a3c5a1)
* [git mailinglist: diff: add -I&lt;regex&gt; that ignores matching changes - Michał Kępień](https://lore.kernel.org/git/20201001120606.25773-1-michal@isc.org/)
* [regular expression - How to diff files ignoring comments (lines starting with #)? - Unix & Linux Stack Exchange](https://unix.stackexchange.com/questions/17040/how-to-diff-files-ignoring-comments-lines-starting-with)
