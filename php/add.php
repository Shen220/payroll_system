<?php
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the form was submitted using POST method.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //Employee Info
    $last_name = filter_var($_POST['last_name']);
    $first_name = filter_var($_POST['first_name']);
    $position = filter_var($_POST['position']);
    $status = filter_var($_POST['status']);
    $board_lodging = filter_var($_POST['board_lodging']);
    $lodging_address = isset($_POST['lodging_address']) ? filter_var($_POST['lodging_address']) : NULL;
    $food_allowance = filter_var($_POST['food_allowance']);
    //Employee Rates
    $w1_daily_minimum_wage = filter_var($_POST['w1_daily_minimum_wage']);
    $w2_sunday_rest_day = filter_var($_POST['w2_sunday_rest_day']);
    $w3_legal_holiday = filter_var($_POST['w3_legal_holiday']);
    $w4_special_holiday = filter_var($_POST['w4_special_holiday']);
    $w5_regular_overtime_perhour = filter_var($_POST['w5_regular_overtime_perhour']);
    $w6_special_overtime_perhour = filter_var($_POST['w6_special_overtime_perhour']);
    $w7_special_holiday_overtime_perhour = filter_var($_POST['w7_special_holiday_overtime_perhour']);
    $w8_regular_holiday_overtime_perhour = filter_var($_POST['w8_regular_holiday_overtime_perhour']);
    $w9_cater = filter_var($_POST['w9_cater']);
        
    try {
        //Insert into employee_info
        $stmt_info = $conn->prepare("INSERT INTO employee_info (last_name, first_name, position, status, board_lodging, lodging_address, food_allowance) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt_info === false) {
            throw new mysqli_sql_exception("Prepare statement for employee_info failed: " . $conn->error);
        }
        $stmt_info->bind_param("sssssss", $last_name, $first_name, $position, $status, $board_lodging, $lodging_address, $food_allowance);
        $stmt_info->execute();
        $employee_id = $stmt_info->insert_id;
        $stmt_info->close();

        //Insert into employee_payroll
        $stmt_payroll = $conn->prepare("
        INSERT INTO employee_payroll (
                employee_id, 
                w1_daily_minimum_wage, 
                w2_sunday_rest_day, 
                w3_legal_holiday, 
                w4_special_holiday, 
                w5_regular_overtime_perhour, 
                w6_special_overtime_perhour, 
                w7_special_holiday_overtime_perhour, 
                w8_regular_holiday_overtime_perhour, 
                w9_cater
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt_payroll === false) {
            throw new mysqli_sql_exception("Prepare statement for employee_payroll failed: " . $conn->error);
        }
        
        $stmt_payroll->bind_param(
        "iddddddddd",
        $employee_id,
        $w1_daily_minimum_wage,
        $w2_sunday_rest_day,
        $w3_legal_holiday,
        $w4_special_holiday,
        $w5_regular_overtime_perhour,
        $w6_special_overtime_perhour,
        $w7_special_holiday_overtime_perhour,
        $w8_regular_holiday_overtime_perhour,
        $w9_cater
    );

        $stmt_payroll->execute();
        $payroll_id = $stmt_payroll->insert_id;
        $stmt_payroll->close();
        
        //Insert into payroll_computation with default 0 values
        $stmt_computation = $conn->prepare("INSERT INTO payroll_computation (payroll_id, employee_id) VALUES (?, ?)");
        if ($stmt_computation === false) {
            throw new mysqli_sql_exception("Prepare statement for payroll_computation failed: " . $conn->error);
        }
        $stmt_computation->bind_param("ii", $payroll_id, $employee_id);
        $stmt_computation->execute();
        $stmt_computation->close();

        // Set a success message and redirect.
        $_SESSION['success'] = "Employee added successfully!";
        header("Location: dashboard.php");
        exit();

    } catch (mysqli_sql_exception $e) {
        // Handle database errors gracefully.
        die("Database error: " . $e->getMessage());
    }
}

$conn->close();
include '../html/edit_employee_html.php';
?>