<?php
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();

// Fetch users (exclude admin)
$sql = "SELECT userID, firstName, lastName, username, role FROM a_user WHERE role != 'admin'";
$result = $conn->query($sql);

// Delete user using userID from corresponding row
if (isset($_POST['delete_user_id'])) {
    $uid = $_POST['delete_user_id'];
    $conn->query("DELETE FROM a_user WHERE userID = $uid");
    // Refresh the page
    header("Location: admin_users.php");
    exit();
}
?>

<!-- HTML Structure -->
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Users</title>
    <link rel="icon" type="image/png" href="images/icon.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav class="navbar">
            <ul>
                <!-- "database: opens the phpmmyadmin" -->
                <li><a href="http://169.239.251.102:341/phpmyadmin/index.php?route=/database/structure&db=webtech_2025A_emmanuel_buasiako" target="_blank">Database</a></li>
                <li><a href="admin_users.php">Users</a></li>
                <li><a href="admin_posts.php">Posts</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Users</h2>
        <table>
            <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Type</th>
                <th>Action</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['firstName'] . ' ' . $row['lastName']); ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['role']); ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Delete this user?');">
                        <input type="hidden" name="delete_user_id" value="<?php echo $row['userID']; ?>">
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
