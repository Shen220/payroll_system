<?php
session_start();

$host = "localhost";
$user = "root";         // default username for XAMPP
$pass = "";             // default password is empty
$db   = "payroll_db";   // name of your database

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$employee = null;
$payroll = null;
$transactions = null;

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Validate employee ID and payroll ID
    if (!isset($_GET['employee_id']) || !is_numeric($_GET['employee_id'])) {
        header("Location: ../php/dashboard.php");
        exit();
    }
    if (!isset($_GET['payroll_id']) || !is_numeric($_GET['payroll_id'])) {
        header("Location: ../php/dashboard.php");
        exit();
    }

    $employee_id = intval($_GET['employee_id']);
    $payroll_id = intval($_GET['payroll_id']);

    // Fetch employee info (read-only)
    $stmt = $conn->prepare("SELECT * FROM employee_info_and_rates WHERE employee_id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
    $stmt->close();

    if (!$employee) {
        die("Employee not found.");
    }

    // Fetch payroll week
    $stmt = $conn->prepare("SELECT * FROM payroll_dates WHERE payroll_id = ?");
    $stmt->bind_param("i", $payroll_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payroll = $result->fetch_assoc();
    $stmt->close();

    if (!$payroll) {
        die("Payroll period not found.");
    }

    // Fetch payroll transaction (hours/days input)
    $stmt = $conn->prepare("
        SELECT * 
        FROM payroll_transactions 
        WHERE employee_id = ? AND payroll_id = ?
    ");
    $stmt->bind_param("ii", $employee_id, $payroll_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = $result->fetch_assoc();
    $stmt->close();

    if (!$transactions) {
        // If no record exists, initialize with defaults
        $transactions = [
            'num_of_days_for_rate_1' => 0,
            'num_of_days_for_rate_2' => 0,
            'num_of_days_for_rate_3' => 0,
            'num_of_days_for_rate_4' => 0,
            'num_of_hours_for_rate_5' => 0,
            'num_of_hours_for_rate_6' => 0,
            'num_of_hours_for_rate_7' => 0,
            'num_of_hours_for_rate_8' => 0,
            'num_of_days_for_rate_9' => 0,
            'cater_deductions' => 0.00,
            'advance_deductions' => 0.00,
        ];
    }

} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate IDs
    $employee_id = intval($_POST['employee_id']);
    $payroll_id = intval($_POST['payroll_id']);

    // Fetch form inputs (days/hours only)
    $rate1 = intval($_POST['num_of_days_for_rate_1'] ?? 0);
    $rate2 = intval($_POST['num_of_days_for_rate_2'] ?? 0);
    $rate3 = intval($_POST['num_of_days_for_rate_3'] ?? 0);
    $rate4 = intval($_POST['num_of_days_for_rate_4'] ?? 0);
    $rate5 = intval($_POST['num_of_hours_for_rate_5'] ?? 0);
    $rate6 = intval($_POST['num_of_hours_for_rate_6'] ?? 0);
    $rate7 = intval($_POST['num_of_hours_for_rate_7'] ?? 0);
    $rate8 = intval($_POST['num_of_hours_for_rate_8'] ?? 0);
    $rate9 = intval($_POST['num_of_days_for_rate_9'] ?? 0);
    $cater_deductions = floatval($_POST['cater_deductions'] ?? 0);
    $advance_deductions = floatval($_POST['advance_deductions'] ?? 0);

    // Insert or update payroll transaction
    $stmt = $conn->prepare("
        INSERT INTO payroll_transactions (
            payroll_id, employee_id,
            num_of_days_for_rate_1, num_of_days_for_rate_2, num_of_days_for_rate_3, num_of_days_for_rate_4,
            num_of_hours_for_rate_5, num_of_hours_for_rate_6, num_of_hours_for_rate_7, num_of_hours_for_rate_8,
            num_of_days_for_rate_9, cater_deductions, advance_deductions
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            num_of_days_for_rate_1 = VALUES(num_of_days_for_rate_1),
            num_of_days_for_rate_2 = VALUES(num_of_days_for_rate_2),
            num_of_days_for_rate_3 = VALUES(num_of_days_for_rate_3),
            num_of_days_for_rate_4 = VALUES(num_of_days_for_rate_4),
            num_of_hours_for_rate_5 = VALUES(num_of_hours_for_rate_5),
            num_of_hours_for_rate_6 = VALUES(num_of_hours_for_rate_6),
            num_of_hours_for_rate_7 = VALUES(num_of_hours_for_rate_7),
            num_of_hours_for_rate_8 = VALUES(num_of_hours_for_rate_8),
            num_of_days_for_rate_9 = VALUES(num_of_days_for_rate_9),
            cater_deductions = VALUES(cater_deductions),
            advance_deductions = VALUES(advance_deductions)
    ");

    $stmt->bind_param(
        "iiiiiiiiiiidd",
        $payroll_id, $employee_id,
        $rate1, $rate2, $rate3, $rate4,
        $rate5, $rate6, $rate7, $rate8,
        $rate9, $cater_deductions, $advance_deductions
    );
    $stmt->execute();
    $stmt->close();

    // Redirect to reload updated data
    header("Location: edit_payslip.php?employee_id=$employee_id&payroll_id=$payroll_id");
    exit();
}

$conn->close();
include '../html/edit_payslip_html.php';
?>
