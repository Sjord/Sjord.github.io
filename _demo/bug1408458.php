<?php
header('Content-Type:');
header('X-Content-Type-Options: nosniff,hello');
?>
<html><body>
<a href="https://bugs.chromium.org/p/chromium/issues/detail?id=1408458">Issue 1408458: Sniffing not disabled if X-Content-Type-Options contains multiple values or quoted values</a>
<marquee>This is obviously rendered as HTML</marquee>
<p>
    This page has no <tt>Content-Type</tt> header, and it does have a <tt>X-Content-Type-Options: nosniff,hello</tt> header. I expect sniffing to be disabled, since the first value in the <tt>X-Content-Type-Options</tt> header is nosniff, as described <a href="https://fetch.spec.whatwg.org/#x-content-type-options-header">here in the fetch spec</a>. But as this is rendered as HTML, sniffing is not disabled.
</p>
<h1>Expected rendering</h1>
<img src="bug1408458_expected.png">
<h1>Actual rendering</h1>
<img src="bug1408458_actual.png">
