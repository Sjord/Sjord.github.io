---
layout: post
title: "How does git diff --ignore-matching-lines work"
thumbnail: compare-questions-480.jpg
date: 2021-08-14
---

Git diff does not display a sequence of consecutive lines, if all of the removed and added lines match any of the regexes specified by `--ignore-matching-lines` (`-I`).

Git diff shows the differences in files between two commits. Instead of showing all differences, it can omit some differences and show only interesting changes. For example, `--ignore-all-space` or `-w` omits differences in white space. Additionally, it is possibly to specify which differences to ignore, by specifying one or more regular expressions with `--ignore-matching-lines` (`-I`).

A difference is only ignored when both the removed lines and the added lines match at least one of the supplied regular expressions.

## Example

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
His bill will hold more than his belican,\n
                                           ↑
                                           ^ matches because \n starts a new line
                                           $ matches because the buffer ends here

```

To match more precisely, we can use <code class="language-plaintext highlighter-rouge">\`</code> to match the start of the buffer, and `\'` to match the end of the buffer. An empty line can thus be matched with:

    \`\n\'

Where `\n` is an actual newline, not backslash-n. This needs much escaping to enter correctly in a shell:

    git diff -I $'\\`\n\\\'' …
    
## Regex dialect

Git [passes](https://github.com/git/git/blob/55194925e62b34a3f62b31034f73a6bcfb063bc5/diff.c#L5237-L5238) the following flags to `regcomp`:

* REG_EXTENDED - [Extended](https://www.gnu.org/software/gnulib/manual/html_node/posix_002dextended-regular-expression-syntax.html#posix_002dextended-regular-expression-syntax) syntax. We don't have to put a backslash before modifiers, so 'a+b?' matches multiple *a*'s optionally followed by a *b*.
* REG_NEWLINE - Line-based matching, so `.` doesn't match newline, `^` matches the start of the line, and `$` matches the end of the line.


```
static void xdl_mark_ignorable_regex(xdchange_t *xscr, const xdfenv_t *xe,
				     xpparam_t const *xpp)
{
	xdchange_t *xch;

	for (xch = xscr; xch; xch = xch->next) {
		xrecord_t **rec;
		int ignore = 1;
		long i;

		/*
		 * Do not override --ignore-blank-lines.
		 */
		if (xch->ignore)
			continue;

		rec = &xe->xdf1.recs[xch->i1];
		for (i = 0; i < xch->chg1 && ignore; i++)
			ignore = record_matches_regex(rec[i], xpp);

		rec = &xe->xdf2.recs[xch->i2];
		for (i = 0; i < xch->chg2 && ignore; i++)
			ignore = record_matches_regex(rec[i], xpp);

		xch->ignore = ignore;
	}
}
```

* `xdl_diff` called for each changed file.
* commits are not relevant.
* `chg1` contains removed text, `chg2` contains new text.
* Both old text and new text must match any of the regexes for it to be ignored.
* If text is added, there is no old text, and the regexes are not tested against it.
* Each record is a line. Each line in a change needs to match the regex for it to be ignored.
* Match empty lines: not with `^$`. Because every line ends in a newline? With `^\s+$`?
* [regexec](https://www.man7.org/linux/man-pages/man3/regex.3.html) with REG_STARTEND
* git comes with its own regex functions, because macOS regex and Linux regex are different
* doesn't work in --name-status and related modes.
* \A and \z don't seem to work.
* Do \` and \' work to indicate start and end of buffer?
* Not called for binary files

## Read more

* [xdl_mark_ignorable_regex in git source code](https://github.com/git/git/blob/55194925e62b34a3f62b31034f73a6bcfb063bc5/xdiff/xdiffi.c#L1028-L1054)
* [Specified Lines (Comparing and Merging Files)](https://www.gnu.org/software/diffutils/manual/html_node/Specified-Lines.html#Specified-Lines)
* [diff: add -I<regex> that ignores matching changes · git/git@296d4a9](https://github.com/git/git/commit/296d4a94e7231a1d57356889f51bff57a1a3c5a1)
* [[LINUX] Exact behavior of diff --ignore-matching-lines = RE](https://memotut.com/diff-ignore-matching-lines=re-exact-behavior-d9ff4/)
* [[PATCH 0/2] diff: add -I<regex> that ignores matching changes - Michał Kępień](https://lore.kernel.org/git/20201001120606.25773-1-michal@isc.org/)
* [regular expression - How to diff files ignoring comments (lines starting with #)? - Unix & Linux Stack Exchange](https://unix.stackexchange.com/questions/17040/how-to-diff-files-ignoring-comments-lines-starting-with)
* [GNU Gnulib: Regular expressions](https://www.gnu.org/software/gnulib/manual/html_node/Regular-expressions.html#Regular-expressions)
