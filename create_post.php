<?php
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'publisher') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $publisherID = $_SESSION['publisher_id'];
    
    if (!empty($title) && !empty($content)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO a_post (publisherID, title, content, createdAt) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $publisherID, $title, $content);
        
        if ($stmt->execute()) {
            header("Location: posts_view.php");
            exit();
        } else {
            $error = "Error creating post.";
        }
        $conn->close();
    } else {
        $error = "Title and Content are required.";
    }
}
?>

<!-- HTML Structure -->
<!DOCTYPE html>
<html>
<head>
    <title>Create Post</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Create New Post</h1>
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
        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <label>Title:</label><br>
            <input type="text" name="title" required style="width: 50%;"><br><br>
            
            <label>Content:</label><br>
            <textarea name="content" rows="50" style="width: 100%;" required></textarea><br><br>
            
            <input type="submit" value="Publish" class="button">
        </form>
    </main>
</body>
</html>
