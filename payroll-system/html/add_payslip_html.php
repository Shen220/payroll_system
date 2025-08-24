<?php
// add_payslip_form.php
$data = include('../php/add_payslip.php');
$employee = $data['employee'];
$payroll_result = $data['payroll_result'];
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

    <label>Year:</label>
    <select name="year" required>
        <option value="">-- Select Year --</option>
        <?php for($y=date("Y"); $y>=2000; $y--): ?>
            <option value="<?= $y ?>"><?= $y ?></option>
        <?php endfor; ?>
    </select><br><br>

    <label>Payroll Period:</label>
    <select name="payroll_id" required>
        <option value="">-- Select Payroll Period --</option>
        <?php while($row=$payroll_result->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($row['payroll_id']) ?>"><?= htmlspecialchars($row['week']) ?></option>
        <?php endwhile; ?>
    </select><br><br>

<?php foreach ($rate_labels as $name => $data): 
    $rate_key = $data['key'];
    $label = $data['label'];
    $multiplier = $employee[$rate_key] ?? 0;
?>
    <div class="rate-box">
        <label><?= htmlspecialchars($label) ?>:</label>
        <input 
            class="add-payslip-for-num"
            type="number" 
            name="<?= htmlspecialchars($name) ?>" 
            id="input_<?= $rate_key ?>" 
            data-multiplier="<?= htmlspecialchars($multiplier) ?>" 
            min="0" 
            value="0">
        <p><strong>Result:</strong> <span id="result_<?= $rate_key ?>">0.00</span></p>
    </div>
<?php endforeach; ?>

<div class="for-pay">
    <label>Gross Pay:</label>
    <span id="total_amount">0.00</span>
</div>

<div>
  <label>Deductions:</label>
  <p class="for-indent"><strong>SSS (5%): </strong><span id="sss">0.00</span></p>
  <p class="for-indent"><strong>Pagibig (2%): </strong><span id="pagibig">0.00</span></p>
  <p class="for-indent"><strong>PhilHealth (2.5%): </strong><span id="philhealth">0.00</span></p>
  <div>
    <span class="for-indent">Cater: </span>
    <input style="margin-left: 26.5px;" class="add-payslip-for-num-special" type="number" id="cater_deductions" name="cater_deductions" value="<?= htmlspecialchars($_POST['cater_deductions'] ?? 0) ?>">
  </div>
  <div>
    <span class="for-indent">Advance: </span>
    <input class="add-payslip-for-num-special" type="number" id="advance_deductions" name="advance_deductions" value="<?= htmlspecialchars($_POST['advance_deductions'] ?? 0) ?>">
  </div>
  <p style="margin-top: 10px;"><strong>Total Deductions: </strong><span id="total_deductions">0.00</span></p>
</div>

<div class="for-pay">
  <label>Net Pay:</label>
  <span id="net_pay">0.00</span>
</div>
    <button class="save-edit-button" type="submit">Submit</button>
</form>
