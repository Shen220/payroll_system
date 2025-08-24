<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = json_decode($_POST['payroll_ids'] ?? '[]', true);

    // Connect to DB
    include('db.php');

    if (!$ids || !is_array($ids)) {
        die("Invalid input.");
    }

    function formatCurrency($amount) {
        return '₱ ' . number_format((float)$amount, 2);
    }

    echo "<html><head><title>Batch Payslip</title><style>
        .payslip {
            margin-bottom: 40px;
            padding: 20px;
            border: 1px solid #ccc;
        }
        @media print {
            body {
                font-family: Arial, sans-serif;
                page-break-after: always;
            }
        }
    </style></head><body>";

    $stmt = $conn->prepare("
        SELECT ei.*, ep.*, pc.*
        FROM employee_info_and_rates ei
        JOIN employee_payroll ep ON ei.employee_id = ep.employee_id
        JOIN payroll_computation pc ON ep.payroll_id = pc.payroll_id
        WHERE ep.payroll_id = ?
    ");

    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    foreach ($ids as $payroll_id) {
        $payroll_id = (int)$payroll_id;
        $stmt->bind_param("i", $payroll_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (!$row) {
            continue;
        }
        

  
        echo "<div class='payslip'>";
        echo "<h2>AI Korean Buffet Restaurant</h2>";
        echo "<h4>MH del Pilar Burnham Legarda road, Baguio City, Philippines</h4>";

        echo "<p><strong>ID:</strong> " . htmlspecialchars($row['employee_id']) . "</p>";
        echo "<p><strong>Name:</strong> " . mb_strtoupper(htmlspecialchars($row['last_name'])) . ", " . htmlspecialchars($row['first_name']) . "</p>";
        echo "<p><strong>Position:</strong> " . htmlspecialchars($row['position']) . "</p>";
        echo "<p><strong>Status:</strong> " . htmlspecialchars($row['status']) . "</p>";

        echo "<h3>Work Details</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>
                <tr>
                    <td></td>
                    <td>Rates</td>
                    <td>Days/Hours</td>
                    <td>Total</td>
                </tr>";

     if (!is_null($row['w1_daily_minimum_wage']) && $row['w1_daily_minimum_wage'] != 0) {
    echo "<tr class='ppayslip-tr'>
            <td class='ppayslip-td'>Daily Minimum Wage</td>
            <td class='ppayslip-td'>₱ " . number_format((float)$row['w1'] / max((float)$row['w1_daily_minimum_wage'], 1), 2) . "</td>
            <td class='ppayslip-center-align'>" . htmlspecialchars($row['w1_daily_minimum_wage']) . "</td>
            <td class='ppayslip-td'>" . formatCurrency($row['w1']) . "</td>
          </tr>";
}

if (!is_null($row['w2_sunday_rest_day']) && $row['w2_sunday_rest_day'] != 0) {
    echo "<tr class='ppayslip-tr'>
            <td class='ppayslip-td'>Sunday Rest Day</td>
            <td class='ppayslip-td'>₱ " . number_format((float)$row['w2'] / max((float)$row['w2_sunday_rest_day'], 1), 2) . "</td>
            <td class='ppayslip-center-align'>" . htmlspecialchars($row['w2_sunday_rest_day']) . "</td>
            <td class='ppayslip-td'>" . formatCurrency($row['w2']) . "</td>
          </tr>";
}

if (!is_null($row['w3_legal_holiday']) && $row['w3_legal_holiday'] != 0) {
    echo "<tr class='ppayslip-tr'>
            <td class='ppayslip-td'>Legal Holiday</td>
            <td class='ppayslip-td'>₱ " . number_format((float)$row['w3'] / max((float)$row['w3_legal_holiday'], 1), 2) . "</td>
            <td class='ppayslip-center-align'>" . htmlspecialchars($row['w3_legal_holiday']) . "</td>
            <td class='ppayslip-td'>" . formatCurrency($row['w3']) . "</td>
          </tr>";
}

if (!is_null($row['w4_special_holiday']) && $row['w4_special_holiday'] != 0) {
    echo "<tr class='ppayslip-tr'>
            <td class='ppayslip-td'>Special Holiday</td>
            <td class='ppayslip-td'>₱ " . number_format((float)$row['w4'] / max((float)$row['w4_special_holiday'], 1), 2) . "</td>
            <td class='ppayslip-center-align'>" . htmlspecialchars($row['w4_special_holiday']) . "</td>
            <td class='ppayslip-td'>" . formatCurrency($row['w4']) . "</td>
          </tr>";
}

if (!is_null($row['w5_regular_overtime_perhour']) && $row['w5_regular_overtime_perhour'] != 0) {
    echo "<tr class='ppayslip-tr'>
            <td class='ppayslip-td'>Regular Overtime/Hour</td>
            <td class='ppayslip-td'>₱ " . number_format((float)$row['w5'] / max((float)$row['w5_regular_overtime_perhour'], 1), 2) . "</td>
            <td class='ppayslip-center-align'>" . htmlspecialchars($row['w5_regular_overtime_perhour']) . "</td>
            <td class='ppayslip-td'>" . formatCurrency($row['w5']) . "</td>
          </tr>";
}

if (!is_null($row['w6_special_overtime_perhour']) && $row['w6_special_overtime_perhour'] != 0) {
    echo "<tr class='ppayslip-tr'>
            <td class='ppayslip-td'>Special Overtime/Hour</td>
            <td class='ppayslip-td'>₱ " . number_format((float)$row['w6'] / max((float)$row['w6_special_overtime_perhour'], 1), 2) . "</td>
            <td class='ppayslip-center-align'>" . htmlspecialchars($row['w6_special_overtime_perhour']) . "</td>
            <td class='ppayslip-td'>" . formatCurrency($row['w6']) . "</td>
          </tr>";
}

if (!is_null($row['w7_special_holiday_overtime_perhour']) && $row['w7_special_holiday_overtime_perhour'] != 0) {
    echo "<tr class='ppayslip-tr'>
            <td class='ppayslip-td'>Special Holiday Overtime/Hour</td>
            <td class='ppayslip-td'>₱ " . number_format((float)$row['w7'] / max((float)$row['w7_special_holiday_overtime_perhour'], 1), 2) . "</td>
            <td class='ppayslip-center-align'>" . htmlspecialchars($row['w7_special_holiday_overtime_perhour']) . "</td>
            <td class='ppayslip-td'>" . formatCurrency($row['w7']) . "</td>
          </tr>";
}

if (!is_null($row['w8_regular_holiday_overtime_perhour']) && $row['w8_regular_holiday_overtime_perhour'] != 0) {
    echo "<tr class='ppayslip-tr'>
            <td class='ppayslip-td'>Regular Holiday Overtime/Hour</td>
            <td class='ppayslip-td'>₱ " . number_format((float)$row['w8'] / max((float)$row['w8_regular_holiday_overtime_perhour'], 1), 2) . "</td>
            <td class='ppayslip-center-align'>" . htmlspecialchars($row['w8_regular_holiday_overtime_perhour']) . "</td>
            <td class='ppayslip-td'>" . formatCurrency($row['w8']) . "</td>
          </tr>";
}

if (!is_null($row['w9_cater']) && $row['w9_cater'] != 0) {
    echo "<tr class='ppayslip-tr'>
            <td class='ppayslip-td'>Cater</td>
            <td class='ppayslip-td'>₱ " . number_format((float)$row['w9'] / max((float)$row['w9_cater'], 1), 2) . "</td>
            <td class='ppayslip-center-align'>" . htmlspecialchars($row['w9_cater']) . "</td>
            <td class='ppayslip-td'>" . formatCurrency($row['w9']) . "</td>
          </tr>";
}

        echo "<tr>
                <td><strong>Gross Pay</strong></td>
                <td></td>
                <td></td>
                <td><strong>" . formatCurrency($row['gross_pay']) . "</strong></td>
              </tr>";
        echo "</table>";

        echo "<h3>Deductions</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";

        if ($row['sss'] != 0) {
            echo "<tr>
                    <td>SSS</td>
                    <td>" . formatCurrency($row['gross_pay']) . "</td>
                    <td> x 5% </td>
                    <td>" . formatCurrency($row['sss']) . "</td>
                  </tr>";
        }
        if ($row['philhealth'] != 0) {
            echo "<tr>
                    <td>PhilHealth</td>
                    <td>" . formatCurrency($row['gross_pay']) . "</td>
                    <td> x 2% </td>
                    <td>" . formatCurrency($row['philhealth']) . "</td>
                  </tr>";
        }
        if ($row['pagibig'] != 0) {
            echo "<tr>
                    <td>Pag-IBIG</td>
                    <td>" . formatCurrency($row['gross_pay']) . "</td>
                    <td> x 2% </td>
                    <td>" . formatCurrency($row['pagibig']) . "</td>
                  </tr>";
        }
        if ($row['cater1'] != 0) {
            echo "<tr>
                    <td>CATER</td>
                    <td></td>
                    <td></td>
                    <td>" . formatCurrency($row['cater1']) . "</td>
                  </tr>";
        }
        if ($row['advance'] != 0) {
            echo "<tr>
                    <td>Advance</td>
                    <td></td>
                    <td></td>
                    <td>" . formatCurrency($row['advance']) . "</td>
                  </tr>";
        }

        echo "<tr>
                <td><strong>Total Deductions</strong></td>
                <td></td>
                <td></td>
                <td>" . formatCurrency($row['total_deductions']) . "</td>
              </tr>";

        echo "<tr>
                <td><strong>Net Pay</strong></td>
                <td></td>
                <td></td>
                <td><strong>" . formatCurrency($row['net_pay']) . "</strong></td>
              </tr>";

        echo "</table>";
        echo "</div>";
    }

    $stmt->close();
    echo "<script>window.onload = () => window.print();</script>";
    echo "</body></html>";
}
?>
