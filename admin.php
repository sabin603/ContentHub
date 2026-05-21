<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin-login.php");
    exit();
}

include "db.php";

// Counts
$total_users      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users"))[0];
$total_posts      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM posts"))[0];
$total_categories = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM categories"))[0];
$total_reports    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM reports"))[0];

// Handle Add Category
$cat_msg = "";
if (isset($_POST['add_category'])) {
    $cat_name = mysqli_real_escape_string($conn, $_POST['cat_name']);
    $cat_desc = mysqli_real_escape_string($conn, $_POST['cat_desc']);
    $today    = date('Y-m-d H:i:s');
    if ($cat_name !== "") {
        $check = mysqli_query($conn, "SELECT id FROM categories WHERE name = '$cat_name'");
        if (mysqli_num_rows($check) > 0) {
            $cat_msg = "Category already exists.";
        } else {
            mysqli_query($conn, "INSERT INTO categories (name, description, created_at) VALUES ('$cat_name', '$cat_desc', '$today')");
            $cat_msg = "Category added successfully.";
            $total_categories = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM categories"))[0];
        }
    } else {
        $cat_msg = "Please enter a category name.";
    }
}

// Handle Delete User
if (isset($_GET['delete_user'])) {
    $uid = (int) $_GET['delete_user'];
    mysqli_query($conn, "DELETE FROM users WHERE id = $uid");
    header("Location: admin.php?page=users");
    exit();
}

// Handle Delete Category
if (isset($_GET['delete_cat'])) {
    $cid = (int) $_GET['delete_cat'];
    mysqli_query($conn, "DELETE FROM categories WHERE id = $cid");
    header("Location: admin.php?page=categories");
    exit();
}

// Handle Dismiss Report
if (isset($_GET['delete_report'])) {
    $rid = (int) $_GET['delete_report'];
    mysqli_query($conn, "DELETE FROM reports WHERE id = $rid");
    header("Location: admin.php?page=reports");
    exit();
}

// Handle Delete Post (from reports page)
if (isset($_GET['delete_post'])) {
    $pid = (int) $_GET['delete_post'];
    mysqli_query($conn, "DELETE FROM posts WHERE id = $pid");
    mysqli_query($conn, "DELETE FROM reports WHERE post_id = $pid");
    header("Location: admin.php?page=reports");
    exit();
}

// View post detail (for reports)
$view_post    = null;
$view_post_id = null;
if (isset($_GET['view_post'])) {
    $view_post_id = (int) $_GET['view_post'];
    $view_post    = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT posts.*, users.name AS author_name
        FROM posts
        LEFT JOIN users ON posts.author_id = users.id
        WHERE posts.id = $view_post_id
    "));
}

$current_page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CONTENTHUB - Admin</title>
  <link rel="stylesheet" href="admincss.css" />
</head>
<body>

