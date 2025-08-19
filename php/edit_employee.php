<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

include('db.php');
if ($_SERVER["REQUEST_METHOD"] === "GET" && !isset($_GET['id'])) {
    echo "Employee ID not provided in URL.";
    exit;
}

// Fetch employee data
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $employee_id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM employee_info WHERE employee_id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
    $stmt->close(); 

    if (!$employee) {
        die("Employee not found.");
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Process form submission
    $employee_id = $_POST['employee_id'];
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $position = $_POST['position'];
    $status = $_POST['status'];
    $board_lodging = $_POST['board_lodging'];
    $lodging_address = isset($_POST['lodging_address']) ? $_POST['lodging_address'] : NULL;
    $food_allowance = $_POST['food_allowance'];

    $stmt = $conn->prepare("UPDATE employee_info 
                            SET last_name = ?, first_name = ?, position = ?, status = ?, board_lodging = ?, lodging_address = ?, food_allowance = ?
                            WHERE employee_id = ?");
    $stmt->bind_param("sssssssi", $last_name, $first_name, $position, $status, $board_lodging, $lodging_address, $food_allowance, $employee_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php"); // redirect after update
        exit();
    } else {
        die("Error updating record: " . $stmt->error);
    }
}

// Close connection (will be skipped if redirected)
$conn->close();
include '../html/edit_employee_html.php';
?>
