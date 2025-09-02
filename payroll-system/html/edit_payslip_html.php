<?php
$data = include('../php/edit_payslip.php'); 
$employee = $data['employee'];
$payroll_result1 = $data['payroll_result1'];
$rate_labels = $data['rate_labels'];
$payroll_data = $data['payroll_data'];
$message = $data['message'];

?>

<div class="add-payslip-details">
    <div class="sub-details">
        <p><strong>ID:</strong> <?= mb_strtoupper(htmlspecialchars($employee['employee_id'])) ?></p>
        <p><strong>Name:</strong> <?= mb_strtoupper(htmlspecialchars($employee['first_name'].' '.$employee['last_name'])) ?></p>
    </div>
    <div class="sub-details">
        <p><strong>Position:</strong> <?= mb_strtoupper(htmlspecialchars($employee['position'])) ?></p>
        <p><strong>Status:</strong> <?= mb_strtoupper(htmlspecialchars($employee['status'])) ?></p>
    </div>
</div>

<form id="payslipForm" method="post" action="../php/edit_payslip.php">
    <input type="hidden" name="employee_id" value="<?= htmlspecialchars($employee['employee_id']) ?>">
    <input type="hidden" name="old_payroll_id" value="<?= htmlspecialchars($payroll_data['payroll_id']) ?>">

    <!-- Year -->
    <label>Year:</label>
    <select name="year" required>
        <option value="">-- Select Year --</option>
        <?php for($y=date("Y"); $y>=2000; $y--): ?>
            <option value="<?= $y ?>" <?= ($payroll_data['year']??'')==$y?'selected':'' ?>>
                <?= $y ?>
            </option>
        <?php endfor; ?>
    </select>

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

<!-- RATES TABLE -->
<table style="width:100%; table-layout:fixed; border-collapse:collapse; margin-top: 10px;">
  <colgroup>
    <col style="width: 40%;">
    <col style="width: 30%;">
    <col style="width: 30%;">
  </colgroup>

  <thead>
    <tr>
      <th>Rates</th>
      <th>Hours/Days</th>
      <th>Result</th>
    </tr>
  </thead>
  <!-- Mukhang walang problema sa HTML, nasa PHP -->
  <tbody>
    <?php foreach ($rate_labels as $name => $data): 
        $rate_key = $data['key'];
        $label = $data['label'];
        $multiplier = $employee[$rate_key] ?? 0;
        $value = $payroll_data[$name] ?? 0;
    ?>
      <tr>
        <td>
          <label for="input_<?= $rate_key ?>"><?= htmlspecialchars($label) ?>:</label>
        </td>
        <td>
          <input 
            class="add-payslip-for-num"
            type="number" 
            name="<?= htmlspecialchars($name) ?>" 
            id="input_<?= $rate_key ?>" 
            data-multiplier="<?= htmlspecialchars($multiplier) ?>" 
            min="0" 
            value="<?= htmlspecialchars($value) ?>"
            style="width: 90%;">
        </td>
        <td>
          <span>₱</span>
          <span id="result_<?= $rate_key ?>">0.00</span>
        </td>
      </tr>
    <?php endforeach; ?>

    <!-- Gross Pay -->
    <tr style="font-weight:bold; background:#f9f9f9;">
      <td>Gross Pay:</td>
      <td></td>
      <td>
        <strong>₱</strong>
        <span id="total_amount">0.00</span>
      </td>
    </tr>
  </tbody>
</table>


<!-- DEDUCTIONS -->
<div style="margin-top: 20px;">
    <label>
        <input 
    type="checkbox" 
    id="disableGovDeductionsToggle" 
    name="disableGovDeductionsToggle"
    style="margin-right: 10px; transform: scale(1.2);"
    <?= (isset($payroll_data['payroll_id']) && ($payroll_data['payroll_id'] % 2 == 1)) ? 'checked' : '' ?>
>Government Deductions Disabled
    </label>
</div>

<table id="govDeductions" border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; width: 100%;  margin-top: 10px;">
  <thead>
    <tr style="background-color: #f2f2f2;">
      <th>Deductions</th>
      <th>Result</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>SSS (5%):</td>
      <td id="sss"><span>₱ </span>0.00</td>
    </tr>
    <tr>
      <td>Pagibig (2%):</td>
      <td id="pagibig">0.00</td>
    </tr>
    <tr>
      <td>PhilHealth (2.5%):</td>
      <td id="philhealth">0.00</td>
    </tr>
    <tr>
      <td>Cater:</td>
      <td>
        <input type="number" 
               id="cater_deductions" name="cater_deductions" 
               value="<?= htmlspecialchars($payroll_data['cater_deductions'] ?? 0) ?>"
               style="width: 100px; padding: 3px;">
      </td>
    </tr>
    <tr>
      <td>Advance:</td>
      <td>
        <input type="number" 
               id="advance_deductions" name="advance_deductions" 
               value="<?= htmlspecialchars($payroll_data['advance_deductions'] ?? 0) ?>"
               style="width: 100px; padding: 3px;">
      </td>
    </tr>
    <!-- Total Deductions -->
    <tr style="font-weight:bold; background:#f9f9f9;">
      <td>Total Deductions:</td>
      <td>
        <strong>₱</strong>
        <span id="total_deductions">0.00</span>
      </td>
    </tr>
  </tbody>
</table>

<table id="add-payslip-data" border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; width: 100%;">
    <tr id="add-payslip-row">
        <td style="color: green; font-weight: bold;">Net Pay:</td>
        <td>
        <strong style="color: green; font-weight: bold;">₱</strong>
        <span id="net_pay" style="color: green; font-weight: bold;">0.00
        </span>
        </td>
    </tr>
</table>


<?php if($message): ?>
    <p style="color:green;"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<button class="save-edit-button" type="submit">Update</button>
</form>
