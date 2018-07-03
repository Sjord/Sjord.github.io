<?php
$cross_base = "http://sjoerd.local/_demo/";
$cross_base1 = "http://ace/_demo/";
$cross_base2 = "http://172.17.0.2/_demo/";

header("Access-Control-Allow-Origin: *");

list($type, $remaining) = explode(",", $_REQUEST["type"], 2);

if (isset($_SERVER['HTTP_ORIGIN'])) {
    $origin_text = $_SERVER['HTTP_ORIGIN'];
} else {
    $origin_text = 'no origin header';
}

if ($type === 'redirect') {
    $dest = $cross_base."origin.php";
    if (strlen($remaining) == 14) {
        $dest = $cross_base1."origin.php";
    }
    if ($remaining) {
        $dest .= "?type=$remaining";
    }
    header("Location: $dest", true, 307);
}
if ($type === 'iframe') {
    die($origin_text);
}
if ($type === 'fetch') {
    die($origin_text);
}
if ($type === "script") {
    die("document.write(`$origin_text`);");
}
if ($type === "style") {
    header("Content-Type: text/css");
    $id = $_REQUEST["id"];
    die("#$id::before { content: \"$origin_text\"; }");
}

if ($type === "image") {
    header("Content-Type: image/svg+xml");
?>
<?xml version="1.0" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" 
  "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="10cm" height="33px" viewBox="0 0 1000 33" xmlns="http://www.w3.org/2000/svg" version="1.1">
  <text x="0" y="33" font-family="Verdana" font-size="33">
    <?php echo $origin_text; ?>
  </text>
</svg>
<?php
die();
}

?>
<!doctype html>
<html lang="en">
<head>
<link rel="stylesheet" type="text/css" href="origin.php?type=style&id=style_out_same" />
<link rel="stylesheet" type="text/css" href="<?= $cross_base ?>origin.php?type=style&id=style_out_cross" />
<script>
function get_fetch_origin(url, result_id) {
    fetch(url).then(function (response) {
        response.text().then(function (content) {
            document.getElementById(result_id).textContent = content;
        });
    });
}
</script>
</head>
<body>
<h2>Same-origin</h2>
<ul>
<li>current origin: <?php echo $origin_text; ?></li>
<li><a href="origin.php">link</a></li>
<li><a href="origin.php?type=redirect">redirect</a></li>
<li><button onclick="window.location='origin.php'">JS navigation</li>
<li><form method="GET"><input type="submit" value="GET form"></form></li>
<li><form method="POST"><input type="submit" value="POST form"></form></li>
<li><form method="POST" action="origin.php?type=redirect"><input type="submit" value="POST with redirect"></form></li>
</ul>

<table>
    <tr><th></th><th>same-origin</th><th>cross-origin</th></tr>
    <tr>
        <td>This page</td>
        <td><?= $origin_text; ?></td>
        <td></td>
    </tr>
    <tr>
        <td>JavaScript</td>
        <td><script src="origin.php?type=script"></script></td>
        <td><script src="<?= $cross_base ?>origin.php?type=script"></script></td>
    </tr>
    <tr>
        <td>Image</td>
        <td><img src="origin.php?type=image"></td>
        <td><img src="<?= $cross_base ?>origin.php?type=image"></td>
    </tr>
    <tr>
        <td>Iframe</td>
        <td><iframe src="origin.php?type=iframe" height="35"></iframe></td>
        <td><iframe src="<?= $cross_base ?>origin.php?type=iframe" height="35"></iframe></td>
    </tr>
    <tr>
        <td>Stylesheet</td>
        <td><span id="style_out_same"></span></td>
        <td><span id="style_out_cross"></span></td>
    </tr>
    <tr>
        <td>JS fetch</td>
        <td><span id="js_fetch_same">?</span><script>get_fetch_origin("origin.php?type=fetch", "js_fetch_same");</script></td>
        <td><span id="js_fetch_cross">?</span><script>get_fetch_origin("<?= $cross_base ?>origin.php?type=fetch", "js_fetch_cross");</script></td>
        <td><span id="js_fetch_redirect">?</span><script>get_fetch_origin("origin.php?type=redirect,redirect,fetch", "js_fetch_redirect");</script></td>
    </tr>

