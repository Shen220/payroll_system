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

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['payroll_id'])) {
    $payroll_id = $_POST['payroll_id'];

    // Update employee_payroll (hours/days)
    $empPayrollFields = [
        'w1_daily_minimum_wage', 'w2_sunday_rest_day', 'w3_legal_holiday',
        'w4_special_holiday', 'w5_regular_overtime_perhour',
        'w6_special_overtime_perhour', 'w7_special_holiday_overtime_perhour',
        'w8_regular_holiday_overtime_perhour', 'w9_cater'
    ];

    $empPlaceholders = implode(", ", array_map(fn($f) => "$f = ?", $empPayrollFields));
    $sql1 = "UPDATE employee_payroll SET $empPlaceholders WHERE payroll_id = ?";
    $stmt1 = $conn->prepare($sql1);
    if (!$stmt1) die("Prepare failed (employee_payroll): " . $conn->error);

    $empValues = array_map(fn($f) => $_POST[$f] ?? 0, $empPayrollFields);
    $empValues[] = $payroll_id;
    $empTypes = str_repeat('d', count($empPayrollFields)) . 'i';
    $stmt1->bind_param($empTypes, ...$empValues);
    $stmt1->execute();

    // Calculate totals (w1–w9) using rate * hours
    $totalFields = [];
    for ($i = 1; $i <= 9; $i++) {
        $hourField = match($i) {
            1 => 'w1_daily_minimum_wage',
            2 => 'w2_sunday_rest_day',
            3 => 'w3_legal_holiday',
            4 => 'w4_special_holiday',
            5 => 'w5_regular_overtime_perhour',
            6 => 'w6_special_overtime_perhour',
            7 => 'w7_special_holiday_overtime_perhour',
            8 => 'w8_regular_holiday_overtime_perhour',
            9 => 'w9_cater',
        };
        $rateField = "w{$i}_rate";
        $hours = (float)($_POST[$hourField] ?? 0);
        $rate = (float)($_POST[$rateField] ?? 0);
        $totalFields["w$i"] = $hours * $rate;
    }

    $totalFields['cater1'] = (float)($_POST['cater1'] ?? 0);
    $totalFields['advance'] = (float)($_POST['advance'] ?? 0);

    $fields = array_keys($totalFields);
    $placeholders = implode(", ", array_map(fn($f) => "$f = ?", $fields));
    $sql2 = "UPDATE payroll_computation SET $placeholders WHERE payroll_id = ?";
    $stmt2 = $conn->prepare($sql2);
    if (!$stmt2) die("Prepare failed (payroll_computation): " . $conn->error);

    $values = array_values($totalFields);
    $values[] = $payroll_id;
    $types = str_repeat('d', count($totalFields)) . 'i';
    $stmt2->bind_param($types, ...$values);

    if ($stmt2->execute()) {
        $successMessage = "Payslip updated successfully.";
    } else {
        $errorMessage = "Update failed: " . $stmt2->error;
    }
}

// Fetch data
$payrollId = $_GET['payroll_id'] ?? ($_POST['payroll_id'] ?? null);
if (!$payrollId) die("No payroll ID provided.");

$query = "
SELECT ei.*, ep.*, pc.* 
FROM employee_info ei
LEFT JOIN employee_payroll ep ON ei.employee_id = ep.employee_id
LEFT JOIN payroll_computation pc ON ep.payroll_id = pc.payroll_id
WHERE ep.payroll_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $payrollId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) die("Payroll not found.");
$value = $result->fetch_assoc();

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    include '../html/edit_payslip_html.php';
    exit();
} else {
    header("Location: dashboard.php"); // redirect after update
    exit();
}

$stmt->close();
?>

