<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "db.php";

$user_id = $_SESSION['user_id'];

// Handle rating submission
if (isset($_POST['submit_rating'])) {
    $post_id = (int) $_POST['post_id'];
    $rating  = (int) $_POST['rating'];
    if ($rating >= 1 && $rating <= 5) {
        $check = mysqli_query($conn, "SELECT id FROM ratings WHERE post_id = $post_id AND user_id = $user_id");
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($conn, "UPDATE ratings SET rating = $rating WHERE post_id = $post_id AND user_id = $user_id");
        } else {
            mysqli_query($conn, "INSERT INTO ratings (post_id, user_id, rating) VALUES ($post_id, $user_id, $rating)");
        }
    }
    header("Location: feed.php#post-" . $post_id);
    exit();
}

// Handle comment submission
if (isset($_POST['submit_comment'])) {
    $post_id = (int) $_POST['post_id'];
    $comment = mysqli_real_escape_string($conn, trim($_POST['comment']));
    if ($comment !== "") {
        mysqli_query($conn, "INSERT INTO comments (post_id, user_id, comment) VALUES ($post_id, $user_id, '$comment')");
    }
    header("Location: feed.php#post-" . $post_id);
    exit();
}

// Handle save post
if (isset($_POST['save_post'])) {
    $post_id = (int) $_POST['post_id'];
    $check   = mysqli_query($conn, "SELECT id FROM saved_posts WHERE user_id = $user_id AND post_id = $post_id");
    if (mysqli_num_rows($check) > 0) {
        // Already saved — unsave it
        mysqli_query($conn, "DELETE FROM saved_posts WHERE user_id = $user_id AND post_id = $post_id");
    } else {
        mysqli_query($conn, "INSERT INTO saved_posts (user_id, post_id) VALUES ($user_id, $post_id)");
    }
    header("Location: feed.php#post-" . $post_id);
    exit();
}

// Handle report submission
if (isset($_POST['submit_report'])) {
    $post_id = (int) $_POST['post_id'];
    $reason  = mysqli_real_escape_string($conn, trim($_POST['reason']));
    if ($reason !== "") {
        // One report per user per post
        $check = mysqli_query($conn, "SELECT id FROM reports WHERE user_id = $user_id AND post_id = $post_id");
        if (mysqli_num_rows($check) === 0) {
            mysqli_query($conn, "INSERT INTO reports (user_id, post_id, reason) VALUES ($user_id, $post_id, '$reason')");
        }
    }
    header("Location: feed.php#post-" . $post_id);
    exit();
}

// Search
$search      = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_safe = mysqli_real_escape_string($conn, $search);

