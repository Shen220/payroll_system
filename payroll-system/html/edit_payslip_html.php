<?php
$data = include('../php/edit_payslip.php'); // your PHP backend script
$employee = $data['employee'];
$payroll_result = $data['payroll_result'];
$rate_labels = $data['rate_labels'];
$payroll_data = $data['payroll_data'];
$message = $data['message'];
?>

<div class="add-payslip-details">
    <div class="sub-details">
        <p><strong>ID:</strong> <?= htmlspecialchars($employee['employee_id']) ?></p>
        <p><strong>Name:</strong> <?= htmlspecialchars($employee['first_name'].' '.$employee['last_name']) ?></p>
    </div>
    <div class="sub-details">
        <p><strong>Position:</strong> <?= htmlspecialchars($employee['position']) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($employee['status']) ?></p>
    </div>
</div>

<?php if($message): ?>
    <p style="color:green;"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form id="payslipForm" method="post">
    <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee['employee_id']) ?>">
    <input type="hidden" name="payroll_id" value="<?= htmlspecialchars($payroll_data['payroll_id'] ?? '') ?>">

    <!-- Year -->
    <label>Year:</label>
    <select name="year" required>
        <option value="">-- Select Year --</option>
        <?php for($y=date("Y"); $y>=2000; $y--): ?>
            <option value="<?= $y ?>" <?= ($payroll_data['year']??'')==$y?'selected':'' ?>><?= $y ?></option>
        <?php endfor; ?>
    </select><br><br>

    <!-- Payroll Week -->
    <label>Payroll Period:</label>
    <select name="week" id="payrollPeriod" required>
        <option value="">-- Select Payroll Period --</option>
        <?php while($row=$payroll_result->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($row['week']) ?>" <?= ($payroll_data['week']??'')==$row['week']?'selected':'' ?>><?= htmlspecialchars($row['week']) ?></option>
        <?php endwhile; ?>
    </select><br><br>

    <!-- Rate Inputs -->
    <?php foreach ($rate_labels as $name => $data): 
        $rate_key = $data['key'];
        $label = $data['label'];
        $value = $payroll_data[$name] ?? 0;
        $multiplier = $employee[$rate_key] ?? 0;
    ?>
        <div class="rate-box">
            <label><?= htmlspecialchars($label) ?>:</label>
            <input 
                class="add-payslip-for-num"
                type="number" 
                name="<?= htmlspecialchars($name) ?>" 
                min="0" 
                value="<?= htmlspecialchars($value) ?>"
                id="input_<?= htmlspecialchars($rate_key) ?>"
                data-multiplier="<?= htmlspecialchars($multiplier) ?>"
            >
            <p><strong>Result:</strong> <span id="result_<?= htmlspecialchars($rate_key) ?>">0.00</span></p>
        </div>
    <?php endforeach; ?>

    <!-- Gross Pay -->
    <div class="for-pay">
        <label>Gross Pay:</label>
        <span id="total_amount">0.00</span>
    </div>

    <!-- Government Deductions Toggle -->
    <div style="margin-top:10px; margin-bottom:5px;">
        <input type="checkbox" id="disableGovDeductionsToggle">
        <label for="disableGovDeductionsToggle">Disable SSS, Pagibig, PhilHealth</label>
    </div>

    <!-- Deductions -->
    <div id="govDeductions">
        <p class="for-indent"><strong>SSS (5%): </strong><span id="sss">0.00</span></p>
        <p class="for-indent"><strong>Pagibig (2%): </strong><span id="pagibig">0.00</span></p>
        <p class="for-indent"><strong>PhilHealth (2.5%): </strong><span id="philhealth">0.00</span></p>
    </div>
    <div>
        <span class="for-indent">Cater: </span>
        <input 
            id="cater_deductions"
            class="add-payslip-for-num-special" 
            type="number" 
            name="cater_deductions" 
            value="<?= htmlspecialchars($payroll_data['cater_deductions']??0) ?>"
        >
    </div>
    <div>
        <span class="for-indent">Advance: </span>
        <input 
            id="advance_deductions"
            class="add-payslip-for-num-special" 
            type="number" 
            name="advance_deductions" 
            value="<?= htmlspecialchars($payroll_data['advance_deductions']??0) ?>"
        >
    </div>
    <p style="margin-top: 10px;"><strong>Total Deductions: </strong><span id="total_deductions">0.00</span></p>

    <!-- Net Pay -->
    <div class="for-pay">
        <label>Net Pay:</label>
        <span id="net_pay">0.00</span>
    </div>

    <!-- Hidden inputs for form submit -->
    <input type="hidden" id="hidden_total_amount" name="total_amount">
    <input type="hidden" id="hidden_sss" name="sss">
    <input type="hidden" id="hidden_pagibig" name="pagibig">
    <input type="hidden" id="hidden_philhealth" name="philhealth">
    <input type="hidden" id="hidden_total_deductions" name="total_deductions">
    <input type="hidden" id="hidden_net_pay" name="net_pay">

    <button class="save-edit-button" type="submit">Update</button>
</form>