<div class="wrapper">

  <div class="sidebar">
    <div class="site-name">CONTENTHUB</div>
    <div class="admin-label">Admin Panel</div>
    <nav>
      <a href="admin.php?page=dashboard"  class="nav-link <?php echo $current_page === 'dashboard'  ? 'active' : ''; ?>">Dashboard</a>
      <a href="admin.php?page=users"      class="nav-link <?php echo $current_page === 'users'      ? 'active' : ''; ?>">Users</a>
      <a href="admin.php?page=categories" class="nav-link <?php echo $current_page === 'categories' ? 'active' : ''; ?>">Categories</a>
      <a href="admin.php?page=videos"     class="nav-link <?php echo $current_page === 'videos'     ? 'active' : ''; ?>">Videos</a>
      <a href="admin.php?page=reports"    class="nav-link <?php echo $current_page === 'reports'    ? 'active' : ''; ?>">
        Reports <?php if ($total_reports > 0): ?>(<?php echo $total_reports; ?>)<?php endif; ?>
      </a>
    </nav>
    <div class="sidebar-bottom">
      <a href="adminlogout.php" style="color:#aaa; font-size:12px;">Logout</a>
    </div>
  </div>

  <div class="main">

    <div class="topbar">
      <?php
        $titles = ['dashboard' => 'Dashboard', 'users' => 'Users', 'categories' => 'Categories', 'videos' => 'Videos', 'reports' => 'Reports'];
        echo $titles[$current_page] ?? 'Dashboard';
      ?>
    </div>

    <!-- DASHBOARD -->
    <?php if ($current_page === 'dashboard'): ?>
    <div class="page active">
      <h2>Welcome, Admin</h2>
      <p>Here is a summary of the system.</p>
      <div class="summary-boxes">
        <div class="box"><div class="box-label">Total Users</div><div class="box-count"><?php echo $total_users; ?></div></div>
        <div class="box"><div class="box-label">Total Videos</div><div class="box-count"><?php echo $total_posts; ?></div></div>
        <div class="box"><div class="box-label">Categories</div><div class="box-count"><?php echo $total_categories; ?></div></div>
        <div class="box"><div class="box-label">Reports</div><div class="box-count"><?php echo $total_reports; ?></div></div>
      </div>
    </div>

    <!-- USERS -->
    <?php elseif ($current_page === 'users'): ?>
    <div class="page active">
      <h2>Users</h2>
      <table border="1">
        <thead>
          <tr><th>S.No</th><th>Full Name</th><th>Email</th><th>Joined Date</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php
          $users_result = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
          if (mysqli_num_rows($users_result) === 0): ?>
            <tr><td colspan="5" class="empty">No users found.</td></tr>
          <?php else: $i = 1; while ($u = mysqli_fetch_assoc($users_result)): ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td><?php echo htmlspecialchars($u['name']); ?></td>
              <td><?php echo htmlspecialchars($u['email']); ?></td>
              <td><?php echo date("d M Y", strtotime($u['created_at'])); ?></td>
              <td>
                <a href="admin.php?page=users&delete_user=<?php echo $u['id']; ?>"
                   class="action-btn btn-reject"
                   onclick="return confirm('Delete this user?')">Delete</a>
              </td>
            </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>

    <!-- CATEGORIES -->
    <?php elseif ($current_page === 'categories'): ?>
    <div class="page active">
      <h2>Categories</h2>
      <div class="form-section">
        <h3>Add New Category</h3>
        <form action="admin.php?page=categories" method="POST">
          <table class="form-table">
            <tr>
              <td><label>Category Name:</label></td>
              <td><input type="text" name="cat_name" required /></td>
            </tr>
            <tr>
              <td><label>Description:</label></td>
              <td><input type="text" name="cat_desc" /></td>
            </tr>
            <tr>
              <td></td>
              <td><button type="submit" name="add_category">Add Category</button></td>
            </tr>
          </table>
        </form>
        <?php if ($cat_msg !== ""): ?>
          <p id="cat-msg"><?php echo $cat_msg; ?></p>
        <?php endif; ?>
      </div>
      <table border="1">
        <thead>
          <tr><th>S.No</th><th>Category Name</th><th>Description</th><th>Added On</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php
          $categories_result = mysqli_query($conn, "SELECT * FROM categories ORDER BY created_at DESC");
          if (mysqli_num_rows($categories_result) === 0): ?>
            <tr><td colspan="5" class="empty">No categories added yet.</td></tr>
          <?php else: $i = 1; while ($c = mysqli_fetch_assoc($categories_result)): ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td><?php echo htmlspecialchars($c['name']); ?></td>
              <td><?php echo htmlspecialchars($c['description']); ?></td>
              <td><?php echo date("d M Y", strtotime($c['created_at'])); ?></td>
              <td>
                <a href="admin.php?page=categories&delete_cat=<?php echo $c['id']; ?>"
                   class="action-btn btn-reject"
                   onclick="return confirm('Delete this category?')">Delete</a>
              </td>
            </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>

    <!-- VIDEOS -->
    <?php elseif ($current_page === 'videos'): ?>
    <div class="page active">
      <h2>Uploaded Videos</h2>
      <table border="1">
        <thead>
          <tr><th>S.No</th><th>Title</th><th>Uploaded By</th><th>Category</th><th>Uploaded On</th><th>Video</th></tr>
        </thead>
        <tbody>
          <?php
          $posts_result = mysqli_query($conn, "
              SELECT posts.*, users.name AS author_name
              FROM posts
              LEFT JOIN users ON posts.author_id = users.id
              ORDER BY posts.created_at DESC
          ");
          if (mysqli_num_rows($posts_result) === 0): ?>
            <tr><td colspan="6" class="empty">No videos uploaded yet.</td></tr>
          <?php else: $i = 1; while ($p = mysqli_fetch_assoc($posts_result)): ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td><?php echo htmlspecialchars($p['title']); ?></td>
              <td><?php echo htmlspecialchars($p['author_name'] ?? 'Unknown'); ?></td>
              <td><?php echo htmlspecialchars($p['category']); ?></td>
              <td><?php echo date("d M Y", strtotime($p['created_at'])); ?></td>
              <td><a href="<?php echo htmlspecialchars($p['video_url']); ?>" target="_blank">Watch</a></td>
            </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>

    <!-- REPORTS -->
    <?php elseif ($current_page === 'reports'): ?>
    <div class="page active">
      <h2>User Reports</h2>

      <!-- Post Detail View (shown when admin clicks View Post) -->
      <?php if ($view_post): ?>
        <div class="post-detail-box">
          <div class="post-detail-header">
            <strong>Post Detail</strong>
            <a href="index.php?page=reports">Close</a>
          </div>

          <?php if (!empty($view_post['thumbnail_path'])): ?>
            <div class="post-detail-thumb">
              <img src="../contenthub/<?php echo htmlspecialchars($view_post['thumbnail_path']); ?>" alt="Thumbnail" />
            </div>
          <?php endif; ?>

          <table class="detail-table">
            <tr>
              <td><strong>Title</strong></td>
              <td><?php echo htmlspecialchars($view_post['title']); ?></td>
            </tr>
            <tr>
              <td><strong>Uploaded By</strong></td>
              <td><?php echo htmlspecialchars($view_post['author_name'] ?? 'Unknown'); ?></td>
            </tr>
            <tr>
              <td><strong>Category</strong></td>
              <td><?php echo htmlspecialchars($view_post['category']); ?></td>
            </tr>
            <tr>
              <td><strong>Description</strong></td>
              <td><?php echo nl2br(htmlspecialchars($view_post['description'])); ?></td>
            </tr>
            <tr>
              <td><strong>Video URL</strong></td>
              <td><a href="<?php echo htmlspecialchars($view_post['video_url']); ?>" target="_blank">Watch Video</a></td>
            </tr>
            <tr>
              <td><strong>Uploaded On</strong></td>
              <td><?php echo date("d M Y", strtotime($view_post['created_at'])); ?></td>
            </tr>
          </table>

          <div class="post-detail-actions">
            <a href="index.php?page=reports&delete_post=<?php echo $view_post['id']; ?>"
               class="action-btn btn-reject"
               onclick="return confirm('Delete this post and all its reports?')">Delete Post</a>
            <a href="index.php?page=reports" class="action-btn">Back to Reports</a>
          </div>
        </div>
      <?php endif; ?>

      <!-- Reports Table -->
      <table border="1">
        <thead>
          <tr>
            <th>S.No</th>
            <th>Reported By</th>
            <th>Post Title</th>
            <th>Reason</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $reports_result = mysqli_query($conn, "
              SELECT reports.*, users.name AS reporter_name, posts.title AS post_title, posts.id AS pid
              FROM reports
              LEFT JOIN users ON reports.user_id = users.id
              LEFT JOIN posts ON reports.post_id = posts.id
              ORDER BY reports.created_at DESC
          ");
          if (mysqli_num_rows($reports_result) === 0): ?>
            <tr><td colspan="6" class="empty">No reports submitted yet.</td></tr>
          <?php else: $i = 1; while ($r = mysqli_fetch_assoc($reports_result)): ?>
            <tr>
              <td><?php echo $i++; ?></td>
              <td><?php echo htmlspecialchars($r['reporter_name'] ?? 'Unknown'); ?></td>
              <td><?php echo htmlspecialchars($r['post_title'] ?? 'Deleted post'); ?></td>
              <td><?php echo htmlspecialchars($r['reason']); ?></td>
              <td><?php echo date("d M Y", strtotime($r['created_at'])); ?></td>
              <td class="action-cell">
                <?php if (!empty($r['pid'])): ?>
                  <a href="admin.php?page=reports&view_post=<?php echo $r['pid']; ?>"
                     class="action-btn">View Post</a>
                <?php endif; ?>
                <a href="admin.php?page=reports&delete_report=<?php echo $r['id']; ?>"
                   class="action-btn btn-reject"
                   onclick="return confirm('Dismiss this report?')">Dismiss</a>
              </td>
            </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>

    <?php endif; ?>

  </div>
</div>

</body>
</html>