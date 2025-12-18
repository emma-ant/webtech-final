<?php
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'subscriber') {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();
$results = [];
$searchType = $_GET['type'] ?? 'none';
$searchTerm = $_GET['search'] ?? '';

if ($searchType !== 'none' && !empty($searchTerm)) {
    $searchTermLike = "%$searchTerm%";
    if ($searchType === 'publisher') {
        $stmt = $conn->prepare("SELECT publisherID, publisherName FROM a_publisher WHERE publisherName LIKE ?");
        $stmt->bind_param("s", $searchTermLike);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $results[] = ['type' => 'publisher', 'id' => $row['publisherID'], 'name' => $row['publisherName']];
        }
        $stmt->close();
    } elseif ($searchType === 'post') {
        $stmt = $conn->prepare("SELECT postID, title FROM a_post WHERE title LIKE ?");
        $stmt->bind_param("s", $searchTermLike);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $results[] = ['type' => 'post', 'id' => $row['postID'], 'name' => $row['title']];
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Search</h1>
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
        <form action="search.php" method="GET">
            <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            <select name="type">
                <option value="none" <?php if($searchType=='none') echo 'selected'; ?>>None</option>
                <option value="publisher" <?php if($searchType=='publisher') echo 'selected'; ?>>Publisher</option>
                <option value="post" <?php if($searchType=='post') echo 'selected'; ?>>Post</option>
            </select>
            <input type="submit" value="Search" class="button">
        </form>

        <?php if (!empty($results)): ?>
            <table>
                <tr>
                    <th>Result</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($results as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>
                        <?php if ($item['type'] === 'publisher'): ?>
                            <a href="view_publisher.php?id=<?php echo $item['id']; ?>">View Publisher</a>
                        <?php else: ?>
                            <a href="view_post.php?id=<?php echo $item['id']; ?>">View Post</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php elseif ($searchType !== 'none'): ?>
            <p>No results found.</p>
        <?php endif; ?>
    </main>
</body>
</html>
