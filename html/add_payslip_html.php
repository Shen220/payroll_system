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
<select name="payroll_id" required>
<option value="">-- Select Payroll Period --</option>
<?php while($row=$payroll_result->fetch_assoc()): ?>
    <option value="<?= htmlspecialchars($row['payroll_id']) ?>"><?= htmlspecialchars($row['week']) ?></option>
<?php endwhile; ?>
</select><br><br>

<?php
$rate_labels = [
'num_of_days_for_rate_1'=>'Number of Days for Rate 1',
'num_of_days_for_rate_2'=>'Number of Days for Rate 2',
'num_of_days_for_rate_3'=>'Number of Days for Rate 3',
'num_of_days_for_rate_4'=>'Number of Days for Rate 4',
'num_of_hours_for_rate_5'=>'Number of Hours for Rate 5',
'num_of_hours_for_rate_6'=>'Number of Hours for Rate 6',
'num_of_hours_for_rate_7'=>'Number of Hours for Rate 7',
'num_of_hours_for_rate_8'=>'Number of Hours for Rate 8',
'num_of_days_for_rate_9'=>'Number of Days for Rate 9'
];
foreach($rate_labels as $name=>$label):
?>
<label><?= htmlspecialchars($label) ?>:</label>
<input type="number" name="<?= htmlspecialchars($name) ?>" min="0" value="0"><br><br>
<?php endforeach; ?>

<button type="submit">Submit</button>
</form>
