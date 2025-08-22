<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Employee Info
    $last_name = filter_var($_POST['last_name']);
    $first_name = filter_var($_POST['first_name']);
    $position = filter_var($_POST['position']);
    $status = filter_var($_POST['status']);
    $board_lodging = filter_var($_POST['board_lodging']);
    $lodging_address = isset($_POST['lodging_address']) ? filter_var($_POST['lodging_address']) : NULL;
    $food_allowance = filter_var($_POST['food_allowance']);

    // Employee Rates
    $rate_1 = filter_var($_POST['rate_1_daily_minimum_wage']);
    $rate_2 = filter_var($_POST['rate_2_sunday_rest_day']);
    $rate_3 = filter_var($_POST['rate_3_legal_holiday']);
    $rate_4 = filter_var($_POST['rate_4_special_holiday']);
    $rate_5 = filter_var($_POST['rate_5_regular_overtime_perhour']);
    $rate_6 = filter_var($_POST['rate_6_special_overtime_perhour']);
    $rate_7 = filter_var($_POST['rate_7_special_holiday_overtime_perhour']);
    $rate_8 = filter_var($_POST['rate_8_regular_holiday_overtime_perhour']);
    $rate_9 = filter_var($_POST['rate_9_cater']);

    try {
        $stmt = $conn->prepare("
            INSERT INTO employee_info_and_rates (
                last_name, first_name, position, status, board_lodging, lodging_address, food_allowance,
                rate_1_daily_minimum_wage,
                rate_2_sunday_rest_day,
                rate_3_legal_holiday,
                rate_4_special_holiday,
                rate_5_regular_overtime_perhour,
                rate_6_special_overtime_perhour,
                rate_7_special_holiday_overtime_perhour,
                rate_8_regular_holiday_overtime_perhour,
                rate_9_cater
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if ($stmt === false) {
            throw new mysqli_sql_exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssssddddddddd",
            $last_name,
            $first_name,
            $position,
            $status,
            $board_lodging,
            $lodging_address,
            $food_allowance,
            $rate_1,
            $rate_2,
            $rate_3,
            $rate_4,
            $rate_5,
            $rate_6,
            $rate_7,
            $rate_8,
            $rate_9
        );

        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = "Employee added successfully!";
        header("Location: dashboard.php");
        exit();

    } catch (mysqli_sql_exception $e) {
        die("Database error: " . $e->getMessage());
    }
}

$conn->close();
include '../html/add_html.php';
?>