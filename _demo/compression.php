<?php
    ob_start();
?>
<html>
    <head>
        <title>Compression side-channel attack demonstration</title>
    </head>
    <body>
        <p>
            <h1>Compression side-channel attack demonstration page</h1>
        </p>
        <form method="GET">
            Search for: <input type="text" name="search">
            <input type="submit">
        </form>
        <p>
            <?php
                if ($_GET['search']) {
                    echo 'No results found for search "'.htmlentities($_GET['search']).'".';
                }
            ?>
        </p>
        <p>
            Your secret code is: <?php
                echo substr(hash_hmac("SHA256", $_SERVER['REMOTE_ADDR'], "mysecretkey"), 0, 8); 
            ?>.
            See if you can determine it from the size of this page alone!
        </p>
        <p>
            This page is approximately nnnnn bytes compressed and mmmmm bytes uncompressed.
        </p>
    </body>
</html>
<?php
    $orig_page = ob_get_contents();
    ob_end_clean();

    $page = $orig_page;
    for ($i = 0; $i <= 3; $i++) {
        $len = strlen($page);
        $compressed = gzencode($page);
        $clen = strlen($compressed);

        $page = str_replace('nnnnn', $clen, $orig_page);
        $page = str_replace('mmmmm', $len, $page);
    }

    header('Content-Encoding: gzip');
    echo $compressed;
