<?php
header("Content-Type: text/plain");
header("X-Content-Type-Options: nosniff");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, X-Custom-Header");

if ($_SERVER['REQUEST_METHOD'] != "OPTIONS") {
    if (empty($_SERVER["REDIRECT_BYTE_AUTHORIZATION"])) {
        header('WWW-Authenticate: Basic realm="My Realm"', true, 401);
        echo "Authorization header missing";
    } else {
        echo "Authorization header received";
    }
}
