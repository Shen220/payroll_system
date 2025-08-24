<?php
session_start();

// Check authentication
if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

require 'db.php'; // Database connection

// Validate employee_id
if (empty($_GET['employee_id']) || !ctype_digit($_GET['employee_id'])) {
    die("Invalid or missing employee ID.");
}

$employee_id = (int) $_GET['employee_id'];

// Prepare and execute delete query
$stmt = $conn->prepare("DELETE FROM employee_info_and_rates WHERE employee_id = ?");
if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $employee_id);
if ($stmt->execute()) {
    header("Location: dashboard.php?deleted=1");
    exit();
} else {
    die("Error deleting employee: " . $stmt->error);
}

$stmt->close();
$conn->close();
