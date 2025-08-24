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

    echo "<html><head><title>Batch Payslip</title><style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .payslip { margin-bottom: 40px; padding: 20px; border: 1px solid #ccc; }
        .sub-details { display: flex; gap: 50px; margin-bottom: 10px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: center; }
        .for-indent { margin-left: 20px; }
        @media print { .payslip { page-break-after: always; } }
    </style></head><body>";

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

    // Loop through each selected payroll_id
    foreach ($ids as $payroll_id) {
        $payroll_id = (int)$payroll_id;
        $stmt->bind_param("i", $payroll_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if (!$row) continue;

        echo "<div class='payslip'>";
        echo "<h2>AI Korean Buffet Restaurant</h2>";
        echo "<h4>MH del Pilar Burnham Legarda road, Baguio City, Philippines</h4>";

        // Employee info
        echo "<div class='sub-details'>
                <div>
                    <p><strong>ID:</strong> " . htmlspecialchars($row['employee_id']) . "</p>
                    <p><strong>Name:</strong> " . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</p>
                </div>
                <div>
                    <p><strong>Position:</strong> " . htmlspecialchars($row['position']) . "</p>
                    <p><strong>Status:</strong> " . htmlspecialchars($row['status']) . "</p>
                </div>
                <div>
                     <p><strong>Year:</strong> " . htmlspecialchars($row['year']) . "</p>
                    <p><strong>Payroll Period:</strong> " . htmlspecialchars($row['week'] ?? $row['payroll_id']) . "</p>
                </div>
              </div>";

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

        // Payroll Table
        echo "<table><thead>
                <tr>
                    <th>Category</th>
                    <th>Hours/Days</th>
                    <th>Rate</th>
                    <th>Total</th>
                </tr>
              </thead><tbody>";

        $grossPay = 0;
        foreach ($categories as $cat) {
            $hours = $row[$cat['hours']] ?? 0;
            $rate  = $row[$cat['rate']] ?? 0;
            $total = $hours * $rate;
            $grossPay += $total;

            // Show all categories
            echo "<tr>
                    <td>" . htmlspecialchars($cat['label']) . "</td>
                    <td>" . $hours . "</td>
                    <td>" . formatCurrency($rate) . "</td>
                    <td>" . formatCurrency($total) . "</td>
                  </tr>";
        }

        // Gross Pay row
        echo "<tr style='font-weight:bold; background:#f0f0f0;'>
                <td colspan='3' style='text-align:right;'>Gross Pay</td>
                <td>" . formatCurrency($grossPay) . "</td>
              </tr>";
        echo "</tbody></table>";

        // Deductions
        $sss        = $grossPay * 0.05;
        $pagibig    = $grossPay * 0.02;
        $philhealth = $grossPay * 0.025;
        $caterDeduction   = $row['cater_deductions'] ?? 0;
        $advanceDeduction = $row['advance_deductions'] ?? 0;
        $totalDeductions  = $sss + $pagibig + $philhealth + $caterDeduction + $advanceDeduction;

        echo "<h3>Deductions</h3>";
        echo "<p class='for-indent'><strong>SSS (5%): </strong>" . formatCurrency($sss) . "</p>";
        echo "<p class='for-indent'><strong>Pagibig (2%): </strong>" . formatCurrency($pagibig) . "</p>";
        echo "<p class='for-indent'><strong>PhilHealth (2.5%): </strong>" . formatCurrency($philhealth) . "</p>";
        echo "<p class='for-indent'><strong>Cater Deduction: </strong>" . formatCurrency($caterDeduction) . "</p>";
        echo "<p class='for-indent'><strong>Advance Deduction: </strong>" . formatCurrency($advanceDeduction) . "</p>";
        echo "<hr>";
        echo "<p class='for-indent'><strong>Total Deductions: </strong>" . formatCurrency($totalDeductions) . "</p>";
        echo "<p><strong>Net Pay: </strong>" . formatCurrency($grossPay - $totalDeductions) . "</p>";
        echo "</div>";
    }

    $stmt->close();
    echo "<script>window.onload = () => window.print();</script>";
    echo "</body></html>";
    $conn->close();
}
?>
