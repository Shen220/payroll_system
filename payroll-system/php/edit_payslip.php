<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

include 'db.php';

// Retrieve parameters from POST or GET
$employee_id = $_POST['employee_id'] ?? $_GET['employee_id'] ?? null;
$year        = $_POST['year'] ?? $_GET['year'] ?? null;
$week        = $_POST['week'] ?? $_GET['week'] ?? null;

// Validate required parameters
if (!$employee_id || !$year || !$week) {
    die("❌ Missing required parameters. Got: " 
        . "Employee ID = " . var_export($employee_id, true) . ", "
        . "Year = " . var_export($year, true) . ", "
        . "Week = " . var_export($week, true));
}

$message = "";

// Fetch employee info
$stmt = $conn->prepare("SELECT * FROM employee_info_and_rates WHERE employee_id=?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$employee) die("Employee not found.");

// Fetch payroll period by week
$stmt = $conn->prepare("SELECT payroll_id, week FROM payroll_dates WHERE week=?");
$stmt->bind_param("s", $week);
$stmt->execute();
$payroll_result = $stmt->get_result();
$stmt->close();
if (!$payroll_result->num_rows) die("Payroll week not found.");

// Labels for form inputs
$rate_labels = [
    'num_of_days_for_rate_1' => ['label' => 'Number of Days for Rate 1', 'key' => 'rate_1_daily_minimum_wage'],
    'num_of_days_for_rate_2' => ['label' => 'Number of Days for Rate 2', 'key' => 'rate_2_sunday_rest_day'],
    'num_of_days_for_rate_3' => ['label' => 'Number of Days for Rate 3', 'key' => 'rate_3_legal_holiday'],
    'num_of_days_for_rate_4' => ['label' => 'Number of Days for Rate 4', 'key' => 'rate_4_special_holiday'],
    'num_of_hours_for_rate_5' => ['label' => 'Number of Hours for Rate 5', 'key' => 'rate_5_regular_overtime_perhour'],
    'num_of_hours_for_rate_6' => ['label' => 'Number of Hours for Rate 6', 'key' => 'rate_6_special_overtime_perhour'],
    'num_of_hours_for_rate_7' => ['label' => 'Number of Hours for Rate 7', 'key' => 'rate_7_special_holiday_overtime_perhour'],
    'num_of_hours_for_rate_8' => ['label' => 'Number of Hours for Rate 8', 'key' => 'rate_8_regular_holiday_overtime_perhour'],
    'num_of_days_for_rate_9' => ['label' => 'Number of Days for Rate 9', 'key' => 'rate_9_cater']
];

// Fetch existing payroll data for this employee, year, and week
$payroll_data = [];
$stmt = $conn->prepare("
    SELECT pt.*, pd.week 
    FROM payroll_transactions pt
    JOIN payroll_dates pd ON pt.payroll_id = pd.payroll_id
    WHERE pt.employee_id=? AND pt.year=? AND pd.week=?
");
$stmt->bind_param("iis", $employee_id, $year, $week);
$stmt->execute();
$payroll_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$payroll_data) $message = "No existing payroll record found for this employee, year, and week.";

// === POST: Update the record ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payroll_id = $_POST['payroll_id'] ?? null;

    if (!$payroll_id) {
        die("Payroll ID is required for update.");
    }

    // Collect all rate fields
    $rates = array_keys($rate_labels);
    foreach ($rates as $r) {
        $$r = (int)($_POST[$r] ?? 0);
    }

    $cater_deductions = (float)($_POST['cater_deductions'] ?? 0);
    $advance_deductions = (float)($_POST['advance_deductions'] ?? 0);

    // Update the payroll record
    $stmt = $conn->prepare("
        UPDATE payroll_transactions SET 
            year=?, 
            num_of_days_for_rate_1=?, 
            num_of_days_for_rate_2=?, 
            num_of_days_for_rate_3=?, 
            num_of_days_for_rate_4=?, 
            num_of_hours_for_rate_5=?, 
            num_of_hours_for_rate_6=?, 
            num_of_hours_for_rate_7=?, 
            num_of_hours_for_rate_8=?, 
            num_of_days_for_rate_9=?, 
            cater_deductions=?, 
            advance_deductions=?
        WHERE employee_id=? AND payroll_id=?
    ");

    $stmt->bind_param(
        "iiiiiiiiiiiddi",
        $year,
        $num_of_days_for_rate_1,
        $num_of_days_for_rate_2,
        $num_of_days_for_rate_3,
        $num_of_days_for_rate_4,
        $num_of_hours_for_rate_5,
        $num_of_hours_for_rate_6,
        $num_of_hours_for_rate_7,
        $num_of_hours_for_rate_8,
        $num_of_days_for_rate_9,
        $cater_deductions,
        $advance_deductions,
        $employee_id,
        $payroll_id
    );

    $message = $stmt->execute() ? "Record updated successfully!" : "Error: ".$stmt->error;
    $stmt->close();

    // Refetch updated payroll data for form
    $stmt = $conn->prepare("
        SELECT pt.*, pd.week 
        FROM payroll_transactions pt
        JOIN payroll_dates pd ON pt.payroll_id = pd.payroll_id
        WHERE pt.employee_id=? AND pt.year=? AND pd.week=?
    ");
    $stmt->bind_param("iis", $employee_id, $year, $week);
    $stmt->execute();
    $payroll_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$conn->close();

// Return variables for form
return [
    'employee' => $employee,
    'payroll_result' => $payroll_result,
    'rate_labels' => $rate_labels,
    'payroll_data' => $payroll_data,
    'message' => $message
];
