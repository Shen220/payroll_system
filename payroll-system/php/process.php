<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "payroll_db";  // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert into Employee Info Table
if (isset($_POST['submit_employee'])) {
    // Get form data
    $employee_id = $_POST['employee_id'];
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $position = $_POST['position'];
    $status = $_POST['status'];
    $board_lodging = $_POST['board_lodging'];
    $lodging_address = $_POST['lodging_address'];
    $food_allowance = $_POST['food_allowance'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO employee_info (employee_id, last_name, first_name, position, status, board_lodging, lodging_address, food_allowance) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssss", $employee_id, $last_name, $first_name, $position, $status, $board_lodging, $lodging_address, $food_allowance);

    // Execute the query
    if ($stmt->execute()) {
        echo "New record created successfully in Employee table.<br>";
    } else {
        echo "Error: " . $stmt->error . "<br>";
    }

    // Close statement
    $stmt->close();
}

// Insert into Rates Table
if (isset($_POST['submit_rate'])) {
    // Get form data
    $daily_minimum_wage = $_POST['daily_minimum_wage'];
    $sunday_rest_day = $_POST['sunday_rest_day'];
    $legal_holiday = $_POST['legal_holiday'];
    $special_holiday = $_POST['special_holiday'];
    $regular_overtime_perhour = $_POST['regular_overtime_perhour'];
    $special_overtime_perhour = $_POST['special_overtime_perhour'];
    $special_holiday_overtime_perhour = $_POST['special_holiday_overtime_perhour'];
    $regular_holiday_overtime_perhour = $_POST['regular_holiday_overtime_perhour'];
    $cater = $_POST['cater'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO rates (daily_minimum_wage, sunday_rest_day, legal_holiday, special_holiday, regular_overtime_perhour, special_overtime_perhour, special_holiday_overtime_perhour, regular_holiday_overtime_perhour, cater) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ddddddddd", $daily_minimum_wage, $sunday_rest_day, $legal_holiday, $special_holiday, $regular_overtime_perhour, $special_overtime_perhour, $special_holiday_overtime_perhour, $regular_holiday_overtime_perhour, $cater);

    // Execute the query
    if ($stmt->execute()) {
        echo "New record created successfully in Rates table.<br>";
    } else {
        echo "Error: " . $stmt->error . "<br>";
    }

    // Close statement
    $stmt->close();
}

/// Insert into Days/Hours Worked Table
if (isset($_POST['submit_days_hours'])) {
    // Get form data
    $employee_id = $_POST['employee_id'];
    $rate_id = $_POST['rate_id'];
    $days_hours_of = $_POST['days_hours_of'];
    $quantity = $_POST['quantity'];

    // Ensure the rate_id exists in the rates table
    $stmt_check_rate = $conn->prepare("SELECT rate_id FROM rates WHERE rate_id = ?");
    $stmt_check_rate->bind_param("i", $rate_id);
    $stmt_check_rate->execute();
    $result = $stmt_check_rate->get_result();

    if ($result->num_rows == 0) {
        echo "Error: The rate_id does not exist in the rates table.";
        exit; // Stop execution if the rate_id is not found
    }
    $stmt_check_rate->close();

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO days_hours (employee_id, rate_id, days_hours_of, quantity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisd", $employee_id, $rate_id, $days_hours_of, $quantity);

    // Execute the query
    if ($stmt->execute()) {
        echo "New record created successfully in Days/Hours Worked table.<br>";
    } else {
        echo "Error: " . $stmt->error . "<br>";
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>
