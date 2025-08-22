<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

include 'db.php';

// 🔹 For Employee List tab
$query = "SELECT employee_id, last_name, first_name, position, status, board_lodging, lodging_address, food_allowance FROM employee_info_and_rates";
$result = $conn->query($query);
if (!$result) {
    die("Error retrieving employee info: " . $conn->error);
}

// 🔹 For Payroll List tab
$query1 = "
SELECT 
  ei.employee_id,
  ei.last_name,
  ei.first_name,
  ei.status,
  pd.payroll_id,
  pd.week
FROM employee_info_and_rates ei
LEFT JOIN payroll_dates pd ON ei.employee_id = ei.employee_id
";
$result1 = $conn->query($query1);
if (!$result1) {
    die("Error retrieving payroll info: " . $conn->error);
}

include '../html/dashboard_html.php';
?>
