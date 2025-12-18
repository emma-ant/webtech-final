<?php
require_once "config.php"; //because obviouly

// Check session ID
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();

// Fetch posts
$sql = "SELECT p.postID, p.title, pub.publisherName, pub.publisherID 
        FROM a_post p 
        JOIN a_publisher pub ON p.publisherID = pub.publisherID 
        ORDER BY p.createdAt DESC";
$result = $conn->query($sql);

// Delete post using postID from corresponding row
if (isset($_POST['delete_post_id'])) {
    $pid = $_POST['delete_post_id'];
    $conn->query("DELETE FROM a_post WHERE postID = $pid");
    header("Location: admin_posts.php");
    exit();
}
?>

<!-- HTML Structure -->
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Posts</title>
    <link rel="icon" type="image/png" href="images/icon.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav class="navbar">
            <ul>
                <li><a href="http://169.239.251.102:341/phpmyadmin/index.php?route=/database/structure&db=webtech_2025A_emmanuel_buasiako" target="_blank">Database</a></li>
                <li><a href="admin_users.php">Users</a></li>
                <li><a href="admin_posts.php">Posts</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Posts</h2>
        <table>
            <tr>
                <th>Title</th>
                <th>Publisher</th>
                <th>Action</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><a href="view_adminpost.php?id=<?php echo $row['postID']; ?>"><?php echo htmlspecialchars($row['title']); ?></a></td>
                <td><a href="view_publisher.php?id=<?php echo $row['publisherID']; ?>"><?php echo htmlspecialchars($row['publisherName']); ?></a></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Delete this post?');">
                        <input type="hidden" name="delete_post_id" value="<?php echo $row['postID']; ?>">
                        <input type="submit" value="Delete" class="button" style="background-color: #febabaff; color: red;">
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php $conn->close(); ?>
    </main>
</body>
</html>
