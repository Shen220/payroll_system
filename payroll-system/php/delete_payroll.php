<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

include 'db.php';

$employee_id = $_GET['employee_id'] ?? null;

if (!$employee_id) {
    die("No employee ID specified.");
}

//Delete from payroll_computation table
$sql1 = "DELETE FROM payroll_computation WHERE employee_id = ?";
$stmt1 = $conn->prepare($sql1);
if (!$stmt1) {
    die("Prepare failed for payroll_computation: " . $conn->error);
}
$stmt1->bind_param('i', $employee_id);
if (!$stmt1->execute()) {
    die("Error deleting from payroll_computation: " . $stmt1->error);
}

//Delete from employee_payroll table
$sql2 = "DELETE FROM employee_payroll WHERE employee_id = ?";
$stmt2 = $conn->prepare($sql2);
if (!$stmt2) {
    die("Prepare failed for employee_payroll: " . $conn->error);
}
$stmt2->bind_param('i', $employee_id);
if (!$stmt2->execute()) {
    die("Error deleting from employee_payroll: " . $stmt2->error);
}

header("Location: dashboard.php");
exit();
?>
