<?php
// add_payslip_logic.php
include('../php/db.php');
session_start();

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

$message = "";

// Get employee ID from GET (for form load) or POST (for submission)
$employee_id = $_POST['employee_id'] ?? $_GET['employee_id'] ?? null;
if(!$employee_id) die("No employee ID provided.");

// Fetch employee info
$stmt = $conn->prepare("SELECT * FROM employee_info_and_rates WHERE employee_id=?");
$stmt->bind_param("i",$employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
$stmt->close();
if(!$employee) die("Employee not found.");

// Fetch payroll periods
$payroll_result = $conn->query("SELECT payroll_id, week FROM payroll_dates");

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

// Handle POST submission
if($_SERVER['REQUEST_METHOD']==='POST'){
    $year = $_POST['year'] ?? null;
    $pay_id = $_POST['payroll_id'] ?? null;

    $rates = array_keys($rate_labels);
    foreach($rates as $r) $$r = (int)($_POST[$r] ?? 0);

    $cater_deductions = (float)($_POST['cater_deductions'] ?? 0);
    $advance_deductions = (float)($_POST['advance_deductions'] ?? 0);

    if($year && $pay_id){
        $stmt = $conn->prepare("INSERT INTO payroll_transactions 
            (`year`, payroll_id, employee_id, num_of_days_for_rate_1, num_of_days_for_rate_2, num_of_days_for_rate_3, num_of_days_for_rate_4, num_of_hours_for_rate_5, num_of_hours_for_rate_6, num_of_hours_for_rate_7, num_of_hours_for_rate_8, num_of_days_for_rate_9,cater_deductions,advance_deductions)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param(
            "iiiiiiiiiiiidd",
            $year,$pay_id,$employee_id,
            $num_of_days_for_rate_1,$num_of_days_for_rate_2,$num_of_days_for_rate_3,$num_of_days_for_rate_4,
            $num_of_hours_for_rate_5,$num_of_hours_for_rate_6,$num_of_hours_for_rate_7,$num_of_hours_for_rate_8,
            $num_of_days_for_rate_9, $cater_deductions, $advance_deductions
        );
        $message = $stmt->execute() ? "Record added successfully!" : "Error: ".$stmt->error;
        $stmt->close();
    }else{
        $message = "Please fill in year and payroll ID.";
    }
}

$conn->close();

// Export variables for HTML form
return [
    'employee' => $employee,
    'payroll_result' => $payroll_result,
    'rate_labels' => $rate_labels,
    'message' => $message
];
