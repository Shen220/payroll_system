<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../php/db.php');

$employee = null;

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Check for employee ID in URL
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: ../php/dashboard.php");
        exit();
    }

    $employee_id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT * FROM employee_info_and_rates WHERE employee_id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
    $stmt->close();

    if (!$employee) {
        die("Employee not found.");
    }

} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get form data
    $employee_id = intval($_POST['employee_id']);
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $position = $_POST['position'];
    $status = $_POST['status'];
    $board_lodging = $_POST['board_lodging'];
    $lodging_address = !empty($_POST['lodging_address']) ? $_POST['lodging_address'] : NULL;
    $food_allowance = $_POST['food_allowance'];

    // Rates
    $rate_1 = $_POST['rate_1_daily_minimum_wage'];
    $rate_2 = $_POST['rate_2_sunday_rest_day'];
    $rate_3 = $_POST['rate_3_legal_holiday'];
    $rate_4 = $_POST['rate_4_special_holiday'];
    $rate_5 = $_POST['rate_5_regular_overtime_perhour'];
    $rate_6 = $_POST['rate_6_special_overtime_perhour'];
    $rate_7 = $_POST['rate_7_special_holiday_overtime_perhour'];
    $rate_8 = $_POST['rate_8_regular_holiday_overtime_perhour'];
    $rate_9 = $_POST['rate_9_cater'];

    $stmt = $conn->prepare("
        UPDATE employee_info_and_rates 
        SET last_name = ?, first_name = ?, position = ?, status = ?, board_lodging = ?, lodging_address = ?, food_allowance = ?,
            rate_1_daily_minimum_wage = ?, rate_2_sunday_rest_day = ?, rate_3_legal_holiday = ?, 
            rate_4_special_holiday = ?, rate_5_regular_overtime_perhour = ?, rate_6_special_overtime_perhour = ?, 
            rate_7_special_holiday_overtime_perhour = ?, rate_8_regular_holiday_overtime_perhour = ?, rate_9_cater = ?
        WHERE employee_id = ?
    ");

    $stmt->bind_param(
        "sssssssdddddddddi",
        $last_name, $first_name, $position, $status, $board_lodging, $lodging_address, $food_allowance,
        $rate_1, $rate_2, $rate_3, $rate_4, $rate_5, $rate_6, $rate_7, $rate_8, $rate_9,
        $employee_id
    );
    $stmt->execute();
    $stmt->close();

    // Redirect back to dashboard or show success message
    header("Location: ../php/dashboard.php");
    exit();
}

$conn->close();

// Render HTML form
include '../html/edit_employee_html.php';
