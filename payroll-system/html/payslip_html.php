<?php
// add_payslip_form.php
$data = include('../php/payslip.php');
$employee = $data['employee'];
$payrollInfo = $data['payrollInfo'];
$message = $data['message'];
?>

<div class="payslip-details">
    <p class="payslip-p-special">Restocafe & Event Catering Services</strong></p>
    <p class="payslip-p">Burnham-Legarda, Baguio City</p>
    <div class="payslip-sub-details">
        <?php if ($payrollInfo): ?>
        <p class="payslip-text-red"><?= htmlspecialchars($payrollInfo['week'] ?? $payrollInfo['payroll_id']) ?></p>
        <p class="payslip-text-red" style="margin-left: -78px;">,&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($payrollInfo['year']) ?></p>
        <?php else: ?>
        <p class="payslip-text"><em>No payroll record found.</em></p>
        <?php endif; ?>
    </div>
    <div class="payslip-sub-details">
        <p class="payslip-text"><strong>Name:</strong> <?= mb_strtoupper($employee['first_name'].' '.$employee['last_name']) ?></p>
        <p class="payslip-text"><strong>Position:</strong> <?= mb_strtoupper($employee['position']) ?></p>
    </div>
</div>

<?php if($message): ?>
    <p class="payslip-text message-success"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>
  
<table class="modal-body payslip-table">
    <thead class="payslip-thead-special">
        <tr class="payslip-tr">
            <th class="payslip-th-red"></th>
            <th class="payslip-th-red">Rate</th>
            <th class="payslip-th-red">Hours/Days</th>
            <th class="payslip-th-red">Total</th>
        </tr>
    </thead>
    <tbody class="payslip-tbody">
        <?php 
        $grossPay = 0; // accumulator

        foreach ($categories as $cat): 
            $hours = $payrollInfo[$cat['hours']] ?? 0;
            $rate  = $employee[$cat['rate']] ?? 0; // rates from employee_info_and_rates
            $total = $hours * $rate;
            $grossPay += $total; // add to gross pay
        ?>
        <tr class="payslip-tr">
            <td class="payslip-td-column-one"><?= htmlspecialchars($cat['label']) ?></td>
            <td class="payslip-td"><?= formatCurrency($rate) ?></td>
            <td class="payslip-td"><?= $hours ?></td>
            <td class="payslip-td"><?= formatCurrency($total) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Gross Pay Row -->
<table class="payslip-table-total">
    <tr class="total-row-underline">
        <td class="payslip-td"><strong>Gross Pay</strong></td>
        <td class="payslip-td"><?= formatCurrency($grossPay) ?></td>
    </tr>
</table>
        
<?php

    $grossPay = $grossPay ?? 0; // just in case

// 🔹 Apply Gov deductions only if payroll_id is odd
if (!empty($payrollInfo['payroll_id']) && ($payrollInfo['payroll_id'] % 2 === 1)) {
    $sss        = $grossPay * 0.05;
    $pagibig    = $grossPay * 0.02;
    $philhealth = $grossPay * 0.025;
} else {
    $sss = $pagibig = $philhealth = 0;
}

// From payroll_transactions
$caterDeduction   = $payrollInfo['cater_deductions']   ?? 0;
$advanceDeduction = $payrollInfo['advance_deductions'] ?? 0;

$totalDeductions = $sss + $pagibig + $philhealth + $caterDeduction + $advanceDeduction;

?>

<table class="payslip-table" style="margin-top: 15px;">
    <thead class="payslip-thead-special">
        <tr class="payslip-tr-special">
            <th class="payslip-th"><i>Deductions</i></th>
            <th class="payslip-th"></th>
        </tr>
    </thead>
    <tbody class="payslip-tbody">
        <tr class="payslip-tr-second">
            <td class="payslip-td-column-one"">SSS (5%)</td>
            <td class="payslip-text"><?= formatCurrency($sss) ?></td>
        </tr>
        <tr class="payslip-tr-second">
            <td class="payslip-td-column-one">Pagibig (2%)</td>
            <td class="payslip-text"><?= formatCurrency($pagibig) ?></td>
        </tr>
        <tr class="payslip-tr-second">
            <td class="payslip-td-column-one">PhilHealth (2.5%)</td>
            <td class="payslip-text"><?= formatCurrency($philhealth) ?></td>
        </tr>
        <tr class="payslip-tr-second">
            <td class="payslip-td-column-one">Cater Deduction</td>
            <td class="payslip-text"><?= formatCurrency($caterDeduction) ?></td>
        </tr>
        <tr class="payslip-tr-second">
            <td class="payslip-td-column-one">Advance Deduction</td>
            <td class="payslip-text"<?= formatCurrency($advanceDeduction) ?></td>
        </tr>
    </tbody>
</table>

<table class="payslip-table-total">
    <tr class="total-row">
        <td class="payslip-td"><strong>Total Deductions</strong></td>
        <td class="payslip-td"><?= formatCurrency($totalDeductions) ?></td>
    </tr>
    <tr class="total-row-underline">
        <td class="payslip-td"><strong>Net Pay</strong></td>
        <td class="payslip-td"><?= formatCurrency($grossPay - $totalDeductions) ?></td>
    </tr>
</table>    

<br>

<hr class="broken-line">

<div class="payslip-details">
    <p class="payslip-p-ordinary">Acknowledgement</strong></p>
    <div class="payslip-sub-details">
        <?php if ($payrollInfo): ?>
        <p class="payslip-text"><?= htmlspecialchars($payrollInfo['week'] ?? $payrollInfo['payroll_id']) ?></p>
        <p class="payslip-text" style="margin-left: -78px;">,&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($payrollInfo['year']) ?></p>
        <?php else: ?>
        <p class="payslip-text"><em>No payroll record found.</em></p>
        <?php endif; ?>
    </div>
    <div class="payslip-sub-details-pp">
        <p class="payslip-text"><strong>Name:</strong> <?= mb_strtoupper($employee['first_name'].' '.$employee['last_name']) ?></p>
    </div>
</div>

<table class="payslip-table-total" style="margin-top: 40px;">
    <tr class="total-row">
        <td class="payslip-td"><strong>Gross Pay</strong></td>
        <td class="payslip-td"><?= formatCurrency($grossPay) ?></td>
    </tr>
    <tr class="total-row">
        <td class="payslip-td"><strong>Total Deductions</strong></td>
        <td class="payslip-td"><?= formatCurrency($totalDeductions) ?></td>
    </tr>
    <tr class="total-row-red">
        <td class="payslip-td"><strong>Net Pay</strong></td>
        <td class="payslip-td"><?= formatCurrency($grossPay - $totalDeductions) ?></td>
    </tr>
</table>

<br>

<table class="payslip-table-total">
    <tr class="total-row-special">
        <td class="payslip-td"><strong>Recieved By:</strong></td>
        <td class="payslip-td"><span>________________</span></td>
    </tr>
    <tr class="total-row-special">
        <td class="payslip-td"><strong>Date:</strong></td>
        <td class="payslip-td"><span>________________</span></td>
    </tr>
</table>