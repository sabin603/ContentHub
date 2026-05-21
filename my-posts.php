<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "db.php";

$user_id = $_SESSION['user_id'];

// Handle Delete Post
if (isset($_GET['delete_post'])) {
    $pid = (int) $_GET['delete_post'];
    // Make sure the post belongs to this user
    $check = mysqli_query($conn, "SELECT id FROM posts WHERE id = $pid AND author_id = $user_id");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "DELETE FROM posts WHERE id = $pid");
        mysqli_query($conn, "DELETE FROM ratings WHERE post_id = $pid");
        mysqli_query($conn, "DELETE FROM comments WHERE post_id = $pid");
        mysqli_query($conn, "DELETE FROM reports WHERE post_id = $pid");
        mysqli_query($conn, "DELETE FROM saved_posts WHERE post_id = $pid");
    }
    header("Location: my-posts.php");
    exit();
}

// Handle Edit Post
$edit_post = null;
if (isset($_GET['edit_post'])) {
    $pid       = (int) $_GET['edit_post'];
    $edit_post = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM posts WHERE id = $pid AND author_id = $user_id"));
}

// Handle Edit Submit
if (isset($_POST['update_post'])) {
    $pid         = (int) $_POST['post_id'];
    $title       = mysqli_real_escape_string($conn, trim($_POST['title']));
    $video_url   = mysqli_real_escape_string($conn, trim($_POST['video_url']));
    $category    = mysqli_real_escape_string($conn, $_POST['category']);
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));

    // Handle new thumbnail if uploaded
    $thumb_sql = "";
    if (!empty($_FILES['thumbnail']['name'])) {
        $image_name     = basename($_FILES['thumbnail']['name']);
        $thumbnail_path = "uploads/" . $image_name;
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnail_path);
        $thumb_sql = ", thumbnail_path = '$thumbnail_path'";
    }

    mysqli_query($conn, "
        UPDATE posts
        SET title = '$title', video_url = '$video_url', category = '$category', description = '$description' $thumb_sql
        WHERE id = $pid AND author_id = $user_id
    ");

    header("Location: my-posts.php");
    exit();
}

