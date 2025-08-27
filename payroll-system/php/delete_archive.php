<?php
include 'db.php';

if (isset($_GET['id'])) {
    $employee_id = intval($_GET['id']);

    try {
        $query = "DELETE FROM archived_employees WHERE employee_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();

        // Redirect back with success message
        header("Location: ../html/archive_html.php?msg=Employee deleted permanently");
        exit();
    } catch (Exception $e) {
        echo "Error deleting employee: " . $e->getMessage();
    }
} else {
    echo "No employee ID provided.";
}
?>
