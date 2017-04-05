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
    </style>
  </head>
  <body>
    <?php
      function generate_crsf_token($time) {
        $key = 'secret';
        if (empty($time)) {
          $time = time();
        }
        $mac = substr(hash_hmac("sha1", $time, $key), 0, 10);
        return "$time:$mac";
      }

      function csrf_token_is_valid($token) {
        list($time, $mac) = explode(':', $token);
        return $time > (time() - 30) && $token === generate_crsf_token($time);
      }

      if (isset($_POST['username']) && isset($_POST['password'])) {
        if (csrf_token_is_valid($_POST['csrf_token'])) {
          echo '<aside role="alert">Invalid username or password</aside>';
        } else {
          echo '<aside role="alert">Invalid CSRF token</aside>';
        }
      }
    ?>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?php echo generate_crsf_token(); ?>">
      <label for="username">username</label>
      <input type="username" id="username" name="username" placeholder="username" required autocomplete="username" autofocus>
      <label for="password">password</label>
      <input type="password" id="password" name="password" placeholder="password" required autocomplete="current-password">
      <input type="submit" value="Log in">
    </form>
  </body>
</html>
