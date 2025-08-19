<?php
include '../php/db.php';
// Small helper for safe escaping
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Payslip</title>
    <link rel="stylesheet" href="../css/edit_payslip.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2>Edit Payslip</h2>

    <!-- Employee info (read-only) -->
    <div class="employee-info">
        <h3>Employee Information</h3>
        <p><strong>Name:</strong>
            <?= h(trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''))) ?></p>
        <p><strong>Position:</strong> <?= h($employee['position'] ?? '') ?></p>
        <p><strong>Status:</strong> <?= h($employee['status'] ?? '') ?></p>
        <p><strong>Food Allowance:</strong> <?= h($employee['food_allowance'] ?? '0') ?></p>
        <p><strong>Board & Lodging:</strong> <?= h($employee['board_lodging'] ?? '') ?></p>
        <p><strong>Lodging Address:</strong> <?= h($employee['lodging_address'] ?? '') ?></p>
    </div>

    <!-- Payroll week info -->
    <div class="payroll-info">
        <h3>Payroll Period</h3>
        <p><strong>Week:</strong> <?= h($payroll['week'] ?? '') ?></p>
    </div>

    <!-- Editable fields: days/hours/deductions -->
    <form action="../php/edit_payslip.php" method="POST">
        <input type="hidden" name="employee_id" value="<?= h($employee['employee_id'] ?? '') ?>">
        <input type="hidden" name="payroll_id" value="<?= h($payroll['payroll_id'] ?? '') ?>">

        <h3>Days / Hours Worked</h3>
        <table border="1" cellpadding="6" cellspacing="0">
            <tr>
                <th>Rate</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Daily Minimum Wage Days</td>
                <td><input type="number" name="num_of_days_for_rate_1" value="<?= h($transactions['num_of_days_for_rate_1'] ?? 0) ?>" min="0"></td>
            </tr>
            <tr>
                <td>Sunday Rest Day Days</td>
                <td><input type="number" name="num_of_days_for_rate_2" value="<?= h($transactions['num_of_days_for_rate_2'] ?? 0) ?>" min="0"></td>
            </tr>
            <tr>
                <td>Legal Holiday Days</td>
                <td><input type="number" name="num_of_days_for_rate_3" value="<?= h($transactions['num_of_days_for_rate_3'] ?? 0) ?>" min="0"></td>
            </tr>
            <tr>
                <td>Special Holiday Days</td>
                <td><input type="number" name="num_of_days_for_rate_4" value="<?= h($transactions['num_of_days_for_rate_4'] ?? 0) ?>" min="0"></td>
            </tr>
            <tr>
                <td>Regular Overtime Hours</td>
                <td><input type="number" name="num_of_hours_for_rate_5" value="<?= h($transactions['num_of_hours_for_rate_5'] ?? 0) ?>" min="0"></td>
            </tr>
            <tr>
                <td>Special Overtime Hours</td>
                <td><input type="number" name="num_of_hours_for_rate_6" value="<?= h($transactions['num_of_hours_for_rate_6'] ?? 0) ?>" min="0"></td>
            </tr>
            <tr>
                <td>Special Holiday Overtime Hours</td>
                <td><input type="number" name="num_of_hours_for_rate_7" value="<?= h($transactions['num_of_hours_for_rate_7'] ?? 0) ?>" min="0"></td>
            </tr>
            <tr>
                <td>Regular Holiday Overtime Hours</td>
                <td><input type="number" name="num_of_hours_for_rate_8" value="<?= h($transactions['num_of_hours_for_rate_8'] ?? 0) ?>" min="0"></td>
            </tr>
            <tr>
                <td>Cater Days</td>
                <td><input type="number" name="num_of_days_for_rate_9" value="<?= h($transactions['num_of_days_for_rate_9'] ?? 0) ?>" min="0"></td>
            </tr>
        </table>

        <h3>Deductions</h3>
        <label>Cater Deductions:
            <input type="number" step="0.01" name="cater_deductions" value="<?= h($transactions['cater_deductions'] ?? 0) ?>">
        </label><br>
        <label>Advance Deductions:
            <input type="number" step="0.01" name="advance_deductions" value="<?= h($transactions['advance_deductions'] ?? 0) ?>">
        </label><br><br>

        <button type="submit">Save Changes</button>
        <a href="../php/dashboard.php">Cancel</a>
    </form>
</div>
</body>
</html>
