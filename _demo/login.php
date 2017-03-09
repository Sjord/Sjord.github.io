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
      if (isset($_POST['username']) && isset($_POST['password'])) {
        echo '<aside role="alert">Invalid username or password</aside>';
      }
    ?>
    <form method="POST">
      <label for="username">username</label>
      <input type="username" id="username" name="username" placeholder="username" required autocomplete="username" autofocus>
      <label for="password">password</label>
      <input type="password" id="password" name="password" placeholder="password" required autocomplete="current-password">
      <input type="submit" value="Log in">
    </form>
  </body>
</html>
