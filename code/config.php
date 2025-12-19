<?php
// Database configuration
define('hostname', 'localhost');
define('username', 'emmanuel.buasiako');      
define('password', 'your_new_password');
//define('username', 'root');      
//define('password', '');            
define('database', 'webtech_2025A_emmanuel_buasiako');

// Create connectkon function to be used else where
function getDBConnection() {
    $conn = new mysqli(hostname,username,password,database);
    //$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

session_start();
?>
