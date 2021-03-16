<?php
$iv = "circumnavigation";
$key = "whatchamacallits";
$method = "AES-256-CBC";

if (isset($_POST['username'])) {
    $_POST['is_admin'] = 0;
    $json = json_encode($_POST);
    $ciphertext = openssl_encrypt($json, $method, $key, OPENSSL_RAW_DATA, $iv);
    header('Location: /bitflip.php?data='.bin2hex($ciphertext));
}

if (isset($_GET['data'])) {
    $ciphertext = hex2bin($_GET['data']);
    $plaintext = openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
    $plaintext = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '?', trim($plaintext));
    $data = json_decode($plaintext);
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <style type="text/css">
      label, input {
        display: block;
      }
      input {
        margin-bottom: 1em;
        width: 200px;
        box-sizing: border-box;
      }
      body {
        display: flex;
        justify-content: center;
        align-items: center;
        font-family: sans-serif;
        background-color: #312;
      }
      html, body {
        height: 99%;
      }
      form {
        background-color: white;
        padding: 2em;
        border: solid black 2px;
      }
      aside {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        border-bottom: solid black 1px;
        background-color: #fcc;
        padding: 1em;
        text-align: center;
      }
      .loggedin, a {
        color: white;
        text-align: center;
      }
    </style>
  </head>
  <body>
    <?php
        if (isset($plaintext)) {
            if (is_null($data)) {
                echo '<aside role="alert">Could not decode JSON</aside>';
            } else if ($data->is_admin) {
                echo '<div class="loggedin"><h1>Welcome, administrator!</h1></div><!--';
            } else if ($data->username) {
                echo '<aside role="alert">You are no administrator, '.htmlentities($data->username).'</aside>';
            }
        }
    ?>
    <form method="POST">
      <label for="username">username</label>
      <input type="username" id="username" name="username" placeholder="username" required autocomplete="username" autofocus>
      <label for="nickname">nickname</label>
      <input type="text" id="nickname" name="nickname" placeholder="nickname">
      <label for="nickname">hobbies</label>
      <input type="text" id="hobbies" name="hobbies" placeholder="hobbies">
      <input type="submit" value="Submit">
    </form>
    <script>
        let data = <?php echo $plaintext; ?>;
        //         ^               ^               ^               ^               ^               ^               ^               ^
        for (let key in data) {
            let elem = document.getElementById(key);
            if (elem) {
                elem.value = data[key];
            }
        }
    </script>
    <!-- -->
  </body>
</html>
