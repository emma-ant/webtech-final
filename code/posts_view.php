<?php
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'publisher') {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();
$publisherID = $_SESSION['publisher_id'];

// Handle Delete
if (isset($_POST['delete_id'])) {
    $postID = $_POST['delete_id'];
    // Validating ownership
    $check = $conn->query("SELECT postID FROM a_post WHERE postID = $postID AND publisherID = $publisherID");
    if ($check->num_rows > 0) {
        $conn->query("DELETE FROM a_post WHERE postID = $postID");
    }
}

// Fetch Posts
$sql = "SELECT p.postID, p.title, p.createdAt, 
               (SELECT COUNT(*) FROM a_read r WHERE r.postID = p.postID) as readCount 
        FROM a_post p 
        WHERE p.publisherID = $publisherID 
        ORDER BY p.createdAt DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Posts</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>My Posts</h1>
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
        <div style="margin-bottom: 20px;">
            <a href="create_post.php" class="button" style="text-decoration:none;">New Post</a>
        </div>
        
        <table>
            <tr>
                <th>Title</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><a href="view_post.php?id=<?php echo $row['postID']; ?>"><?php echo htmlspecialchars($row['title']); ?></a></td>
                <td><?php echo $row['createdAt']; ?></td>
                <td>
                    <!-- Mae sure to put validation for delete post -->
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this post?');">
                        <input type="hidden" name="delete_id" value="<?php echo $row['postID']; ?>">
                        <input type="submit" value="Delete" class="button" style="background-color: #ffcccc; color: red;">
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <!-- Close connection -->
        <?php $conn->close(); ?>
    </main>
</body>
</html>
