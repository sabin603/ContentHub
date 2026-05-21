<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "db.php";

$message = "";

if (isset($_POST['submit'])) {

    $author_id   = $_SESSION['user_id'];
    $title       = mysqli_real_escape_string($conn, $_POST['title']);
    $video_url   = mysqli_real_escape_string($conn, $_POST['video_url']);
    $category    = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $thumbnail_path = "";
    if (!empty($_FILES['thumbnail']['name'])) {
        $image_name     = basename($_FILES['thumbnail']['name']);
        $thumbnail_path = "uploads/" . $image_name;
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnail_path);
    }

    $sql = "INSERT INTO posts (author_id, title, video_url, thumbnail_path, category, description)
            VALUES ('$author_id', '$title', '$video_url', '$thumbnail_path', '$category', '$description')";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $message = "success";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Post - ContentHub</title>
  <link rel="stylesheet" href="create-post.css" />
</head>
<body>

  <div class="page-wrapper">

    <div class="page-header">
      <h1>CONTENTHUB</h1>
      <div class="header-links">
        <span>Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <a href="feed.php">View Feed</a>
        <a href="logout.php">Logout</a>
      </div>
    </div>

    <div class="form-container">
      <h2>Create a Post</h2>
      <p>Fill in the details below to submit your video content.</p>

      <?php if ($message === "success"): ?>
        <div class="msg-success">
          Post submitted successfully! <a href="feed.php">View Feed</a>
        </div>
      <?php elseif ($message !== ""): ?>
        <div class="msg-error"><?php echo $message; ?></div>
      <?php endif; ?>

      <form action="" method="POST" enctype="multipart/form-data">

        <div class="form-group">
          <label for="title">Post Title *</label>
          <input type="text" id="title" name="title" required placeholder="Enter a title" />
        </div>

        <div class="form-group">
          <label for="video_url">Video URL *</label>
          <input type="url" id="video_url" name="video_url" required placeholder="https://youtube.com/watch?v=..." />
        </div>

        <div class="form-group">
          <label for="category">Category *</label>
          <select id="category" name="category" required>
            <option value="">-- Select Category --</option>
            <option value="Technology">Technology</option>
            <option value="Science">Science</option>
            <option value="Design">Design</option>
            <option value="Business">Business</option>
            <option value="Education">Education</option>
          </select>
        </div>

        <div class="form-group">
          <label for="thumbnail">Thumbnail Image *</label>
          <input type="file" id="thumbnail" name="thumbnail" accept="image/*" required />
        </div>

        <div class="form-group">
          <label for="description">Description *</label>
          <textarea id="description" name="description" rows="5" required placeholder="Write a short description..."></textarea>
        </div>

        <div class="form-group">
          <button type="submit" name="submit">Submit Post</button>
        </div>

      </form>
    </div>

  </div>

</body>
</html>