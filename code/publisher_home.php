<?php
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'publisher') {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();
$publisherID = $_SESSION['publisher_id'];
$userName = $_SESSION['user_name'];

// Stats
// Number of posts
$postsResult = $conn->query("SELECT COUNT(*) as count FROM a_post WHERE publisherID = $publisherID");
$postsCount = $postsResult->fetch_assoc()['count'];

// Number of subscribers
$subsResult = $conn->query("SELECT COUNT(*) as count FROM a_subscription WHERE publisherID = $publisherID AND status = 'Active'");
$subsCount = $subsResult->fetch_assoc()['count'];

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Publisher Home</title>
    <link rel="icon" type="image/png" href="images/icon.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Publisher Dashboard</h1>
        <nav class="navbar">
            <ul>
                <li><a href="publisher_home.php">Home</a></li>
                <li><a href="search.php">Search</a></li>
                <li><a href="posts_view.php">Posts</a></li>
                <li><a href="publisher_comments_view.php">Comments</a></li>
                <li><a href="publisher_profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Welcome, <?php echo htmlspecialchars($userName); ?>!</h2>
        <div class="stats">
            <p>Total Posts: <?php echo $postsCount; ?></p>
            <p>Total Subscribers: <?php echo $subsCount; ?></p>
        </div>
    </main>
</body>
</html>
