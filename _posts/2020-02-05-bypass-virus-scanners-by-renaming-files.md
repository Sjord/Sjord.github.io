---
layout: post
title: "Bypass virus scanners by renaming files"
thumbnail: bag-head-480.jpg
date: 2020-03-11
---

To prevent spreading viruses or malware, many web applications scan uploaded files using a virus scanner. Often, the virus scanner is started as another process, and the output is checked for the result. The output of the virus scanner also contains the filename, which makes it possible to influence the logic that checks for the result. This post describes some instances where virus scanning can be bypassed by naming the file a particular way.

## Calling clamscan

When a web application has functionality to upload and share files, it is good practice to use a virus scanner on these files to prevent spreading malware this way. A popular virus scanner to use is clamscan. Clamscan is free and can easily be integrated, since a single file can be scanned from the command line. When running clamscan on a legitimate file, the output looks as follows:

    $ clamscan benign.com 
    benign.com: OK

And a virus looks as follows:

    $ clamscan eicar.com 
    eicar.com: Eicar-Test-Signature FOUND

## Result injection

To obtain the clamscan result, some application try to parse the output. If there is `OK` in the output, the file is considered good. If there is `FOUND` in the output, the file contains a virus. However, since the filename is also printed and can be under control of the attacker, this presents an injection attack. If an attacker names a file `OK.exe`, it contains `OK`, and an application may think it doesn't contain a virus.

### npm clamscan

The [clamscan Node package](https://github.com/kylefarris/clamscan) used to contain the following code, where `result` contains the output from clamscan:

    if (/:\s+OK/.test(result)) {
        if (this.settings.debug_mode) console.log(`${this.debug_label}: File is OK!`);
        return {is_infected: false, viruses: []};
    }

    if (/:\s+(.+)FOUND/gm.test(result)) {
        ...

As you can see, if the output contains `: OK`, the file is considered safe. This can be exploited by renaming a file to something that contains `: OK`, as shown here:       

<video muted autoplay loop src="/images/clamscan.mp4"></video>

### Moodle

The following code is used to check clamscan output in [Moodle](https://github.com/moodle/moodle/blob/master/lib/antivirus/clamav/classes/scanner.php#L373):

    private function parse_socket_response($output) {
        $splitoutput = explode(': ', $output);
        $message = trim($splitoutput[1]);
        if ($message === 'OK') {
            return self::SCAN_RESULT_OK;
        } else {
            ...

This splits on `: ` and takes the second part. So renaming a file `file: OK: .exe` will do the trick. However, it seems that files have a temporary filename when they are scanned, so that prevents an attacker from tricking the virus scanner.

### clamtk

The GUI frontend for clamscan [clamtk](https://github.com/dave-theunsub/clamtk) can be tricked in another way. It uses the following check to determine virus status:

    if ( /(.*?): ([^:]+) FOUND/ ) {
        $file   = $1;
        $status = $2;
    }

The applies a regular expression to the output of clamscan, and stores the matched groups in `$file` and `$status`.

We can't get around this if-statement. If the file contains a virus, the output of clamscan is going to contain a colon and FOUND. However, by changing the filename it is possible to manipulate what goes into the `$file` and `$status` variables. This can be used to bypass the scanner, because of the following critical code:

    # Ensure the file is still there (things get moved)
    # and that it got scanned
    next unless ( $file && -e $file && $status );

If `$file` does not point to an actual file, simply skip the rest of the loop. So, the scanner can be bypassed by naming a file `file: a FOUND.com`. The part before the colon, `file`, is interpreted as the filename. If that file doesn't exist, the file is considered safe.

<img src="/images/clamtk-no-threats-found.png">

## Daemon protocol injection

Loading virus definition files takes a while, and clamscan is thus pretty slow to start. A solution for this is clamd, a daemon that keeps clamscan running and scans files on request. Clients can ask for a virus scan using a simple protocol. A file can be scanned by sending the following command:

    nSCAN /home/sjoerd/eicar.exe

The `n` in this command indicates that the command is terminated by a newline. The alternative is `z`, for nul-byte termination. Since filenames can also contain newlines and nul-bytes, this presents an injection opportunity. We first upload a benign file `file`, followed by a malicious file `file\n.exe`. The newline terminates the nSCAN command:

    nSCAN /var/www/uploads/file
    .exe

The benign file is scanned and reported clean, and our virus bypasses detection.

### npm clam-js

The Node module [clam-js](https://github.com/srijs/clam-js/blob/master/clam.js#L107) is vulnerable to such protocol injection. When scanning a `file\n.exe`, where `\n` is a newline, the communication with clamd looks as follows:

    nSCAN /home/sjoerd/dev/virus/clam/file
    .exe

And the server responds with:
    
    2: /home/sjoerd/dev/virus/clam/file: OK

We tricked it into scanning another file, by terminating the scan command early.

## Conclusion

By manipulating the filename it is sometimes possible to bypass the virus scanner of an application. This can be done by tricking the logic that checks the result of clamscan, or by injecting into the communication with clamd. Shown above are several examples of these bugs in moderately popular software packages.