if ($search_safe !== '') {
    $posts = mysqli_query($conn, "
        SELECT posts.*, users.name AS author_name
        FROM posts
        LEFT JOIN users ON posts.author_id = users.id
        WHERE posts.title LIKE '%$search_safe%'
        ORDER BY posts.created_at DESC
    ");
} else {
    $posts = mysqli_query($conn, "
        SELECT posts.*, users.name AS author_name
        FROM posts
        LEFT JOIN users ON posts.author_id = users.id
        ORDER BY posts.created_at DESC
    ");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Feed - ContentHub</title>
  <link rel="stylesheet" href="feed.css" />
</head>
<body>

<div class="page-wrapper">

  <div class="page-header">
    <h1>CONTENTHUB</h1>
    <div class="header-links">
      <span>Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
      <a href="dashboard.php">Dashboard</a>
      <a href="create-post.php">+ Create Post</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>

  <!-- Search Bar -->
  <form action="feed.php" method="GET" class="search-form">
    <input
      type="text"
      name="search"
      class="search-input"
      placeholder="Search posts by title..."
      value="<?php echo htmlspecialchars($search); ?>"
    />
    <button type="submit">Search</button>
    <?php if ($search !== ''): ?>
      <a href="feed.php" class="clear-search">Clear</a>
    <?php endif; ?>
  </form>

  <?php if ($search !== ''): ?>
    <p class="search-result-info">
      Showing results for: <strong><?php echo htmlspecialchars($search); ?></strong>
    </p>
  <?php endif; ?>

  <div class="feed">

    <?php if (mysqli_num_rows($posts) === 0): ?>
      <p class="no-posts">
        <?php echo $search !== '' ? 'No posts found for your search.' : 'No posts yet. '; ?>
        <?php if ($search === ''): ?><a href="create-post.php">Create the first one.</a><?php endif; ?>
      </p>
    <?php endif; ?>

    <?php while ($row = mysqli_fetch_assoc($posts)):

      $post_id = $row['id'];

      // Average rating
      $rat_result  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) AS avg_rating, COUNT(*) AS total FROM ratings WHERE post_id = $post_id"));
      $avg_rating  = $rat_result['avg_rating'] ? round($rat_result['avg_rating'], 1) : 0;
      $total_votes = $rat_result['total'];

      // User's own rating
      $my_row    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT rating FROM ratings WHERE post_id = $post_id AND user_id = $user_id"));
      $my_rating = $my_row ? $my_row['rating'] : 0;

      // Is post saved by this user?
      $save_check = mysqli_query($conn, "SELECT id FROM saved_posts WHERE user_id = $user_id AND post_id = $post_id");
      $is_saved   = mysqli_num_rows($save_check) > 0;

      // Has user already reported this post?
      $report_check  = mysqli_query($conn, "SELECT id FROM reports WHERE user_id = $user_id AND post_id = $post_id");
      $already_reported = mysqli_num_rows($report_check) > 0;

      // Comments
      $comments = mysqli_query($conn, "
          SELECT comments.comment, comments.created_at, users.name
          FROM comments
          LEFT JOIN users ON comments.user_id = users.id
          WHERE comments.post_id = $post_id
          ORDER BY comments.created_at ASC
      ");

    ?>

    <div class="post-card" id="post-<?php echo $post_id; ?>">

      <?php if (!empty($row['thumbnail_path'])): ?>
        <div class="post-thumbnail">
          <img src="<?php echo htmlspecialchars($row['thumbnail_path']); ?>" alt="Thumbnail" />
        </div>
      <?php endif; ?>

      <div class="post-body">

        <div class="post-meta">
          <span class="post-category"><?php echo htmlspecialchars($row['category']); ?></span>
          <span class="post-date"><?php echo date("d M Y", strtotime($row['created_at'])); ?></span>
        </div>

        <h2 class="post-title"><?php echo htmlspecialchars($row['title']); ?></h2>

        <div class="post-author">
          Posted by: <strong><?php echo htmlspecialchars($row['author_name'] ?? 'Unknown'); ?></strong>
        </div>

        <p class="post-desc"><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>

        <!-- Post Actions: Watch, Save, Report -->
        <div class="post-actions">

          <a href="<?php echo htmlspecialchars($row['video_url']); ?>" target="_blank" class="btn-watch">Watch Video</a>

          <!-- Save -->
          <form action="feed.php" method="POST" style="display:inline;">
            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
            <button type="submit" name="save_post" class="btn-action <?php echo $is_saved ? 'btn-saved' : ''; ?>">
              <?php echo $is_saved ? 'Saved' : 'Save'; ?>
            </button>
          </form>

          <!-- Report -->
          <?php if ($already_reported): ?>
            <span class="btn-action btn-reported">Reported</span>
          <?php else: ?>
            <button class="btn-action btn-report-toggle" onclick="toggleReport(<?php echo $post_id; ?>)">Report</button>
          <?php endif; ?>

        </div>

        <!-- Report Form (hidden by default) -->
        <?php if (!$already_reported): ?>
        <div class="report-form-wrap" id="report-form-<?php echo $post_id; ?>" style="display:none;">
          <form action="feed.php" method="POST">
            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
            <label>Reason for reporting:</label>
            <select name="reason" required>
              <option value="">-- Select a reason --</option>
              <option value="Spam">Spam</option>
              <option value="Inappropriate Content">Inappropriate Content</option>
              <option value="Misleading Information">Misleading Information</option>
              <option value="Irrelevant Content">Irrelevant Content</option>
              <option value="Other">Other</option>
            </select>
            <div class="report-btns">
              <button type="submit" name="submit_report">Submit Report</button>
              <button type="button" onclick="toggleReport(<?php echo $post_id; ?>)">Cancel</button>
            </div>
          </form>
        </div>
        <?php endif; ?>

        <!-- RATING -->
        <div class="rating-section">
          <div class="rating-summary">
            Average Rating:
            <strong><?php echo $avg_rating > 0 ? $avg_rating . " / 5" : "No ratings yet"; ?></strong>
            <?php if ($total_votes > 0): ?>
              (<?php echo $total_votes; ?> vote<?php echo $total_votes > 1 ? 's' : ''; ?>)
            <?php endif; ?>
          </div>

          <form action="feed.php" method="POST" class="rating-form">
            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
            <label><?php echo $my_rating > 0 ? 'Your Rating:' : 'Rate this post:'; ?></label>
            <div class="star-row">
              <?php for ($s = 1; $s <= 5; $s++): ?>
                <label class="star-label <?php echo $my_rating == $s ? 'selected' : ''; ?>">
                  <input type="radio" name="rating" value="<?php echo $s; ?>"
                    <?php echo $my_rating == $s ? 'checked' : ''; ?> required />
                  <?php echo $s; ?>
                </label>
              <?php endfor; ?>
            </div>
            <button type="submit" name="submit_rating">
              <?php echo $my_rating > 0 ? 'Update Rating' : 'Submit Rating'; ?>
            </button>
          </form>
        </div>

        <!-- COMMENTS -->
        <div class="comment-section">
          <div class="comment-title">Comments (<?php echo mysqli_num_rows($comments); ?>)</div>

          <?php if (mysqli_num_rows($comments) === 0): ?>
            <p class="no-comments">No comments yet. Be the first to comment.</p>
          <?php endif; ?>

          <?php while ($c = mysqli_fetch_assoc($comments)): ?>
            <div class="comment-item">
              <div class="comment-meta">
                <strong><?php echo htmlspecialchars($c['name']); ?></strong>
                <span><?php echo date("d M Y, h:i A", strtotime($c['created_at'])); ?></span>
              </div>
              <div class="comment-text"><?php echo nl2br(htmlspecialchars($c['comment'])); ?></div>
            </div>
          <?php endwhile; ?>

          <form action="feed.php" method="POST" class="comment-form">
            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
            <textarea name="comment" rows="3" placeholder="Write a comment..." required></textarea>
            <button type="submit" name="submit_comment">Post Comment</button>
          </form>
        </div>

      </div>
    </div>

    <?php endwhile; ?>

  </div>

</div>

<script src="feed.js"></script>
</body>
</html>