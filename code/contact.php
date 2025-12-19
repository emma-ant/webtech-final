<?php
require_once "config.php";

$message = '';
// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $msg = trim($_POST['message'] ?? '');
    
    // Check if all fields are filled
    if (!empty($name) && !empty($email) && !empty($msg)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO a_contact (contactName, contactEmail, contactMessage) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $msg);
        
        if ($stmt->execute()) {
            $message = "Thank you! Your message has been sent.";
        } else {
            $message = "Error sending message: " . $conn->error;
        }
        $stmt->close();
        $conn->close();
    } else {
        $message = "All fields are required.";
    }
}
?>

<!-- HTML Structure -->
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Contact Us</title>
        <link rel="icon" type="image/png" href="images/icon.png">
        <link rel="stylesheet" href="style.css">
    </head>
    <body class="bg-grad">
        <header><h1>Contact Us</h1></header>
        <nav class="navbar">
                <ul>
                    <li><a href="index.php">Home</a></li>
                </ul>
            </nav>
        <main>
            <p>Thank you for your interest in our services. Please fill out the form below to get in touch with us.</p>
            <?php if($message) echo "<p>" . htmlspecialchars($message) . "</p>"; ?>
            <form action="contact.php" method="POST">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required><br>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required><br>

                <label for="message">Message:</label>
                <textarea id="message" name="message" required></textarea><br>

                <input type="submit" value="Send" class="button">
            </form>
        </main>
    </body>
</html>