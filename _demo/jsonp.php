<?php
$data = [
    "hello" => "world",
    "number" => random_int(0,  PHP_INT_MAX),
    "token" => hash_hmac("sha256", $_SERVER["REMOTE_ADDR"] . $_SERVER["HTTP_USER_AGENT"], "key")
];
$json = json_encode($data);

if ($_GET["callback"]) {
    header("Content-Type: application/javascript");
    echo $_GET["callback"]."(".$json.")";
} else {
    header("Content-Type: application/json");
    echo $json;
}
