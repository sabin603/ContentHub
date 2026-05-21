<?php
session_start();

// Admin credentials - change these if you want
define('ADMIN_EMAIL',    'admin@contenthub.com');
define('ADMIN_PASSWORD', 'admin123');

if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit();
}

$error = "";

if (isset($_POST['submit'])) {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    if ($email === ADMIN_EMAIL && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login - ContentHub</title>
  <link rel="stylesheet" href="admin-login.css" />
</head>
<body>

  <div class="login-wrapper">

    <div class="login-header">
      <h1>CONTENTHUB</h1>
      <p>Admin Panel</p>
    </div>

    <div class="login-box">
      <h2>Admin Login</h2>

      <?php if ($error !== ""): ?>
        <div class="msg-error"><?php echo $error; ?></div>
      <?php endif; ?>

      <form action="" method="POST">

        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required placeholder="admin@contenthub.com" />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required placeholder="Enter password" />
        </div>

        <div class="form-group">
          <button type="submit" name="submit">Login</button>
        </div>

      </form>
    </div>

  </div>

</body>
</html>