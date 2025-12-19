<?php
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$postID = $_GET['id'] ?? 0;
$conn = getDBConnection();

// Fetch post details
$postStmt = $conn->prepare("SELECT title, content, publisherID, createdAt FROM a_post WHERE postID = ?");
$postStmt->bind_param("i", $postID);
$postStmt->execute();
$post = $postStmt->get_result()->fetch_assoc();

if (!$post) die("Post not found.");

// Handle Post Delete
if (isset($_POST['delete_post'])) {
    $conn->query("DELETE FROM a_post WHERE postID = $postID");
    header("Location: admin_posts.php");
    exit();
}

// Handle Comment Delete
if (isset($_POST['delete_comment_id'])) {
    $cid = $_POST['delete_comment_id'];
    $conn->query("DELETE FROM a_reviews WHERE reviewID = $cid");
}

// Fetch comments
$commentsQuery = "SELECT r.reviewID, r.comment, r.createdAt, u.firstName, u.lastName 
                  FROM a_reviews r 
                  JOIN a_subscriber s ON r.subscriberID = s.subscriberID 
                  JOIN a_user u ON s.userID = u.userID 
                  WHERE r.postID = $postID ORDER BY r.createdAt DESC";
$comments = $conn->query($commentsQuery);

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin View Post</title>
    <link rel="icon" type="image/png" href="images/icon.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <nav class="navbar">
            <ul>
                <li><a href="http://localhost/phpmyadmin" target="_blank">Database</a></li>
                <li><a href="admin_users.php">Users</a></li>
                <li><a href="admin_posts.php">Posts</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <p><em>Posted on: <?php echo $post['createdAt']; ?></em></p>
        <div class="content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>
        
        <br>
        <form method="POST" onsubmit="return confirm('Delete this post?');">
            <input type="hidden" name="delete_post" value="1">
            <input type="submit" value="Delete Post" class="button" style="background-color: #ff0000; color: white;">
        </form>
        
        <hr>
        <h3>Comments</h3>
        <?php while($row = $comments->fetch_assoc()): ?>
            <div class="comment-box">
                <div class="comment-meta">
                    <strong><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['lastName']); ?></strong> 
                    - <?php echo $row['createdAt']; ?>
                </div>
                <div class="comment-content">
                    <?php echo htmlspecialchars($row['comment']); ?>
                </div>
                <div style="margin-top: 5px;">
                     <form method="POST" onsubmit="return confirm('Delete this comment?');">
                        <input type="hidden" name="delete_comment_id" value="<?php echo $row['reviewID']; ?>">
                        <input type="submit" value="Delete" class="button" style="background-color: #ffcccc; color: red; padding: 2px 10px; font-size: 0.8em;">
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
        <br>
        <button onclick="history.back()" class="button">Back</button>
    </main>
</body>
</html>
