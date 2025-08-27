<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

include 'db.php';

$message = "";
$payroll_data = []; // Initialize to prevent errors if no data is found
$employee = []; // Initialize to prevent errors

// --- Determine if the request is a POST (form submission) or GET (page load) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- POST: Handle form submission and update database ---

    // Retrieve parameters from the form
    $employee_id = $_POST['employee_id'] ?? null;
    $year        = $_POST['year'] ?? null;
    $week        = $_POST['week'] ?? null;

    

    // Validate essential parameters
    if (!$employee_id || !$year || !$week) {
        die("❌ Missing required parameters. Please go back and select all fields.");
    }
    
    // Convert 'week' to 'payroll_id' by looking it up in the database
    $payroll_id = null;
    $stmt_pid = $conn->prepare("SELECT payroll_id FROM payroll_dates WHERE week = ?");
    $stmt_pid->bind_param("s", $week);
    $stmt_pid->execute();
    $result_pid = $stmt_pid->get_result()->fetch_assoc();
    if ($result_pid) {
        $payroll_id = $result_pid['payroll_id'];
    }
    $stmt_pid->close();

    if (!$payroll_id) {
        die("Error: Could not find Payroll ID for the selected week.");
    }

    // Define rate labels (needed here for key collection)
    $rate_labels = [
        'num_of_days_for_rate_1' => ['label' => 'Daily Minimum Wage', 'key' => 'rate_1_daily_minimum_wage'],
        'num_of_days_for_rate_2' => ['label' => 'Sunday Rest Day', 'key' => 'rate_2_sunday_rest_day'],
        'num_of_days_for_rate_3' => ['label' => 'Legal Holiday', 'key' => 'rate_3_legal_holiday'],
        'num_of_days_for_rate_4' => ['label' => 'Special Holiday', 'key' => 'rate_4_special_holiday'],
        'num_of_hours_for_rate_5' => ['label' => 'Regular Overtime per Hour', 'key' => 'rate_5_regular_overtime_perhour'],
        'num_of_hours_for_rate_6' => ['label' => 'Special Overtime per Hour', 'key' => 'rate_6_special_overtime_perhour'],
        'num_of_hours_for_rate_7' => ['label' => 'Special Holiday OT per Hour', 'key' => 'rate_7_special_holiday_overtime_perhour'],
        'num_of_hours_for_rate_8' => ['label' => 'Regular Holiday OT per Hour', 'key' => 'rate_8_regular_holiday_overtime_perhour'],
        'num_of_days_for_rate_9' => ['label' => 'Cater', 'key' => 'rate_9_cater']
    ];

    // Collect all rate fields from POST data
    $rates = array_keys($rate_labels);
    foreach ($rates as $r) {
        $$r = (int)($_POST[$r] ?? 0);
    }
    $cater_deductions = (float)($_POST['cater_deductions'] ?? 0);
    $advance_deductions = (float)($_POST['advance_deductions'] ?? 0);
$payroll_id = $_POST['payroll_id'] ?? null;

$stmt = $conn->prepare("UPDATE payslips SET payroll_id=? WHERE payslip_id=?");
$stmt->bind_param("ii", $payroll_id, $payslip_id);
$stmt->execute();
    // Update the payroll record using the determined payroll_id
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

    $message = $stmt->execute() ? "Record updated successfully!" : "Error: " . $stmt->error;
    $stmt->close();

} else {
    // --- GET: Handle initial page load ---

    // Retrieve parameters from the URL
    $employee_id = $_GET['employee_id'] ?? null;
    $year        = $_GET['year'] ?? null;
    $week        = $_GET['week'] ?? null;

    // Validate that the required URL parameters are present for the initial load
    if (!$employee_id || !$year || !$week) {
        die("❌ Missing required parameters. Got: "
            . "Employee ID = " . var_export($employee_id, true) . ", "
            . "Year = " . var_export($year, true) . ", "
            . "Week = " . var_export($week, true));
    }
}

// --- Common code for both GET and POST requests ---

// Fetch employee information
$stmt = $conn->prepare("SELECT * FROM employee_info_and_rates WHERE employee_id=?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$employee) die("Employee not found.");

// Fetch all payroll periods for the dropdown menu
$payroll_result1 = $conn->query("SELECT payroll_id, week FROM payroll_dates ORDER BY payroll_id ASC");

// Fetch the current payroll record for this employee, year, and week to populate the form
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

if (!$payroll_data) {
    $message = "No existing payroll record found for this employee, year, and week.";
}

// Labels for form inputs (needed for both GET and POST to render the form)
$rate_labels = [
    'num_of_days_for_rate_1' => ['label' => 'Daily Minimum Wage', 'key' => 'rate_1_daily_minimum_wage'],
    'num_of_days_for_rate_2' => ['label' => 'Sunday Rest Day', 'key' => 'rate_2_sunday_rest_day'],
    'num_of_days_for_rate_3' => ['label' => 'Legal Holiday', 'key' => 'rate_3_legal_holiday'],
    'num_of_days_for_rate_4' => ['label' => 'Special Holiday', 'key' => 'rate_4_special_holiday'],
    'num_of_hours_for_rate_5' => ['label' => 'Regular Overtime per Hour', 'key' => 'rate_5_regular_overtime_perhour'],
    'num_of_hours_for_rate_6' => ['label' => 'Special Overtime per Hour', 'key' => 'rate_6_special_overtime_perhour'],
    'num_of_hours_for_rate_7' => ['label' => 'Special Holiday OT per Hour', 'key' => 'rate_7_special_holiday_overtime_perhour'],
    'num_of_hours_for_rate_8' => ['label' => 'Regular Holiday OT per Hour', 'key' => 'rate_8_regular_holiday_overtime_perhour'],
    'num_of_days_for_rate_9' => ['label' => 'Cater', 'key' => 'rate_9_cater']
];


$conn->close();

// Return variables for the HTML file to use
return [
    'employee' => $employee,
    'payroll_result1' => $payroll_result1,
    'rate_labels' => $rate_labels,
    'payroll_data' => $payroll_data,
    'message' => $message,
    'week' => $week, // Pass the week to the HTML for display
];