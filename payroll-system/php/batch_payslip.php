<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = json_decode($_POST['payroll_ids'] ?? '[]', true);

    include('db.php');

    if (!$ids || !is_array($ids)) {
        die("Invalid input.");
    }

    function formatCurrency($amount) {
        return '₱ ' . number_format((float)$amount, 2);
    }

    // Prepare the statement once
    $stmt = $conn->prepare("
        SELECT ei.*, pt.*, pd.week
        FROM employee_info_and_rates ei
        JOIN payroll_transactions pt ON ei.employee_id = pt.employee_id
        LEFT JOIN payroll_dates pd ON pt.payroll_id = pd.payroll_id
        WHERE pt.payroll_id = ?
    ");

    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    ?>

    <!DOCTYPE html>
    <html>
    <head>
        <title>Batch Payslip</title>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="/payroll-system/css/batch_payslip.css"> <!-- external css -->
    </head>
    <body>
        
    <?php
    // Loop through each selected payroll_id
    foreach ($ids as $payroll_id) {
        $payroll_id = (int)$payroll_id;
        $stmt->bind_param("i", $payroll_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if (!$row) continue;

        // Define categories
        $categories = [
            ['label' => 'Daily Minimum Wage', 'hours' => 'num_of_days_for_rate_1', 'rate' => 'rate_1_daily_minimum_wage'],
            ['label' => 'Sunday Rest Day', 'hours' => 'num_of_days_for_rate_2', 'rate' => 'rate_2_sunday_rest_day'],
            ['label' => 'Legal Holiday', 'hours' => 'num_of_days_for_rate_3', 'rate' => 'rate_3_legal_holiday'],
            ['label' => 'Special Holiday', 'hours' => 'num_of_days_for_rate_4', 'rate' => 'rate_4_special_holiday'],
            ['label' => 'Regular Overtime/Hour', 'hours' => 'num_of_hours_for_rate_5', 'rate' => 'rate_5_regular_overtime_perhour'],
            ['label' => 'Special Overtime/Hour', 'hours' => 'num_of_hours_for_rate_6', 'rate' => 'rate_6_special_overtime_perhour'],
            ['label' => 'Special Holiday Overtime/Hour', 'hours' => 'num_of_hours_for_rate_7', 'rate' => 'rate_7_special_holiday_overtime_perhour'],
            ['label' => 'Regular Holiday Overtime/Hour', 'hours' => 'num_of_hours_for_rate_8', 'rate' => 'rate_8_regular_holiday_overtime_perhour'],
            ['label' => 'Cater', 'hours' => 'num_of_days_for_rate_9', 'rate' => 'rate_9_cater']
        ];

        // Calculate gross
        $grossPay = 0;
        ?>

        <div class="payslip">
            <div class="payslip-details">
                <p class="payslip-p-special"><strong>Restocafe & Event Catering Services</strong></p>
                <p class="payslip-p">Burnham-Legarda, Baguio City</p>
                <div class="sub-details">
                    <div class="payslip-sub-details-special">
                        <p><strong> <?= htmlspecialchars($row['week'] ?? $row['payroll_id']) ?></strong></p>
                        <p><strong>,&nbsp;&nbsp;&nbsp; <?= htmlspecialchars($row['year']) ?></strong></p>
                    </div>
                    <div class="payslip-sub-details">
                        <p><strong>Name:</strong> <?= mb_strtoupper($row['first_name'] . ' ' . $row['last_name']) ?></p>
                        <p><strong>Position:</strong> <?= mb_strtoupper($row['position']) ?></p>
                    </div>
                </div>

                <!-- Payroll Table -->
                <table class="payslip-table">
                    <thead class="payslip-thead-special">
                        <tr class="payslip-tr">
                            <th class="payslip-th"></th>
                            <th class="payslip-th"><strong>Rate</strong></th>
                            <th class="payslip-th"><strong>Hours/Days</strong></th>
                            <th class="payslip-th"><strong>Total</strong></th>
                        </tr>
                    </thead>
                    <tbody class="payslip-tbody">
                    <?php 
                    foreach ($categories as $cat): 
                        $hours = $payrollInfo[$cat['hours']] ?? 0;
                        $rate  = $employee[$cat['rate']] ?? 0; // rates from employee_info_and_rates
                        $total = $hours * $rate;
                        $grossPay += $total; // add to gross pay
                    ?>
                    <tr class="payslip-tr-center">
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
                    <tr class="total-row">
                        <td class="payslip-td"><strong>Gross Pay:</strong></td>
                        <td class="payslip-td"><?= formatCurrency($grossPay) ?></td>
                    </tr>
                </table>

                <!-- Deductions -->
                <?php
                $sss        = $grossPay * 0.05;
                $pagibig    = $grossPay * 0.02;
                $philhealth = $grossPay * 0.025;
                $caterDeduction   = $row['cater_deductions'] ?? 0;
                $advanceDeduction = $row['advance_deductions'] ?? 0;
                $totalDeductions  = $sss + $pagibig + $philhealth + $caterDeduction + $advanceDeduction;
                ?>

                <table class="payslip-table" style="margin-top: 15px;">
                    <thead class="payslip-thead-special">
                        <tr class="payslip-tr-special">
                            <th class="payslip-th"><i>Deductions:</i></th>
                            <th class="payslip-th"></th>
                        </tr>
                        <tr></tr>
                    </thead>
                    <tbody class="payslip-tbody">
                        <tr class="payslip-tr-second">
                            <td class="payslip-td-column-one"">SSS (5%)</td>
                            <td class="payslip-text"><?= formatCurrency($sss) ?></td>
                        </tr>
                        <tr class="payslip-tr-second">
                            <td class="payslip-td-column-one"">Pagibig (2%)</td>
                            <td class="payslip-text"><?= formatCurrency($pagibig) ?></td>
                        </tr>
                        <tr class="payslip-tr-second">
                            <td class="payslip-td-column-one"">PhilHealth (2.5%)</td>
                            <td class="payslip-text"><?= formatCurrency($philhealth) ?></td>
                        </tr>
                        <tr class="payslip-tr-second">
                            <td class="payslip-td-column-one"">Cater Deduction</td>
                            <td class="payslip-text"><?= formatCurrency($caterDeduction) ?></td>
                        </tr>
                        <tr class="payslip-tr-second">
                            <td class="payslip-td-column-one"">Advance Deduction</td>
                            <td class="payslip-text"<?= formatCurrency($advanceDeduction) ?></td>
                        </tr>
                    </tbody>
                </table>

                <table class="payslip-table-total">
                    <tr class="total-row">
                        <td class="payslip-td"><strong>Total Deductions:</strong></td>
                        <td class="payslip-td" style="text-decoration: underline;"><?= formatCurrency($totalDeductions) ?></td>
                    </tr>
                    <tr class="total-row">
                        <td class="payslip-td"><strong>Net Pay:</strong></td>
                        <td class="payslip-td"><?= formatCurrency($grossPay - $totalDeductions) ?></td>
                    </tr>
                </table>    

                <br>

                <hr class="broken-line">

                <div class="payslip-details">
                    <p class="payslip-p" style="margin-bottom: -6px;">Acknowledgement</strong></p>
                    <div class="payslip-sub-details">
                        <?php if ($row): ?>
                            <p class="payslip-text"><?= htmlspecialchars($row['week'] ?? $row['payroll_id']) ?></p>
                            <p class="payslip-text" style="margin-left: -78px;">,&nbsp;&nbsp;&nbsp;<?= htmlspecialchars($row['year']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="payslip-sub-details-pp">
                        <p><strong>Name:</strong> <?= mb_strtoupper($row['first_name'] . ' ' . $row['last_name']) ?></p>
                    </div>
                </div>

                <table class="payslip-table-total" style="margin-top: 40px;">
                    <tr class="total-row">
                        <td class="payslip-td"><strong>Gross Pay</strong></td>
                        <td class="payslip-td" style="text-decoration: underline;"><?= formatCurrency($grossPay) ?></td>
                    </tr>
                    <tr class="total-row">
                        <td class="payslip-td"><strong>Total Deductions</strong></td>
                        <td class="payslip-td" style="text-decoration: underline;"><?= formatCurrency($totalDeductions) ?></td>
                    </tr>
                    <tr class="total-row-red">
                        <td class="payslip-td"><strong>Net Pay</strong></td>
                        <td class="payslip-td"><strong><?= formatCurrency($grossPay - $totalDeductions) ?></strong></td>
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

            </div>
        </div>
        
        <?php
    } // end foreach

    $stmt->close();
    $conn->close();
    ?>
    <script>
        window.onload = () => {
            window.print();
        };

        window.onafterprint = () => {
            window.close(); // closes current tab
            if (window.opener) {
                window.opener.location.href = "http://localhost/payroll-system/html/dashboard_html.php";
            }
        };
    </script>


    </body>
    </html>
<?php
}
?>
