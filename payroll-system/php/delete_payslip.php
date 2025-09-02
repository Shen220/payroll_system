<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

include 'db.php';

// Retrieve parameters from GET
$employee_id = $_GET['employee_id'] ?? null;
$year        = $_GET['year'] ?? null;
$week        = $_GET['week'] ?? null;

if (!$employee_id || !$year || !$week) {
    die("Missing required parameters. Got: "
        . "Employee ID = " . var_export($employee_id, true) . ", "
        . "Year = " . var_export($year, true) . ", "
        . "Week = " . var_export($week, true));
}

// Delete the specific payroll record
$sql = "
    DELETE pt 
    FROM payroll_transactions pt
    JOIN payroll_dates pd ON pt.payroll_id = pd.payroll_id
    WHERE pt.employee_id = ? AND pt.year = ? AND pd.week = ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param('iis', $employee_id, $year, $week);

if (!$stmt->execute()) {
    die("Error deleting payroll record: " . $stmt->error);
}

$stmt->close();
$conn->close();

// Redirect back to dashboard
header("Location: dashboard.php");
exit();
