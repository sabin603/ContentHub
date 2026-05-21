<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About - ContentHub</title>
  <link rel="stylesheet" href="about.css" />
</head>
<body>

<div class="wrapper">

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="site-name">CONTENTHUB</div>
    <nav>
      <a href="about.php" class="nav-link active">About</a>
      <a href="create-post.php" class="nav-link">Create Post</a>
      <a href="my-posts.php" class="nav-link">My Posts</a>
      <a href="logout.php" class="nav-link">Logout</a>
    </nav>
    <div class="sidebar-bottom">
      Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
    </div>
  </div>

  <!-- Main -->
  <div class="main">

    <div class="topbar">About</div>

    <div class="content">

      <!-- Intro -->
      <div class="section">
        <h2>What is ContentHub?</h2>
        <p>
          ContentHub is a simple video content sharing and recommendation platform built for students and content creators.
          It allows users to upload video posts, discover content shared by others, rate and comment on posts,
          and keep track of their own uploaded content — all in one place.
        </p>
      </div>

      <hr />

      <!-- Features -->
      <div class="section">
        <h2>Features</h2>

        <div class="feature">
          <h3>Create Post</h3>
          <p>
            Users can submit a video post by providing a title, video URL, thumbnail image, category,
            and a short description. The post is instantly visible to all other users on the feed.
          </p>
        </div>

        <div class="feature">
          <h3>Feed</h3>
          <p>
            The feed displays all uploaded posts from newest to oldest. Each post shows the thumbnail,
            title, category, uploader name, description, and a link to watch the video.
            Users scroll down to view more posts one at a time.
          </p>
        </div>

        <div class="feature">
          <h3>Search</h3>
          <p>
            Users can search for any post by its title using the search bar on the feed page.
            Results are filtered instantly based on the keyword entered.
          </p>
        </div>

        <div class="feature">
          <h3>Rating</h3>
          <p>
            Every post can be rated from 1 to 5 by any logged-in user. The average rating and total
            number of votes are displayed on each post. Users can update their rating at any time.
          </p>
        </div>

        <div class="feature">
          <h3>Comments</h3>
          <p>
            Users can leave comments on any post. All comments are shown below the post along with
            the commenter's name and the date and time of the comment.
          </p>
        </div>

        <div class="feature">
          <h3>Save Post</h3>
          <p>
            Users can save any post for later by clicking the Save button. Clicking again removes
            it from saved. This helps users bookmark content they want to revisit.
          </p>
        </div>

        <div class="feature">
          <h3>Report Post</h3>
          <p>
            If a post contains inappropriate, misleading, or irrelevant content, users can report it
            by selecting a reason. The report is sent directly to the admin for review.
          </p>
        </div>

        <div class="feature">
          <h3>My Posts</h3>
          <p>
            Each user has a dedicated page to view only their own uploaded posts. From here they can
            edit post details, delete a post, and see the total number of ratings and comments received.
          </p>
        </div>

          </p>
        </div>

      </div>

      <hr />

      <!-- How to use -->
      <div class="section">
        <h2>How to Use</h2>
        <table class="steps-table" border="1">
          <thead>
            <tr>
              <th>Step</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>1</td><td>Register an account on the Register page.</td></tr>
            <tr><td>2</td><td>Login with your email and password.</td></tr>
            <tr><td>3</td><td>Browse the feed to discover video content.</td></tr>
            <tr><td>4</td><td>Rate or comment on any post you find useful.</td></tr>
            <tr><td>5</td><td>Click Create Post to share your own video.</td></tr>
            <tr><td>6</td><td>Go to My Posts to manage your uploaded content.</td></tr>
            <tr><td>7</td><td>Save posts you want to revisit later.</td></tr>
            <tr><td>8</td><td>Report any post that violates content guidelines.</td></tr>
          </tbody>
        </table>
      </div>

      <hr />

      <!-- Contact -->
      <div class="section contact-section">
        <h2>Contact</h2>
        <p>For any queries, suggestions, or issues regarding ContentHub, feel free to reach out.</p>
        <table class="contact-table">
          <tr>
            <td><strong>Email</strong></td>
            <td><a href="mailto:sabin26bca23@kcc.edu.np">sabin26bca23@kcc.edu.np</a></td>
          </tr>
          <tr>
            <td><strong>Phone</strong></td>
            <td>+977-XXXXXXXXXX</td>
          </tr>
        </table>
      </div>

    </div>
  </div>
</div>

</body>
</html>