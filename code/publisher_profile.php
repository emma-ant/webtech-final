<?php
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'publisher') {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();
$userID = $_SESSION['user_id'];
$publisherID = $_SESSION['publisher_id'];
$message = '';

// Handle Delete Account
if (isset($_POST['delete_account'])) {
    // Delete from a_user (Cascade handles a_publisher, a_post, a_subscription, a_reviews?)
    // a_post has CASCADE on publisherID. a_publisher has CASCADE on userID.
    // So deleting user should cascade everything.
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
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        
        // Update a_publisher publisherName
        $pubName = $firstName . ' ' . $lastName;
        $conn->query("UPDATE a_publisher SET publisherName = '$pubName' WHERE userID = $userID");
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
    <title>Publisher Profile</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete your account? This will delete all your posts and can not be undone.");
        }
    </script>
</head>
<body>
    <header>
        <h1>Profile</h1>
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
