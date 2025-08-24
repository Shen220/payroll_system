<?php
// Start of file
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB connection
include('../php/db.php');

// Fetch employee data
$employeeId = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($employeeId) {
    $stmt = $conn->prepare("SELECT * FROM employee_info_and_rates WHERE employee_id = ?");
    $stmt->bind_param("i", $employeeId);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
    $stmt->close();
} else {
    header("Location: ../php/dashboard.php");
    exit();
}

// Check if request is AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (!$isAjax):
?>

<?php endif; // End non-AJAX header ?>
<form method="POST" action="../php/edit_employee.php" class="edit_employee_form">
    <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($employee['employee_id']); ?>">

    <label>Last Name:</label>
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
        <input type="text" name="lodging_address" value="<?php echo htmlspecialchars($employee['lodging_address']); ?>"><br>
    </div>

    <label>Food Allowance:</label>
    <select name="food_allowance" required>
        <option value="Full" <?php if($employee['food_allowance'] == 'Full') echo 'selected'; ?>>Full</option>
        <option value="Partial" <?php if($employee['food_allowance'] == 'Partial') echo 'selected'; ?>>Partial</option>
        <option value="None" <?php if($employee['food_allowance'] == 'None') echo 'selected'; ?>>None</option>
    </select><br>

    <h5 style="margin-top: 10px;">Rate Information</h5>
    <label>Daily Minimum Wage:</label>
    <input class="edit-employee-for-num" type="number"  name="rate_1_daily_minimum_wage" value="<?php echo htmlspecialchars($employee['rate_1_daily_minimum_wage']); ?>"><br>

    <label>Sunday Rest Day:</label>
    <input type="number"  name="rate_2_sunday_rest_day" value="<?php echo htmlspecialchars($employee['rate_2_sunday_rest_day']); ?>"><br>

    <label>Legal Holiday:</label>
    <input type="number"  name="rate_3_legal_holiday" value="<?php echo htmlspecialchars($employee['rate_3_legal_holiday']); ?>"><br>

    <label>Special Holiday:</label>
    <input type="number"  name="rate_4_special_holiday" value="<?php echo htmlspecialchars($employee['rate_4_special_holiday']); ?>"><br>

    <label>Regular Overtime Per Hour:</label>
    <input type="number"  name="rate_5_regular_overtime_perhour" value="<?php echo htmlspecialchars($employee['rate_5_regular_overtime_perhour']); ?>"><br>

    <label>Special Overtime Per Hour:</label>
    <input type="number"  name="rate_6_special_overtime_perhour" value="<?php echo htmlspecialchars($employee['rate_6_special_overtime_perhour']); ?>"><br>

    <label>Special Holiday Overtime Per Hour:</label>
    <input type="number"  name="rate_7_special_holiday_overtime_perhour" value="<?php echo htmlspecialchars($employee['rate_7_special_holiday_overtime_perhour']); ?>"><br>

    <label>Regular Holiday Overtime Per Hour:</label>
    <input type="number"  name="rate_8_regular_holiday_overtime_perhour" value="<?php echo htmlspecialchars($employee['rate_8_regular_holiday_overtime_perhour']); ?>"><br>

    <label>Cater:</label>
    <input type="number"  name="rate_9_cater" value="<?php echo htmlspecialchars($employee['rate_9_cater']); ?>"><br>

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
</body>
</html>
<?php endif; ?>
