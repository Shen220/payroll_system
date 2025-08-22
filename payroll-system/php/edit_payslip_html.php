<?php
// Assuming this is called via AJAX
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('../php/db.php'); // Make sure DB connection is available
?>

<link rel="stylesheet" href="../css/edit_payslip.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

<?php
$payrollId = $_GET['id'] ?? null;
$successMessage = '';
$errorMessage = '';

if ($payrollId) {
    $stmt = $conn->prepare("
        SELECT ei.*, pd.*
        FROM employee_info_and_rates ei
        LEFT JOIN payroll_dates pd ON ei.employee_id = ei.employee_id
        WHERE ei.employee_id = ?
    ");
    $stmt->bind_param("i", $payrollId);
    $stmt->execute();
    $result = $stmt->get_result();
    $value = $result->fetch_assoc();
    $stmt->close();
} else {
    echo "Payroll ID not provided.";
    exit;
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}
?>

<div class="payslip-modal-payslip-editor">

    <?php if ($successMessage): ?>
        <p class="payslip-success"><?= $successMessage ?></p>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <p class="payslip-error"><?= $errorMessage ?></p>
    <?php endif; ?>

    <form method="post" action="../php/edit_payslip.php" class="payslip-edit-payslip-form">
        <input type="hidden" name="payroll_id" value="<?= $payrollId ?>">

        <div class="payslip-for-style">
            <p>Date: <?= date("F j, Y") ?></p>
            <h7 id="ppayslip-text">AI Korean Buffet Restaurant</h7>
            <h7 id="ppayslip-text-special">MH del pilar Burnham Legarda road, Baguio City, Philippines</h7>
        </div>
        <div class="payslip-third_container">
            <div>
                <p class="ppayslip-p"><strong>ID:</strong> <?= htmlspecialchars($value['employee_id']) ?></p>
                <p class="ppayslip-p"><strong>Name:</strong> <?= strtoupper(htmlspecialchars($value['last_name'])) ?>, <?= htmlspecialchars($value['first_name']) ?></p>
            </div>
            <div>
                <p class="ppayslip-p"><strong>Position:</strong> <?= htmlspecialchars($value['position']) ?></p>
                <p class="ppayslip-p"><strong>Status:</strong> <?= htmlspecialchars($value['status']) ?></p>
            </div>
        </div>

        <h3 class="ppayslip-h3">Work Details</h3>
                <table class="ppayslip-table">
            <tr class="ppayslip-tr">
                <td class="ppayslip-td"></td>
                <td class="ppayslip-td">Days/Hours</td>
                <td class="ppayslip-td">Total</td>
            </tr>
            </thead>
            <tbody class="ppayslip-tbody">
            <?php
            $categories = [
                ['Daily Minimum Wage', 'w1_daily_minimum_wage', 'w1'],
                ['Sunday Rest Day', 'w2_sunday_rest_day', 'w2'],
                ['Legal Holiday', 'w3_legal_holiday','w3'],
                ['Special Holiday', 'w4_special_holiday','w4'],
                ['Regular Overtime', 'w5_regular_overtime_perhour',  'w5'],
                ['Special Overtime', 'w6_special_overtime_perhour', 'w6'],
                ['Special Holiday Overtime', 'w7_special_holiday_overtime_perhour','w7'],
                ['Regular Holiday Overtime', 'w8_regular_holiday_overtime_perhour', 'w8'],
                ['Cater', 'w9_cater','w9'],
            ];

            foreach ($categories as [$label, $hourField, $totalField]) {
                $hours = (float)$value[$hourField];
                $total = (float)$value[$totalField];

                echo "<tr>
                    <td>$label</td>
                    <td><input type='number' name='$hourField' value='$hours' class='hours form-control' data-target='$totalField'></td>
                    <td><span id='{$totalField}_display'>" . formatCurrency($total) . "</span></td>
                </tr>";
            }
            ?>
            <tr class="ppayslip-tr">
                <td class="ppayslip-td"><strong>Gross Pay</strong></td>
                <td class="ppayslip-td-special" colspan="3"><?= formatCurrency($value['gross_pay']) ?></td>
            </tr>
            </tbody>
        </table>

        <h3 class="ppayslip-h3">Deductions</h3>
        <table class="ppayslip-table">
            <tbody class="ppayslip-tbody">
                <tr class="ppayslip-tr"><td class="ppayslip-td">SSS</td><td><?= formatCurrency($value['sss']) ?></td></tr>
                <tr class="ppayslip-tr"><td class="ppayslip-td">PhilHealth</td><td><?= formatCurrency($value['philhealth']) ?></td></tr>
                <tr class="ppayslip-tr"><td class="ppayslip-td">Pag-IBIG</td><td><?= formatCurrency($value['pagibig']) ?></td></tr>
                <tr class="ppayslip-tr">
                    <td class="ppayslip-td">Cater Deduction</td>
                    <td class="ppayslip-td-special"><input type="number" name="cater1" value="<?= $value['cater1'] ?>" class="ppayslip-form-control"></td>
                </tr>
                <tr class="ppayslip-tr">
                    <td class="ppayslip-td">Advance</td>
                    <td class="ppayslip-td-special"><input type="number" name="advance" value="<?= $value['advance'] ?>" class="ppayslip-form-control"></td>
                </tr>
                <tr class="ppayslip-tr"><td class="ppayslip-td">Total Deductions</td> <td class="ppayslip-td"><?= formatCurrency($value['total_deductions']) ?></td></tr>
                <tr class="ppayslip-tr"><td class="ppayslip-td"><strong>Net Pay</strong></td>
                <td class="ppayslip-td"><strong class="ppayslip-strong"><?= formatCurrency($value['net_pay']) ?></strong></td>
                </tr>
            </tbody>
        </table>

        <br>
        <button type="submit" class="ppayslip-save_btn">Save Changes</button>
    </form>
</div>