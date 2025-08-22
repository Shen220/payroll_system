<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

include 'db.php';

function formatCurrency($value) {
    return '₱' . number_format((float)$value, 2);
}

$payrollId = $_GET['payroll_id'] ?? null;
if (!$payrollId) {
    die("No ID provided.");
}

// Fetch employee info with payroll and computation
$query = "
SELECT 
  ei.*, ep.*, pc.*
FROM employee_info ei
LEFT JOIN employee_payroll ep ON ei.employee_id = ep.employee_id
LEFT JOIN payroll_computation pc ON ep.payroll_id = pc.payroll_id
WHERE ep.payroll_id = ?
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param('i', $payrollId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Payroll not found.");
}
$value = $result->fetch_assoc();

include '../html/payslip_html.php';
?>
