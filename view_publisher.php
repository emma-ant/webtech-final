<?php
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$publisherID = $_GET['id'] ?? 0;
$conn = getDBConnection();
$userID = $_SESSION['user_id'];
$subscriberID = $_SESSION['subscriber_id'] ?? 0;

// Fetch publisher info
$stmt = $conn->prepare("SELECT p.publisherName, u.firstName, u.lastName, p.userID 
                        FROM a_publisher p 
                        JOIN a_user u ON p.userID = u.userID 
                        WHERE p.publisherID = ?");
$stmt->bind_param("i", $publisherID);
$stmt->execute();
$pubResult = $stmt->get_result();
if ($pubResult->num_rows === 0) die("Publisher not found");
$publisher = $pubResult->fetch_assoc();
$stmt->close();

// Check Subscription Status
$subStatus = 'Inactive';
$stmt = $conn->prepare("SELECT status FROM a_subscription WHERE userID = ? AND publisherID = ?");
$stmt->bind_param("ii", $userID, $publisherID);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $subStatus = $res->fetch_assoc()['status'];
}
$stmt->close();

// Handle Subscribe/Unsubscribe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    if ($action === 'subscribe') {
        if ($res->num_rows > 0) {
            $conn->query("UPDATE a_subscription SET status = 'Active' WHERE userID = $userID AND publisherID = $publisherID");
        } else {
            $conn->query("INSERT INTO a_subscription (userID, publisherID, startDate, status) VALUES ($userID, $publisherID, NOW(), 'Active')");
        }
        $subStatus = 'Active';
    } elseif ($action === 'unsubscribe') {
        $conn->query("UPDATE a_subscription SET status = 'Inactive' WHERE userID = $userID AND publisherID = $publisherID");
        $subStatus = 'Inactive';
    }
}

// Stats
// Subscribers count
$subCountRes = $conn->query("SELECT COUNT(*) as count FROM a_subscription WHERE publisherID = $publisherID AND status = 'Active'");
$subCount = $subCountRes->fetch_assoc()['count'];

// Total Posts
$postCountRes = $conn->query("SELECT COUNT(*) as count FROM a_post WHERE publisherID = $publisherID");
$postCount = $postCountRes->fetch_assoc()['count'];

// Total Reads (Sum of reads for all posts by this publisher)
$readsCountRes = $conn->query("SELECT COUNT(*) as count FROM a_read r JOIN a_post p ON r.postID = p.postID WHERE p.publisherID = $publisherID");
$readsCount = $readsCountRes->fetch_assoc()['count'];

// Fetch Posts
$postsStmt = $conn->prepare("SELECT postID, title, createdAt FROM a_post WHERE publisherID = ? ORDER BY createdAt DESC");
$postsStmt->bind_param("i", $publisherID);
$postsStmt->execute();
$posts = $postsStmt->get_result();

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($publisher['publisherName']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><?php echo htmlspecialchars($publisher['firstName'] . ' ' . $publisher['lastName']); ?></h1>
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
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Publisher Details</h2>
            <form method="POST">
                <?php if ($subStatus === 'Active'): ?>
                    <input type="hidden" name="action" value="unsubscribe">
                    <input type="submit" value="Unsubscribe" class="button">
                <?php else: ?>
                    <input type="hidden" name="action" value="subscribe">
                    <input type="submit" value="Subscribe" class="button">
                <?php endif; ?>
            </form>
        </div>
        
        <div class="stats" style="display: flex; justify-content: space-between;">
            <p>Subscribers: <?php echo $subCount; ?></p>
            <p>Total Reads: <?php echo $readsCount; ?></p>
            <p>Total Posts: <?php echo $postCount; ?></p>
        </div>

        <h3>Posts</h3>
        <table>
            <tr>
                <th>Post Title</th>
                <th>Date</th>
            </tr>
            <?php while($row = $posts->fetch_assoc()): ?>
            <tr>
                <td>
                    <!-- Link logic: if subscribed, logic is handled in view_post.php which redirects, but here we can just link to view_post.php and let it handle -->
                    <a href="view_post.php?id=<?php echo $row['postID']; ?>"><?php echo htmlspecialchars($row['title']); ?></a>
                </td>
                <td><?php echo $row['createdAt']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </main>
</body>
</html>
