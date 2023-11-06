<?php
$username = $_COOKIE['username'] ?? "Anonymous";
echo "<i>".htmlentities($username)."</i>";
