<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "db.php";

$result = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC");


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - ContentHub</title>
  <link rel="stylesheet" href="dashboard1.css" />
</head>
<body>

  <div class="wrapper">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="site-name">CONTENTHUB</div>

      <nav>
        <a href="about.php" class="nav-link active">About</a>
        <a href="create-post.php" class="nav-link">Create Post</a>
        <a href="my-posts.php" class="nav-link active">My Posts</a>
        <a href="logout.php" class="nav-link">Logout</a>
      </nav>

      <div class="sidebar-bottom">
        Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
      </div>
    </div>

    <!-- Main -->
    <div class="main">

      <div class="topbar">
        Latest Posts
      </div>

    </div>
  </div>
  

</body>
</html>