<?php
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'subscriber') {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();
$userID = $_SESSION['user_id'];
$subscriberID = $_SESSION['subscriber_id'];
$message = '';

// Handle Delete Account
if (isset($_POST['delete_account'])) {
    // Delete from a_user (Cascade should handle the rest: a_subscriber, a_reviews, a_read? (Actually a_read might need manual check if no cascade, but I set cascade on userID/subscriberID))
    // Yes, sql.sql updates had ON DELETE CASCADE.
    $conn->query("DELETE FROM a_user WHERE userID = $userID");
    session_destroy();
    header("Location: login.php?msg=deleted");
    exit();
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_account'])) {
    $firstName = trim($_POST['firstname']);
    $lastName = trim($_POST['lastname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $birthDate = $_POST['birthdate'];
    
    // Update a_user
    $stmt = $conn->prepare("UPDATE a_user SET firstName=?, lastName=?, username=?, email=?, birthDate=? WHERE userID=?");
    $stmt->bind_param("sssssi", $firstName, $lastName, $username, $email, $birthDate, $userID);
    if ($stmt->execute()) {
        $message = "Profile updated successfully.";
        // Update session name if changed
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        
        // Update a_subscriber subscriberName too?
        $subName = $firstName . ' ' . $lastName;
        $conn->query("UPDATE a_subscriber SET subscriberName = '$subName' WHERE userID = $userID");
    } else {
        $message = "Error updating profile.";
    }
    $stmt->close();
}

// Fetch current info
$stmt = $conn->prepare("SELECT firstName, lastName, username, email, birthDate FROM a_user WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete your account? This cannot be undone.");
        }
    </script>
</head>
<body>
    <header>
        <h1>Profile</h1>
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
        <?php if($message) echo "<p class='success'>$message</p>"; ?>
        <form method="POST">
            <label>First Name:</label>
            <input type="text" name="firstname" value="<?php echo htmlspecialchars($user['firstName']); ?>" required><br>
            
            <label>Last Name:</label>
            <input type="text" name="lastname" value="<?php echo htmlspecialchars($user['lastName']); ?>" required><br>
            
            <label>Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br>
            
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>
            
            <label>Date of Birth:</label>
            <input type="date" name="birthdate" value="<?php echo $user['birthDate']; ?>" required><br>
            
            <input type="submit" value="Update Profile" class="button">
        </form>
        
        <br><hr><br>
        
        <form method="POST" onsubmit="return confirmDelete();">
            <input type="hidden" name="delete_account" value="1">
            <input type="submit" value="Delete Account" class="button" style="background-color: #ffcccc; color: red;">
        </form>
    </main>
</body>
</html>
