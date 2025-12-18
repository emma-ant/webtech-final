<?php
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'subscriber') {
    header("Location: login.php");
    exit();
}

$postID = $_GET['id'] ?? 0;
$subscriberID = $_SESSION['subscriber_id'];
$conn = getDBConnection();

// Fetch post details
$postStmt = $conn->prepare("SELECT title, content, publisherID, createdAt FROM a_post WHERE postID = ?");
$postStmt->bind_param("i", $postID);
$postStmt->execute();
$post = $postStmt->get_result()->fetch_assoc();

if (!$post) die("Post not found.");

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO a_reviews (postID, subscriberID, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $postID, $subscriberID, $comment);
        $stmt->execute();
        $stmt->close();
    }
}

// Mark as read
$readCheck = $conn->query("SELECT readID FROM a_read WHERE subscriberID = $subscriberID AND postID = $postID");
if ($readCheck->num_rows == 0) {
    $conn->query("INSERT INTO a_read (subscriberID, postID, readDate) VALUES ($subscriberID, $postID, NOW())");
}

// Fetch comments
$commentsQuery = "SELECT r.comment, r.createdAt, u.firstName, u.lastName 
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
    <title>View Post (Subscribed)</title>
    <link rel="icon" type="image/png" href="images/icon.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <nav class="navbar">
            <ul>
                <li><a href="subscriber_home.php">Home</a></li>
                <li><a href="search.php">Search</a></li>
                <li><a href="subscriptions.php">Subscriptions</a></li>
                <li><a href="catchup.php">Catchup</a></li>
                <li><a href="subscriber_profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <p><em>Posted on: <?php echo $post['createdAt']; ?></em></p>
        <div class="content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>
        <hr>
        <h3>Comments</h3>
        <form method="POST">
            <textarea name="comment" required placeholder="Leave a comment..."></textarea><br>
            <input type="submit" value="Post Comment" class="button">
        </form>
        <br>
        
        <?php while ($row = $comments->fetch_assoc()): ?>
            <div class="comment-box">
                <div class="comment-meta">
                    <strong><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['lastName']); ?></strong> 
                    - <?php echo $row['createdAt']; ?>
                </div>
                <div class="comment-content">
                    <?php echo htmlspecialchars($row['comment']); ?>
                </div>
            </div>
        <?php endwhile; ?>
        
        <br>
        <button onclick="history.back()" class="button">Back</button>
    </main>
</body>
</html>
