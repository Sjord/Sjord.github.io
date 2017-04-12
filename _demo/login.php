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

      function credentials_are_valid($username, $password) {
        $key = 'secret';
        $check_length = 3;
        if (empty($username) || empty($password)) {
          return false;
        }
        if ($username === $password) {
          return false;
        }
        $mac_u = substr(hash_hmac("sha1", $username, $key), 0, $check_length);
        $mac_p = substr(hash_hmac("sha1", $password, $key), 0, $check_length);
        return $mac_u === $mac_p;
      }

      if (isset($_POST['username']) && isset($_POST['password'])) {
        if (csrf_token_is_valid($_POST['csrf_token'])) {
          if (credentials_are_valid($_POST['username'], $_POST['password'])) {
            printf('<div class="loggedin"><h1>Welcome, %s</h1>', $_POST['username']);
            printf('<a href="%s">Logout</a></div>', basename(__FILE__));
            echo "\n<!--\n";
          } else {
            echo '<aside role="alert">Invalid username or password</aside>';
          }
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
    <!-- -->
  </body>
</html>
