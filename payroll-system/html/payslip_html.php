<?php
// add_payslip_form.php
$data = include('../php/payslip.php');
$employee = $data['employee'];
$payrollInfo = $data['payrollInfo'];
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
<?php if ($payrollInfo): ?>
  <p><strong>Year:</strong> <?= htmlspecialchars($payrollInfo['year']) ?></p>
  <p><strong>Payroll Period:</strong> <?= htmlspecialchars($payrollInfo['week'] ?? $payrollInfo['payroll_id']) ?></p>
<?php else: ?>
  <p><em>No payroll record found.</em></p>

<?php endif; ?>
  
<table border="1" cellpadding="8">
    <thead>
        <tr>
            <th>Category</th>
            <th>Hours/Days</th>
            <th>Rate</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $grossPay = 0; // accumulator

        foreach ($categories as $cat): 
            $hours = $payrollInfo[$cat['hours']] ?? 0;
            $rate  = $employee[$cat['rate']] ?? 0; // rates from employee_info_and_rates
            $total = $hours * $rate;
            $grossPay += $total; // add to gross pay
        ?>
        <tr>
            <td><?= htmlspecialchars($cat['label']) ?></td>
            <td><?= $hours ?></td>
            <td><?= formatCurrency($rate) ?></td>
            <td><?= formatCurrency($total) ?></td>
        </tr>
        <?php endforeach; ?>

        <!-- Gross Pay Row -->
        <tr style="font-weight: bold; background-color: #f0f0f0;">
            <td colspan="3" style="text-align: right;">Gross Pay</td>
            <td><?= formatCurrency($grossPay) ?></td>
        </tr>
    </tbody>
</table>

<label>Deductions:</label>
<?php
    $grossPay = $grossPay ?? 0; // just in case
    $sss        = $grossPay * 0.05;
    $pagibig    = $grossPay * 0.02;
    $philhealth = $grossPay * 0.025;

    // from payroll_transactions
    $caterDeduction   = $payrollInfo['cater_deductions']   ?? 0;
    $advanceDeduction = $payrollInfo['advance_deductions'] ?? 0;

    $totalDeductions = $sss + $pagibig + $philhealth + $caterDeduction + $advanceDeduction;
?>
<p class="for-indent"><strong>SSS (5%): </strong><?= formatCurrency($sss) ?></p>
<p class="for-indent"><strong>Pagibig (2%): </strong><?= formatCurrency($pagibig) ?></p>
<p class="for-indent"><strong>PhilHealth (2.5%): </strong><?= formatCurrency($philhealth) ?></p>
<p class="for-indent"><strong>Cater Deduction: </strong><?= formatCurrency($caterDeduction) ?></p>
<p class="for-indent"><strong>Advance Deduction: </strong><?= formatCurrency($advanceDeduction) ?></p>
<hr>
<p class="for-indent"><strong>Total Deductions: </strong><?= formatCurrency($totalDeductions) ?></p>
<p><strong>Net Pay: </strong><?= formatCurrency($grossPay - $totalDeductions) ?></p>
