<?php
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'subscriber') {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();
$userID = $_SESSION['user_id'];
$subscriberID = $_SESSION['subscriber_id'];

// Query: Look for posts from active subscriptions that are NOT in a_read for this subscriber
$sql = "SELECT p.postID, p.title, p.createdAt FROM a_post p JOIN a_subscription s ON p.publisherID = s.publisherID WHERE s.userID = $userID AND s.status = 'Active' 
    AND p.postID NOT IN (SELECT postID FROM a_read WHERE subscriberID = $subscriberID) ORDER BY p.createdAt DESC";
$result = $conn->query($sql);
?>

<!-- HTML Structure -->
<!DOCTYPE html>
<html>
<head>
    <title>Catch Up</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Catch Up</h1>
        <nav class="navbar">
            <ul>
                <li><a href="subscriber_home.php">Home</a></li>
                <li><a href="search.php">Search</a></li>
                <li><a href="subscriptions.php">Subscriptions</a></li>
                <li><a href="catchup.php">Catch up</a></li>
                <li><a href="subscriber_profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <!-- Put any unread here -->
        <?php if ($result->num_rows > 0): ?>
            <ul>
            <?php while($row = $result->fetch_assoc()): ?>
                <li>
                    <a href="view_postsub.php?id=<?php echo $row['postID']; ?>"><?php echo htmlspecialchars($row['title']); ?></a>
                    <span style="font-size: 0.8em; color: #666;">(<?php echo $row['createdAt']; ?>)</span>
                </li>
            <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>You are all caught up!</p>
        <?php endif; ?>
        <?php $conn->close(); ?>
    </main>
</body>
</html>
