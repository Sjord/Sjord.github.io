<?php
header("Content-Type: text/plain");
header("X-Content-Type-Options: nosniff");

header("Access-Control-Allow-Origin: http://demo.sjoerdlangkemper.nl");
if (isset($_GET['allowHeaders'])) {
    header("Access-Control-Allow-Headers: Authorization, X-Custom-Header");
}
if (isset($_GET['allowCredentials'])) {
    header("Access-Control-Allow-Credentials: true");
}

if ($_SERVER['REQUEST_METHOD'] != "OPTIONS") {
    if (empty($_SERVER["REDIRECT_BYTE_AUTHORIZATION"])) {
        header('WWW-Authenticate: Basic realm="My Realm"', true, 401);
        echo "Authorization header missing";
    } else {
        echo "Authorization header received";
    }
}
