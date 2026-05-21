<?php
session_start();
include "db.php";

$message = "";

if (isset($_POST['submit'])) {

    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    if ($password !== $confirm) {
        $message = "error|Passwords do not match.";
    } else {
        // Check if email already exists
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");

        if (mysqli_num_rows($check) > 0) {
            $message = "error|Email is already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed')";
            $result = mysqli_query($conn, $sql);

            if ($result) {
                $message = "success|Account created successfully. You can now log in.";
            } else {
                $message = "error|Something went wrong. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register - ContentHub</title>
  <link rel="stylesheet" href="auth.css" />
</head>
<body>

  <div class="auth-wrapper">

    <div class="auth-header">
      <h1>CONTENTHUB</h1>
    </div>

    <div class="auth-box">
      <h2>Create Account</h2>
      <p>Register to start sharing video content.</p>

      <?php if ($message !== ""): ?>
        <?php list($type, $text) = explode("|", $message, 2); ?>
        <div class="msg-<?php echo $type; ?>"><?php echo $text; ?></div>
      <?php endif; ?>

      <form action="" method="POST">

        <div class="form-group">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name" required placeholder="Your full name" />
        </div>

        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required placeholder="you@email.com" />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required placeholder="Create a password" />
        </div>

        <div class="form-group">
          <label for="confirm">Confirm Password</label>
          <input type="password" id="confirm" name="confirm" required placeholder="Repeat your password" />
        </div>

        <div class="form-group">
          <button type="submit" name="submit">Register</button>
        </div>

      </form>

      <div class="auth-footer">
        Already have an account? <a href="login.php">Login here</a>
      </div>

    </div>

  </div>

</body>
</html>