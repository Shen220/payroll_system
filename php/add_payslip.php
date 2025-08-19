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
$stmt = $conn->prepare("SELECT employee_id, first_name, last_name, position, status FROM employee_info_and_rates WHERE employee_id=?");
$stmt->bind_param("i",$employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
$stmt->close();
if(!$employee) die("Employee not found.");

// Fetch payroll periods
$payroll_result = $conn->query("SELECT payroll_id, week FROM payroll_dates");

// Handle POST submission
if($_SERVER['REQUEST_METHOD']==='POST'){
    $year = $_POST['year'] ?? null;
    $pay_id = $_POST['payroll_id'] ?? null;

    $rates = [
        'num_of_days_for_rate_1','num_of_days_for_rate_2','num_of_days_for_rate_3','num_of_days_for_rate_4',
        'num_of_hours_for_rate_5','num_of_hours_for_rate_6','num_of_hours_for_rate_7','num_of_hours_for_rate_8',
        'num_of_days_for_rate_9'
    ];
    foreach($rates as $r) $$r = (int)($_POST[$r] ?? 0);

    if($year && $pay_id){
        $stmt = $conn->prepare("INSERT INTO payroll_transactions 
            (`year`, payroll_id, employee_id, num_of_days_for_rate_1, num_of_days_for_rate_2, num_of_days_for_rate_3, num_of_days_for_rate_4, num_of_hours_for_rate_5, num_of_hours_for_rate_6, num_of_hours_for_rate_7, num_of_hours_for_rate_8, num_of_days_for_rate_9)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("iiiiiiiiiiii",$year,$pay_id,$employee_id,$num_of_days_for_rate_1,$num_of_days_for_rate_2,$num_of_days_for_rate_3,$num_of_days_for_rate_4,$num_of_hours_for_rate_5,$num_of_hours_for_rate_6,$num_of_hours_for_rate_7,$num_of_hours_for_rate_8,$num_of_days_for_rate_9);
        $message = $stmt->execute() ? "Record added successfully!" : "Error: ".$stmt->error;
        $stmt->close();
    }else{
        $message = "Please fill in year and payroll ID.";
    }
}

$conn->close();

// Export variables for HTML form
return [
    'employee'=>$employee,
    'payroll_result'=>$payroll_result,
    'message'=>$message
];
