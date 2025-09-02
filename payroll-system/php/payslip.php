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

$message = "";

// Get required params
$employee_id = $_POST['employee_id'] ?? $_GET['employee_id'] ?? null;
$year        = $_POST['year'] ?? $_GET['year'] ?? null;
$week        = $_POST['week'] ?? $_GET['week'] ?? null;

if (!$employee_id || !$year || !$week) {
    die("❌ Missing required parameters. Got: " 
        . "Employee ID = " . var_export($employee_id, true) . ", "
        . "Year = " . var_export($year, true) . ", "
        . "Week = " . var_export($week, true));
}

// Fetch employee info (rates are here)
$stmt = $conn->prepare("SELECT * FROM employee_info_and_rates WHERE employee_id=?");
$stmt->bind_param("i",$employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
$stmt->close();
if(!$employee) die("Employee not found.");

// Fetch payroll transaction for this employee, year, and week
$stmt1 = $conn->prepare("
    SELECT pt.*, pd.week
    FROM payroll_transactions pt
    LEFT JOIN payroll_dates pd ON pt.payroll_id = pd.payroll_id
    WHERE pt.employee_id = ? AND pt.year = ? AND pd.week = ?
    LIMIT 1
");
$stmt1->bind_param("iss", $employee_id, $year, $week);
$stmt1->execute();
$payrollInfo = $stmt1->get_result()->fetch_assoc();
$stmt1->close();

if (!$payrollInfo) {
    die("❌ No payroll transaction found for Employee {$employee_id}, Year {$year}, Week {$week}");
}

// Categories mapping (label, hours column in payroll_transactions, rate column in employee_info_and_rates)
$categories = [
    ['label' => 'Daily Minimum Wage',          'hours' => 'num_of_days_for_rate_1', 'rate' => 'rate_1_daily_minimum_wage'],
    ['label' => 'Sunday Rest Day',             'hours' => 'num_of_days_for_rate_2', 'rate' => 'rate_2_sunday_rest_day'],
    ['label' => 'Legal Holiday',               'hours' => 'num_of_days_for_rate_3', 'rate' => 'rate_3_legal_holiday'],
    ['label' => 'Special Holiday',             'hours' => 'num_of_days_for_rate_4', 'rate' => 'rate_4_special_holiday'],
    ['label' => 'Regular Overtime per Hour',   'hours' => 'num_of_hours_for_rate_5','rate' => 'rate_5_regular_overtime_perhour'],
    ['label' => 'Special Overtime per Hour',   'hours' => 'num_of_hours_for_rate_6','rate' => 'rate_6_special_overtime_perhour'],
    ['label' => 'Special Holiday OT per Hour', 'hours' => 'num_of_hours_for_rate_7','rate' => 'rate_7_special_holiday_overtime_perhour'],
    ['label' => 'Regular Holiday OT per Hour', 'hours' => 'num_of_hours_for_rate_8','rate' => 'rate_8_regular_holiday_overtime_perhour'],
    ['label' => 'Cater',                       'hours' => 'num_of_days_for_rate_9', 'rate' => 'rate_9_cater'],
];

$conn->close();

// Export variables for HTML display
return [
    'employee'     => $employee,
    'payrollInfo'  => $payrollInfo,
    'categories'   => $categories,
    'message'      => $message
];
?>
