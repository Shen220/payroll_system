<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

include 'db.php';

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
    'num_of_days_for_rate_1' => ['label' => 'Daily Minimum Wage', 'key' => 'rate_1_daily_minimum_wage'],
    'num_of_days_for_rate_2' => ['label' => 'Sunday Rest Day', 'key' => 'rate_2_sunday_rest_day'],
    'num_of_days_for_rate_3' => ['label' => 'Legal Holiday', 'key' => 'rate_3_legal_holiday'],
    'num_of_days_for_rate_4' => ['label' => 'Special Holiday', 'key' => 'rate_4_special_holiday'],
    'num_of_hours_for_rate_5' => ['label' => 'Regular Overtime/Hour', 'key' => 'rate_5_regular_overtime_perhour'],
    'num_of_hours_for_rate_6' => ['label' => 'Special Overtime/Hour', 'key' => 'rate_6_special_overtime_perhour'],
    'num_of_hours_for_rate_7' => ['label' => 'Special Holiday Overtime/Hour', 'key' => 'rate_7_special_holiday_overtime_perhour'],
    'num_of_hours_for_rate_8' => ['label' => 'Regular Holiday Overtime/Hour', 'key' => 'rate_8_regular_holiday_overtime_perhour'],
    'num_of_days_for_rate_9' => ['label' => 'Cater', 'key' => 'rate_9_cater']
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
        if ($stmt->execute()) {
            $stmt->close();
            echo "success"; // or echo json_encode(["status"=>"success"]);
            exit();
        }
        else {
            $message = "Error: " . $stmt->error;
            $stmt->close();
        }

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
