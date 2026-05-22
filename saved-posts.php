<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "db.php";

$user_id = $_SESSION['user_id'];

// Handle unsave
if (isset($_POST['unsave_post'])) {
    $post_id = (int) $_POST['post_id'];
    mysqli_query($conn, "DELETE FROM saved_posts WHERE user_id = $user_id AND post_id = $post_id");
    header("Location: saved-posts.php");
    exit();
}

// Fetch all posts saved by this user
$saved = mysqli_query($conn, "
    SELECT posts.*, users.name AS author_name
    FROM saved_posts
    JOIN posts ON saved_posts.post_id = posts.id
    LEFT JOIN users ON posts.author_id = users.id
    WHERE saved_posts.user_id = $user_id
    ORDER BY saved_posts.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Saved Posts - ContentHub</title>
  <link rel="stylesheet" href="saved-posts.css" />
</head>
<body>

<div class="wrapper">

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="site-name">CONTENTHUB</div>
    <nav>
      <a href="dashboard.php" class="nav-link">Home</a>
      <a href="about.php" class="nav-link">About</a>
      <a href="category.php" class="nav-link">Category</a>
      <a href="search.php" class="nav-link">Search</a>
      <a href="create-post.php" class="nav-link">Create Post</a>
      <a href="my-posts.php" class="nav-link">My Posts</a>
      <a href="saved-posts.php" class="nav-link active">Saved Posts</a>
      <a href="performance.php" class="nav-link">View Performance</a>
      <a href="logout.php" class="nav-link">Logout</a>
    </nav>
    <div class="sidebar-bottom">
      Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
    </div>
  </div>

  <!-- Main -->
  <div class="main">

    <div class="topbar">Saved Posts</div>

    <div class="content">

      <?php if (mysqli_num_rows($saved) === 0): ?>
        <p class="no-posts">
          You have not saved any posts yet.
          <a href="feed.php">Browse the feed.</a>
        </p>
      <?php endif; ?>

      <?php while ($row = mysqli_fetch_assoc($saved)):

        $post_id = $row['id'];

        // Average rating
        $rat        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total FROM ratings WHERE post_id = $post_id"));
        $avg_rating = $rat['avg_rating'] ? round($rat['avg_rating'], 1) : 0;
        $total_votes = $rat['total'];

        // Total comments
        $total_comments = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM comments WHERE post_id = $post_id"))[0];

      ?>

      <div class="post-card">

        <?php if (!empty($row['thumbnail_path'])): ?>
          <img src="<?php echo htmlspecialchars($row['thumbnail_path']); ?>" alt="Thumbnail" />
        <?php endif; ?>

        <div class="post-body">

          <div class="post-meta">
            <span class="post-category"><?php echo htmlspecialchars($row['category']); ?></span>
            <span class="post-date"><?php echo date("d M Y", strtotime($row['created_at'])); ?></span>
          </div>

          <h2><?php echo htmlspecialchars($row['title']); ?></h2>

          <div class="post-author">
            Posted by: <strong><?php echo htmlspecialchars($row['author_name'] ?? 'Unknown'); ?></strong>
          </div>

          <p class="post-desc"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>

          <!-- Stats -->
          <div class="post-stats">
            <div class="stat-box">
              <div class="stat-num"><?php echo $avg_rating > 0 ? $avg_rating . '/5' : 'N/A'; ?></div>
              <div class="stat-label">Avg Rating</div>
            </div>
            <div class="stat-box">
              <div class="stat-num"><?php echo $total_votes; ?></div>
              <div class="stat-label">Votes</div>
            </div>
            <div class="stat-box">
              <div class="stat-num"><?php echo $total_comments; ?></div>
              <div class="stat-label">Comments</div>
            </div>
          </div>

          <!-- Actions -->
          <div class="post-actions">
            <a href="<?php echo htmlspecialchars($row['video_url']); ?>" target="_blank" class="btn-watch">Watch Video</a>

            <form action="saved-posts.php" method="POST" style="display:inline;">
              <input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
              <button type="submit" name="unsave_post" class="btn-unsave"
                onclick="return confirm('Remove this post from saved?')">Remove</button>
            </form>
          </div>

        </div>
      </div>

      <?php endwhile; ?>

    </div>
  </div>
</div>

</body>
</html>