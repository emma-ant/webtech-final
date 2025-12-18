<?php
require_once "config.php";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Email and Password are required";
    } else {
        $conn = getDBConnection();
        $sql = "SELECT userID, firstName, lastName, role, passwordHash FROM a_user WHERE email = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // SALT CHECK
                $saltedPassword = "rose-pattern" . $password;
                
                if (password_verify($saltedPassword, $user['passwordHash'])) {
                    
                    $_SESSION['user_id'] = $user['userID'];
                    $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];
                    $_SESSION['role'] = $user['role'];
                    
                    if ($user['role'] === 'admin') {
                        header("Location: admin_home.php");
                    } elseif ($user['role'] === 'publisher') {
                        // Get publisherID
                        $pStmt = $conn->prepare("SELECT publisherID FROM a_publisher WHERE userID = ?");
                        $pStmt->bind_param("i", $user['userID']);
                        $pStmt->execute();
                        $pRes = $pStmt->get_result();
                        if ($pRes->num_rows > 0) {
                            $_SESSION['publisher_id'] = $pRes->fetch_assoc()['publisherID'];
                        }
                        
                        header("Location: publisher_home.php");
                    } else {
                        // Get subscriberID
                        $sStmt = $conn->prepare("SELECT subscriberID FROM a_subscriber WHERE userID = ?");
                        $sStmt->bind_param("i", $user['userID']);
                        $sStmt->execute();
                        $sRes = $sStmt->get_result();
                        if ($sRes->num_rows > 0) {
                            $_SESSION['subscriber_id'] = $sRes->fetch_assoc()['subscriberID'];
                        }
                        
                        header("Location: subscriber_home.php");
                    }
                    exit();
                } else {
                    $error = "Invalid password or email.";
                }
            } else {
                $error = "User not found.";
            }
            $stmt->close();
        } else {
            $error = "Database error.";
        }
        $conn->close();
    }
}

// Check if error passed via URL (from external redirect although simpler to keep internal now)
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}
?>

<!-- HTML Structure -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="icon" type="image/png" href="images/icon.png">
    <link rel="stylesheet" href="style.css">
</head>

<body class="bg-grad">
    <header>
        <h1>Log In</h1>
    </header>
    <main>
        <!-- Error/Success messages -->
        <?php if($error) echo "<p class='error'>$error</p>"; ?>
        <?php if(isset($_GET['msg']) && $_GET['msg']=='registered') echo "<p class='success'>Registration successful! Please login.</p>"; ?>
        
        <form action="login.php" method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

            <input type="submit" value="Log In" class="button">
        </form>

        <div style="margin-top: 20px;">
            <a href="signup.php">Sign Up</a> | 
            <a href="mailto:eantwibuasiako@gmail.com?subject=Sports Hub Enquiry - Log In">Need Help?</a> |
            <a href="index.php">Home</a>
        </div>
    </main>
    <footer>
    </footer>
</body>
</html>