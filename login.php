<?php
session_start();
include "db.php";

// If already logged in, go to feed
if (isset($_SESSION['user_id'])) {
    header("Location: feed.php");
    exit();
}

$message = "";

if (isset($_POST['submit'])) {

    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql    = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: feed.php");
            exit();
        } else {
            $message = "Incorrect password.";
        }
    } else {
        $message = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - ContentHub</title>
  <link rel="stylesheet" href="auth.css" />
</head>
<body>

  <div class="auth-wrapper">

    <div class="auth-header">
      <h1>CONTENTHUB</h1>
    </div>

    <div class="auth-box">
      <h2>Login</h2>
      <p>Enter your credentials to continue.</p>

      <?php if ($message !== ""): ?>
        <div class="msg-error"><?php echo $message; ?></div>
      <?php endif; ?>

      <form action="" method="POST">

        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required placeholder="you@email.com" />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required placeholder="Your password" />
        </div>

        <div class="form-group">
          <button type="submit" name="submit">Login</button>
        </div>

      </form>

      <div class="auth-footer">
        Don't have an account? <a href="register.php">Register here</a>
      </div>
      <div class="adminlogin">
      <a href="admin-login.php">Login as Admin</a>
        </div>

    </div>

  </div>

</body>
</html>