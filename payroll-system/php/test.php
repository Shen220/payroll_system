<?php
// payslip_form.php

include 'db.php'; // $conn = new mysqli(...)

// Handle form submission
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id   = $_POST['employee_id'] ?? null;
    $year          = $_POST['year'] ?? null;
    $payroll_id    = $_POST['payroll_id'] ?? null;
    $old_year      = $_POST['old_year'] ?? null;
    $old_payroll_id= $_POST['old_payroll_id'] ?? null;

    if ($employee_id && $year && $payroll_id) {
        // Check for duplicate target
        $check = $conn->prepare("
            SELECT 1 FROM payroll_transactions
            WHERE year = ? AND payroll_id = ? AND employee_id = ?
        ");
        $check->bind_param("iii", $year, $payroll_id, $employee_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0 && ($old_year != $year || $old_payroll_id != $payroll_id)) {
            // Duplicate exists if changing to a new period
            $message = "⚠️ A payslip already exists for this employee in the selected period.";
        } else {
            // Update existing record (year/week) instead of inserting new
            $sql = "
                UPDATE payroll_transactions
                SET year = ?, payroll_id = ?, updated_at = CURRENT_TIMESTAMP
                WHERE employee_id = ? AND year = ? AND payroll_id = ?
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiiii", $year, $payroll_id, $employee_id, $old_year, $old_payroll_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $message = "✅ Payslip updated successfully.";
            } else {
                $message = "❌ Update failed or no changes detected.";
            }
            $stmt->close();
        }
        $check->close();
    } else {
        $message = "⚠️ Missing required fields.";
    }
}

// Fetch employees (example: first employee only, adjust as needed)
$employee_result = $conn->query("SELECT employee_id, first_name, last_name FROM employee_info_and_rates LIMIT 1");
$employee = $employee_result->fetch_assoc();

// Fetch payroll periods
$payroll_result1 = $conn->query("SELECT payroll_id, week FROM payroll_dates");

// Optional: If editing existing payroll transaction
$payroll_data = [
    'year' => date("Y"),
    'payroll_id' => null
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip Form</title>
</head>
<body>
    <h2>Payslip Form</h2>

    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
<input type="hidden" name="old_year" value="<?= htmlspecialchars($payroll_data['year'] ?? '') ?>">
<input type="hidden" name="old_payroll_id" value="<?= htmlspecialchars($payroll_data['payroll_id'] ?? '') ?>">
    <form id="payslipForm" method="post">
        <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee['employee_id']) ?>">

        <!-- Year -->
        <label>Year:</label>
        <select name="year" required>
            <option value="">-- Select Year --</option>
            <?php for ($y = date("Y"); $y >= 2000; $y--): ?>
                <option value="<?= $y ?>" <?= ($payroll_data['year'] ?? '') == $y ? 'selected' : '' ?>>
                    <?= $y ?>
                </option>
            <?php endfor; ?>
        </select>
        <br><br>

        <!-- Payroll Period -->
        <label>Payroll Period:</label>
        <select name="payroll_id" required>
            <option value="">-- Select Payroll Period --</option>
            <?php while ($row = $payroll_result1->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['payroll_id']) ?>"
                    <?= ($payroll_data['payroll_id'] ?? '') == $row['payroll_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['week']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br><br>

        <button type="submit">Save</button>
    </form>
</body>
</html>
<?php $conn->close(); ?>
