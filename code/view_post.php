<?php
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$postID = $_GET['id'] ?? 0;
$conn = getDBConnection();

$postStmt = $conn->prepare("SELECT title, content, publisherID, createdAt FROM a_post WHERE postID = ?");
$postStmt->bind_param("i", $postID);
$postStmt->execute();
$postResult = $postStmt->get_result();

if ($postResult->num_rows === 0) {
    die("Post not found.");
}

$post = $postResult->fetch_assoc();
$publisherID = $post['publisherID'];
$userID = $_SESSION['user_id'];

// Check subscription
$subStmt = $conn->prepare("SELECT status FROM a_subscription WHERE userID = ? AND publisherID = ? AND status = 'Active'");
$subStmt->bind_param("ii", $userID, $publisherID);
$subStmt->execute();
$isSubscribed = $subStmt->get_result()->num_rows > 0;
$subStmt->close();

if ($isSubscribed && $_SESSION['role'] === 'subscriber') {
    header("Location: view_postsub.php?id=$postID");
    exit();
}

// Log read if subscriber
if ($_SESSION['role'] === 'subscriber') {
    $subscriberID = $_SESSION['subscriber_id'];
    // Check if already read to avoid duplicates error if unique key exists or logic check
    $readCheck = $conn->query("SELECT readID FROM a_read WHERE subscriberID = $subscriberID AND postID = $postID");
    if ($readCheck->num_rows == 0) {
        $conn->query("INSERT INTO a_read (subscriberID, postID) VALUES ($subscriberID, $postID)");
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Post</title>
    <link rel="icon" type="image/png" href="images/icon.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    </header>
    <main>
        <p><em>Posted on: <?php echo $post['createdAt']; ?></em></p>
        <div class="content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>
        
        <?php
        // Show comments table if User is the Publisher of this post OR Admin (future proofing)
        // We need to check if $_SESSION['role'] is publisher and $_SESSION['publisher_id'] matches $publisherID
        $showComments = false;
        if ($_SESSION['role'] === 'publisher' && isset($_SESSION['publisher_id']) && $_SESSION['publisher_id'] == $publisherID) {
            $showComments = true;
        }
        
        if ($showComments) {
            $commentsQuery = "SELECT r.comment, r.createdAt, u.firstName, u.lastName 
                  FROM a_reviews r 
                  JOIN a_subscriber s ON r.subscriberID = s.subscriberID 
                  JOIN a_user u ON s.userID = u.userID 
                  WHERE r.postID = $postID ORDER BY r.createdAt DESC";
            $comments = $conn->query($commentsQuery);
            
            echo "<hr><h3>Comments</h3>";
            while ($cRow = $comments->fetch_assoc()) {
                echo "<div class='comment-box'>";
                echo "<div class='comment-meta'><strong>" . htmlspecialchars($cRow['firstName'] . ' ' . $cRow['lastName']) . "</strong> - " . $cRow['createdAt'] . "</div>";
                echo "<div class='comment-content'>" . htmlspecialchars($cRow['comment']) . "</div>";
                echo "</div>";
            }
        }
        ?>
        <br>
        <button onclick="history.back()" class="button">Back</button>
    </main>
</body>
</html>
