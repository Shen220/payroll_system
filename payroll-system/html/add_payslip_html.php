<?php
// add_payslip_form.php
$data = include('../php/add_payslip.php');
$employee = $data['employee'];
$payroll_result = $data['payroll_result'];
$message = $data['message'];
?>

<div>
    <h4>Employee Information</h4>
    <p><strong>ID:</strong> <?= htmlspecialchars($employee['employee_id']) ?></p>
    <p><strong>Name:</strong> <?= htmlspecialchars($employee['first_name'].' '.$employee['last_name']) ?></p>
    <p><strong>Position:</strong> <?= htmlspecialchars($employee['position']) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($employee['status']) ?></p>
</div>

<?php if($message): ?>
    <p style="color:green;"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form id="payslipForm" method="post">
    <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee['employee_id']) ?>">

    <label>Year:</label>
    <select name="year" required>
        <option value="">-- Select Year --</option>
        <?php for($y=date("Y"); $y>=2000; $y--): ?>
            <option value="<?= $y ?>"><?= $y ?></option>
        <?php endfor; ?>
    </select><br><br>

   <label>Payroll Period:</label>
<select name="payroll_id" id="payrollPeriod" required>
    <option value="">-- Select Payroll Period --</option>
    <?php while($row=$payroll_result->fetch_assoc()): ?>
        <option value="<?= htmlspecialchars($row['payroll_id']) ?>">
            <?= htmlspecialchars($row['week']) ?>
        </option>
    <?php endwhile; ?>
</select>
<br><br>

<?php foreach ($rate_labels as $name => $data): 
    $rate_key = $data['key'];
    $label = $data['label'];
    $multiplier = $employee[$rate_key] ?? 0;
?>
    <div class="rate-box">
        <label><?= htmlspecialchars($label) ?>:</label>
        <input 
            type="number" 
            name="<?= htmlspecialchars($name) ?>" 
            id="input_<?= $rate_key ?>" 
            data-multiplier="<?= htmlspecialchars($multiplier) ?>" 
            min="0" 
            value="0">
        <p><strong>Result:</strong> <span id="result_<?= $rate_key ?>">0.00</span></p>
    </div>
<?php endforeach; ?>

<div>
    <label><strong>GROSS PAY:</strong></label>
    <span id="total_amount">0.00</span>
</div>


<div>
    <label><strong>DEDUCTIONS:</strong></label>

    <!-- wrap the auto (gov) deductions -->
<div id="govDeductions">
    <label><strong>DEDUCTIONS:</strong></label><br>
    SSS: <span id="sss">0.00</span><br>
    Pagibig: <span id="pagibig">0.00</span><br>
    PhilHealth: <span id="philhealth">0.00</span><br>
</div>

    Cater: <input type="number" id="cater_deductions" name="cater_deductions" value="0"><br>
    Advance: <input type="number" id="advance_deductions" name="advance_deductions" value="0"><br>

    <strong>Total Deductions: <span id="total_deductions">0.00</span></strong>
</div>

<div>
    <label><strong>NET PAY:</strong></label>
    <span id="net_pay">0.00</span>
</div>

<!-- Hidden inputs to send computed values to PHP -->
<input type="hidden" id="hidden_total_amount" name="total_amount">
<input type="hidden" id="hidden_sss" name="sss">
<input type="hidden" id="hidden_pagibig" name="pagibig">
<input type="hidden" id="hidden_philhealth" name="philhealth">
<input type="hidden" id="hidden_total_deductions" name="total_deductions">
<input type="hidden" id="hidden_net_pay" name="net_pay">
</div>


    <button type="submit">Submit</button>
</form>
