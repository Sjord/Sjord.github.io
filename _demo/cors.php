<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-Prototype-Version, X-Requested-With");
header("Access-Control-Expose-Headers: X-JSON");
header("Content-Type: application/javascript");
?>
alert(document.domain);
