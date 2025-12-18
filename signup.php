<?php
require_once "config.php";

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['firstname'] ?? '');
    $lastName = trim($_POST['lastname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    // confirm password check logic could be added here, currently relying on HTML5 pattern match or simple check
    $birthDate = $_POST['birthdate'] ?? '';
    $role = $_POST['role'] ?? 'subscriber'; 
    $profilePic = $_FILES['profilepic'] ?? null; // not implemented logic yet, but keeping placeholder

    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $conn = getDBConnection();
        
        // Check if email or username exists
        $stmt = $conn->prepare("SELECT userID FROM a_user WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        if ($stmt->num_rows > 0) {
            $error = "Email or Username already exists.";
        } else {
            $stmt->close();
            
            // SALT AND HASH PASSWORD
            $saltedPassword = "rose-pattern" . $password;
            $passwordHash = password_hash($saltedPassword, PASSWORD_DEFAULT);

            // Insert into a_user
            $stmt = $conn->prepare("INSERT INTO a_user (firstName, lastName, username, email, passwordHash, birthDate, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $firstName, $lastName, $username, $email, $passwordHash, $birthDate, $role);
            
            if ($stmt->execute()) {
                $userID = $stmt->insert_id;
                $stmt->close();
                
                // Insert into role specific table
                if ($role === 'publisher') {
                    $publisherName = $firstName . ' ' . $lastName;
                    $stmt = $conn->prepare("INSERT INTO a_publisher (userID, publisherName) VALUES (?, ?)");
                    $stmt->bind_param("is", $userID, $publisherName);
                    $stmt->execute();
                } elseif ($role === 'subscriber') {
                    $subscriberName = $firstName . ' ' . $lastName;
                    $stmt = $conn->prepare("INSERT INTO a_subscriber (userID, subscriberName) VALUES (?, ?)");
                    $stmt->bind_param("is", $userID, $subscriberName);
                    $stmt->execute();
                }
                
                header("Location: login.php?msg=registered");
                exit();
            } else {
                $error = "Error registering user: " . $conn->error;
            }
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sign Up</title>
    <link rel="icon" type="image/png" href="images/icon.png">
    <link rel="stylesheet" href="style.css">
</head>

<body class="bg-grad">
    <header>
        <h1>Sign Up</h1>
    </header>
    <main>
        <?php if($error) echo "<p class='error'>$error</p>"; ?>
        <!-- This is a comment-->
        <form action="signup.php" method="POST" enctype="multipart/form-data">
            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" minlength="3" maxlength="20" required
                placeholder="John"><br>

            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" minlength="3" maxlength="20" required
                placeholder="Doe"><br>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" minlength="5" maxlength="20" required
                placeholder="jdo123"><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" minlength="5" required placeholder="j.doe@gmail.com"><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" minlength="8" maxlength="20"
                pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$" required placeholder="Password123"><br>

            <label for="checkpassword">Confirm Password:</label>
            <input type="password" id="checkpassword" name="checkpassword" minlength="8" maxlength="20"
                pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$" required placeholder="Password123"><br>

            <label for="birthdate">Date of Birth:</label>
            <input type="date" id="birthdate" name="birthdate" required><br>

            <label for="role">User Type: </label>
            <select id="role" name="role">
                <option value="subscriber">Subscriber</option>
                <option value="publisher">Publisher</option>
                <option value="admin">Admin</option>
            </select><br>

            <label for="profilepic">Profile Picture:</label>
            <input type="file" id="profilepic" name="profilepic" accept="image/png, image/jpeg"><br>


            <input type="reset" class="button">

            <input type="submit" class="button">
        </form>
        <!-- href links to websites and pages in your server-->
        <!-- target(where the link should open): blank opens in a new tab or a new window
         title(gives a tooltip): 
         -->
        <div style="margin-top: 20px;">
            <a href="login.php">Log In</a> |
            <a href="mailto:eantwibuasiako@gmail.com?subject=Sports Hub Enquiry - Sign Up">Need Help?</a> |
            <a href="index.php">Home</a>
        </div>
    </main>
    <footer>

    </footer>
</body>

</html>
