<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

include 'db.php'; // DB connection

// Get the employee ID from the URL
$employee_id = $_GET['employee_id'] ?? null;

if (!$employee_id) {
    die("No employee ID specified.");
}

// Start a transaction for safety
$conn->begin_transaction();

try {
    // Insert record into archived_employees
    $sql_insert = "
        INSERT INTO archived_employees (
            employee_id,
            last_name,
            first_name,
            position,
            status,
            board_lodging,
            lodging_address,
            food_allowance,
            rate_1_daily_minimum_wage,
            rate_2_sunday_rest_day,
            rate_3_legal_holiday,
            rate_4_special_holiday,
            rate_5_regular_overtime_perhour,
            rate_6_special_overtime_perhour,
            rate_7_special_holiday_overtime_perhour,
            rate_8_regular_holiday_overtime_perhour,
            rate_9_cater
        )
        SELECT 
            employee_id,
            last_name,
            first_name,
            position,
            status,
            board_lodging,
            lodging_address,
            food_allowance,
            rate_1_daily_minimum_wage,
            rate_2_sunday_rest_day,
            rate_3_legal_holiday,
            rate_4_special_holiday,
            rate_5_regular_overtime_perhour,
            rate_6_special_overtime_perhour,
            rate_7_special_holiday_overtime_perhour,
            rate_8_regular_holiday_overtime_perhour,
            rate_9_cater
        FROM employee_info_and_rates
        WHERE employee_id = ?
    ";

    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param('i', $employee_id);

    if (!$stmt_insert->execute()) {
        throw new Exception("Insert failed: " . $stmt_insert->error);
    }
    
    $sql_delete = "DELETE FROM employee_info_and_rates WHERE employee_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param('i', $employee_id);

    if (!$stmt_delete->execute()) {
        throw new Exception("Delete failed: " . $stmt_delete->error);
    }

    $conn->commit();

    header("Location: dashboard.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Error archiving employee: " . $e->getMessage();
}
?>