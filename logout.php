<?php
//
session_start();
session_unset();
session_destroy();
// Return user to front page
header("Location: index.php");
exit();
?>