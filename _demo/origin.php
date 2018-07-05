<?php
$hosts = [
    ["sjoerd.local", "/_demo/"],
    ["172.17.0.2", "/_demo/"],
];

for ($i = 0; $i < count($hosts); $i++) {
    list($host, $path) = $hosts[$i];
    if ($host === $_SERVER['HTTP_HOST']) {
        $current_host_index = $i;
        break;
    }
}
$next_host_index = ($current_host_index + 1) % count($hosts);
$cross_base = "http://" . implode("", $hosts[$next_host_index]);

if (isset($_SERVER['HTTP_ORIGIN'])) {
    $origin_text = $_SERVER['HTTP_ORIGIN'];
} else {
    $origin_text = 'no origin header';
}

header("X-Reflected-Origin: $origin_text");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: HEAD, GET, PUT, POST, OPTIONS");
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    die();
}

list($type, $remaining) = explode(",", $_REQUEST["type"], 2);
if ($type === 'redirect') {
    $dest = $cross_base."origin.php";
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
function get_fetch_origin(method, url, result_id) {
    fetch(url, {method: method}).then(function (response) {
        if (method == "HEAD") {
            document.getElementById(result_id).textContent = response.headers.get("X-Reflected-Origin");
        } else {
            response.text().then(function (content) {
                document.getElementById(result_id).textContent = content;
            });
        }
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
<li><iframe src='data:text/html,<form method="POST" action="<?= $cross_base ?>origin.php" target="_top"><input type="submit" value="POST form from data"></form>'></iframe></li>
<li><form method="POST" action="origin.php?type=redirect"><input type="submit" value="POST with redirect"></form></li>
</ul>

<table>
    <tr><th></th><th>same-origin</th><th>cross-origin</th><th>multi-origin</th></tr>
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
        <td>JS fetch GET</td>
        <td><span id="js_fetch_get_same">?</span><script>get_fetch_origin("GET", "origin.php?type=fetch", "js_fetch_get_same");</script></td>
        <td><span id="js_fetch_get_cross">?</span><script>get_fetch_origin("GET", "<?= $cross_base ?>origin.php?type=fetch", "js_fetch_get_cross");</script></td>
        <td><span id="js_fetch_get_redirect">?</span><script>get_fetch_origin("GET", "<?= $cross_base ?>origin.php?type=redirect,fetch", "js_fetch_get_redirect");</script></td>
    </tr>
    <tr>
        <td>JS fetch HEAD</td>
        <td><span id="js_fetch_head_same">?</span><script>get_fetch_origin("HEAD", "origin.php?type=fetch", "js_fetch_head_same");</script></td>
        <td><span id="js_fetch_head_cross">?</span><script>get_fetch_origin("HEAD", "<?= $cross_base ?>origin.php?type=fetch", "js_fetch_head_cross");</script></td>
        <td><span id="js_fetch_head_redirect">?</span><script>get_fetch_origin("HEAD", "<?= $cross_base ?>origin.php?type=redirect,fetch", "js_fetch_head_redirect");</script></td>
    </tr>
    <tr>
        <td>JS fetch POST</td>
        <td><span id="js_fetch_post_same">?</span><script>get_fetch_origin("POST", "origin.php?type=fetch", "js_fetch_post_same");</script></td>
        <td><span id="js_fetch_post_cross">?</span><script>get_fetch_origin("POST", "<?= $cross_base ?>origin.php?type=fetch", "js_fetch_post_cross");</script></td>
        <td><span id="js_fetch_post_redirect">?</span><script>get_fetch_origin("POST", "<?= $cross_base ?>origin.php?type=redirect,fetch", "js_fetch_post_redirect");</script></td>
    </tr>
    <tr>
        <td>JS fetch PUT</td>
        <td><span id="js_fetch_put_same">?</span><script>get_fetch_origin("PUT", "origin.php?type=fetch", "js_fetch_put_same");</script></td>
        <td><span id="js_fetch_put_cross">?</span><script>get_fetch_origin("PUT", "<?= $cross_base ?>origin.php?type=fetch", "js_fetch_put_cross");</script></td>
        <td><span id="js_fetch_put_redirect">?</span><script>get_fetch_origin("PUT", "<?= $cross_base ?>origin.php?type=redirect,fetch", "js_fetch_put_redirect");</script></td>
    </tr>
</table>
