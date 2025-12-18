<?php
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'subscriber') {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();
$userID = $_SESSION['user_id'];

// Handle Unsubscribe from this page
if (isset($_POST['unsubscribe_id'])) {
    $pubID = $_POST['unsubscribe_id'];
    $conn->query("UPDATE a_subscription SET status = 'Inactive' WHERE userID = $userID AND publisherID = $pubID");
}

$sql = "SELECT p.publisherID, u.firstName, u.lastName 
        FROM a_subscription s 
        JOIN a_publisher p ON s.publisherID = p.publisherID 
        JOIN a_user u ON p.userID = u.userID 
        WHERE s.userID = $userID AND s.status = 'Active'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Subscriptions</title>
    <link rel="icon" type="image/png" href="images/icon.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Subscriptions</h1>
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
        <table>
            <tr>
                <th>Name</th>
                <th>Action</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['lastName']); ?></td>
                <td>
                    <a href="view_publisher.php?id=<?php echo $row['publisherID']; ?>" class="button">View</a>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="unsubscribe_id" value="<?php echo $row['publisherID']; ?>">
                        <input type="submit" value="Unsubscribe" class="button">
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php $conn->close(); ?>
    </main>
</body>
</html>