// Fetch only this user's posts
$posts = mysqli_query($conn, "SELECT * FROM posts WHERE author_id = $user_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Posts - ContentHub</title>
  <link rel="stylesheet" href="my-posts.css" />
</head>
<body>

<div class="wrapper">

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="site-name">CONTENTHUB</div>
    <nav>
      <a href="about.php" class="nav-link">About</a>
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

    <div class="topbar">My Posts</div>

    <div class="content">

      <!-- EDIT FORM (shown when edit is clicked) -->
      <?php if ($edit_post): ?>
      <div class="edit-form-box">
        <h3>Edit Post</h3>
        <form action="my-posts.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="post_id" value="<?php echo $edit_post['id']; ?>" />

          <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" required value="<?php echo htmlspecialchars($edit_post['title']); ?>" />
          </div>

          <div class="form-group">
            <label>Video URL</label>
            <input type="url" name="video_url" required value="<?php echo htmlspecialchars($edit_post['video_url']); ?>" />
          </div>

          <div class="form-group">
            <label>Category</label>
            <select name="category" required>
              <?php
              $cats = ['Technology', 'Science', 'Design', 'Business', 'Education'];
              foreach ($cats as $cat):
              ?>
                <option value="<?php echo $cat; ?>" <?php echo $edit_post['category'] === $cat ? 'selected' : ''; ?>>
                  <?php echo $cat; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label>Thumbnail <small>(leave empty to keep current)</small></label>
            <?php if (!empty($edit_post['thumbnail_path'])): ?>
              <div class="current-thumb">
                <img src="<?php echo htmlspecialchars($edit_post['thumbnail_path']); ?>" alt="Current Thumbnail" />
                <small>Current thumbnail</small>
              </div>
            <?php endif; ?>
            <input type="file" name="thumbnail" accept="image/*" />
          </div>

          <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="5" required><?php echo htmlspecialchars($edit_post['description']); ?></textarea>
          </div>

          <div class="form-btns">
            <button type="submit" name="update_post">Save Changes</button>
            <a href="my-posts.php" class="btn-cancel">Cancel</a>
          </div>

        </form>
      </div>
      <?php endif; ?>

      <!-- POSTS LIST -->
      <?php if (mysqli_num_rows($posts) === 0): ?>
        <p class="no-posts">You have not uploaded any posts yet. <a href="create-post.php">Create one.</a></p>
      <?php endif; ?>

      <?php while ($row = mysqli_fetch_assoc($posts)):

        $post_id = $row['id'];

        // Rating stats
        $rat = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_ratings
            FROM ratings WHERE post_id = $post_id
        "));
        $avg_rating    = $rat['avg_rating'] ? round($rat['avg_rating'], 1) : 0;
        $total_ratings = $rat['total_ratings'];

        // Comments
        $comments = mysqli_query($conn, "
            SELECT comments.comment, comments.created_at, users.name
            FROM comments
            LEFT JOIN users ON comments.user_id = users.id
            WHERE comments.post_id = $post_id
            ORDER BY comments.created_at ASC
        ");
        $total_comments = mysqli_num_rows($comments);

      ?>

      <div class="post-card">

        <?php if (!empty($row['thumbnail_path'])): ?>
          <img src="<?php echo htmlspecialchars($row['thumbnail_path']); ?>" alt="Thumbnail" />
        <?php endif; ?>

        <div class="post-body">

          <div class="post-top">
            <div>
              <span class="post-category"><?php echo htmlspecialchars($row['category']); ?></span>
              <span class="post-date"><?php echo date("d M Y", strtotime($row['created_at'])); ?></span>
            </div>
            <div class="post-actions">
              <a href="my-posts.php?edit_post=<?php echo $post_id; ?>" class="btn-edit">Edit</a>
              <a href="my-posts.php?delete_post=<?php echo $post_id; ?>"
                 class="btn-delete"
                 onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
            </div>
          </div>

          <h2><?php echo htmlspecialchars($row['title']); ?></h2>

          <p class="post-desc"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>

          <a href="<?php echo htmlspecialchars($row['video_url']); ?>" target="_blank" class="btn-watch">Watch Video</a>

          <!-- Stats -->
          <div class="post-stats">
            <div class="stat-box">
              <div class="stat-num"><?php echo $total_ratings; ?></div>
              <div class="stat-label">People Rated</div>
            </div>
            <div class="stat-box">
              <div class="stat-num"><?php echo $avg_rating > 0 ? $avg_rating . '/5' : 'N/A'; ?></div>
              <div class="stat-label">Avg Rating</div>
            </div>
            <div class="stat-box">
              <div class="stat-num"><?php echo $total_comments; ?></div>
              <div class="stat-label">Comments</div>
            </div>
          </div>

          <!-- Comments -->
          <?php if ($total_comments > 0): ?>
          <div class="comment-section">
            <div class="comment-title">Comments (<?php echo $total_comments; ?>)</div>
            <?php while ($c = mysqli_fetch_assoc($comments)): ?>
              <div class="comment-item">
                <div class="comment-meta">
                  <strong><?php echo htmlspecialchars($c['name']); ?></strong>
                  <span><?php echo date("d M Y, h:i A", strtotime($c['created_at'])); ?></span>
                </div>
                <div class="comment-text"><?php echo nl2br(htmlspecialchars($c['comment'])); ?></div>
              </div>
            <?php endwhile; ?>
          </div>
          <?php else: ?>
            <p class="no-comments">No comments on this post yet.</p>
          <?php endif; ?>

        </div>
      </div>

      <?php endwhile; ?>

    </div>
  </div>
</div>

</body>
</html>