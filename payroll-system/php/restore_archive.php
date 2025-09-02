<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

include 'db.php';

if (isset($_GET['id'])) {
    $employee_id = intval($_GET['id']);

    $conn->begin_transaction();
    try {
        // Move back to main table
        $queryInsert = "
            INSERT INTO employee_info_and_rates (
                employee_id, last_name, first_name, position, status,
                board_lodging, lodging_address, food_allowance,
                rate_1_daily_minimum_wage, rate_2_sunday_rest_day,
                rate_3_legal_holiday, rate_4_special_holiday,
                rate_5_regular_overtime_perhour, rate_6_special_overtime_perhour,
                rate_7_special_holiday_overtime_perhour, rate_8_regular_holiday_overtime_perhour,
                rate_9_cater
            )
            SELECT 
                employee_id, last_name, first_name, position, status,
                board_lodging, lodging_address, food_allowance,
                rate_1_daily_minimum_wage, rate_2_sunday_rest_day,
                rate_3_legal_holiday, rate_4_special_holiday,
                rate_5_regular_overtime_perhour, rate_6_special_overtime_perhour,
                rate_7_special_holiday_overtime_perhour, rate_8_regular_holiday_overtime_perhour,
                rate_9_cater
            FROM archived_employees WHERE employee_id = ?
        ";
        $stmtInsert = $conn->prepare($queryInsert);
        $stmtInsert->bind_param("i", $employee_id);
        $stmtInsert->execute();

        // Delete from archive
        $stmtDelete = $conn->prepare("DELETE FROM archived_employees WHERE employee_id = ?");
        $stmtDelete->bind_param("i", $employee_id);
        $stmtDelete->execute();

        // Commit changes
        $conn->commit();

        // Redirect back to archive page with success message
        header("Location: ../html/archive_html.php?msg=Employee restored successfully");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        echo "Error restoring: " . $e->getMessage();
    }
} else {
    echo "No employee ID provided.";
}
