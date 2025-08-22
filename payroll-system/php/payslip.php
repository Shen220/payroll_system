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
if (!$payrollId || !ctype_digit($payrollId)) {
    die("No valid payroll ID provided.");
}

// Fetch employee info and payroll transactions
$query = "
SELECT 
    eir.*, 
    pt.*
FROM employee_info_and_rates eir
INNER JOIN payroll_transactions pt 
    ON eir.employee_id = pt.employee_id
WHERE pt.payroll_id = ?
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
