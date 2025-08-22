<?php
// Start of file
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB connection here
include('../php/db.php');

// Fetch employee data
$employeeId = $_GET['id'] ?? null;

if ($employeeId) {
    $stmt = $conn->prepare("SELECT * FROM employee_info WHERE employee_id = ?");
    $stmt->bind_param("i", $employeeId);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
} else {
    die("Employee ID not provided.");
}

// Check if request is AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (!$isAjax):
?>

<?php
endif; // End non-AJAX header
?>

<form method="POST" action="../php/edit_employee.php" class="edit_employee_form">
    <!-- Hidden employee_id -->
    <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($employee['employee_id']); ?>">

        <label class="special_label">Last Name:</label>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($employee['last_name']); ?>" required><br>

        <label>First Name:</label>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($employee['first_name']); ?>" required><br>

        <label>Position:</label>
        <input type="text" name="position" value="<?php echo htmlspecialchars($employee['position']); ?>" required><br>

        <label>Status:</label>
        <select name="status" required>
            <option value="Permanent" <?php if($employee['status'] == 'Permanent') echo 'selected'; ?>>Permanent</option>
            <option value="On-Call" <?php if($employee['status'] == 'On-Call') echo 'selected'; ?>>On-Call</option>
        </select><br>


        <label>Board & Lodging:</label>
        <select name="board_lodging" required onchange="toggleLodgingAddress1(this.value, 'edit_lodging_input')">
            <option value="No" <?php if($employee['board_lodging'] == 'No') echo 'selected'; ?>>No</option>
            <option value="Yes" <?php if($employee['board_lodging'] == 'Yes') echo 'selected'; ?>>Yes</option>
        </select><br>

        <div id="edit_lodging_input" style="<?php echo ($employee['board_lodging'] == 'Yes') ? 'display:block;' : 'display:none;'; ?>">
            <label>Lodging Address:</label>
            <input class="add-input"  type="text" name="lodging_address" value="<?php echo htmlspecialchars($employee['lodging_address']); ?>"><br>
        </div>

        <label>Food Allowance:</label>
        <select name="food_allowance" required>
            <option value="Full" <?php if($employee['food_allowance'] == 'Full') echo 'selected'; ?>>Full</option>
            <option value="Partial" <?php if($employee['food_allowance'] == 'Partial') echo 'selected'; ?>>Partial</option>
            <option value="None" <?php if($employee['food_allowance'] == 'None') echo 'selected'; ?>>None</option>
        </select><br>

    <button type="submit" class="update-btn">Update Employee</button>
</form>

<script>
    function toggleLodgingAddress1(value, inputId) {
        document.getElementById(inputId).style.display = (value === "Yes") ? "block" : "none";
    }

    document.addEventListener('DOMContentLoaded', function () {
        const lodgingSelect = document.querySelector('select[name="board_lodging"]');
        toggleLodgingAddress1(lodgingSelect.value, 'edit_lodging_input');
    });
</script>

<?php if (!$isAjax): ?>
</div> <!-- End container -->
</body>
</html>
<?php endif; ?>
