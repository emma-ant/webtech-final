<?php
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'publisher') {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();
$publisherID = $_SESSION['publisher_id'];

// Fetch posts that HAVE comments (or just list all with count)
$sql = "SELECT p.postID, p.title, p.createdAt, COUNT(r.reviewID) as commentCount 
        FROM a_post p 
        JOIN a_reviews r ON p.postID = r.postID 
        WHERE p.publisherID = $publisherID 
        GROUP BY p.postID 
        HAVING commentCount > 0 
        ORDER BY p.createdAt DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Comments</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Comments on Posts</h1>
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
        <table>
            <tr>
                <th>Title</th>
                <th>Date</th>
                <th>Comment Count</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <!-- 
                -->
                <td><a href="view_post.php?id=<?php echo $row['postID']; ?>"><?php echo htmlspecialchars($row['title']); ?></a></td>
                <td><?php echo $row['createdAt']; ?></td>
                <td><?php echo $row['commentCount']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php $conn->close(); ?>
    </main>
</body>
</html>

