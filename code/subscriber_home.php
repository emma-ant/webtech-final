<?php
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'subscriber') {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();
$subscriberID = $_SESSION['subscriber_id'];
$userName = $_SESSION['user_name'];

// Stats
// Number of posts read
$readsResult = $conn->query("SELECT COUNT(*) as count FROM a_read WHERE subscriberID = $subscriberID");
$readsCount = $readsResult->fetch_assoc()['count'];

// Comments left
$commentsResult = $conn->query("SELECT COUNT(*) as count FROM a_reviews WHERE subscriberID = $subscriberID");
$commentsCount = $commentsResult->fetch_assoc()['count'];

// Publishers subscribed to
$subsResult = $conn->query("SELECT COUNT(*) as count FROM a_subscription WHERE userID = {$_SESSION['user_id']} AND status = 'Active'");
$subsCount = $subsResult->fetch_assoc()['count'];

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Subscriber Home</title>
    <link rel="icon" type="image/png" href="images/icon.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Subscriber Dashboard</h1>
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
        <h2>Welcome, <?php echo htmlspecialchars($userName); ?>!</h2>
        <div class="stats">
            <p>Posts Read: <?php echo $readsCount; ?></p>
            <p>Comments Left: <?php echo $commentsCount; ?></p>
            <p>Subscriptions: <?php echo $subsCount; ?></p>
        </div>
    </main>
</body>
</html>
