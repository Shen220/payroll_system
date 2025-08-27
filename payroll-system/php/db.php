<?php
$host = "localhost";
$user = "root";         // default username for XAMPP
$pass = "";             // default password is empty
$db   = "payroll_db";   // name of your database

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>


