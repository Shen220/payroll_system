<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

require 'db.php';

// Allow both GET or POST (so <a href="export.php"> works)
if ($_SERVER['REQUEST_METHOD'] === 'GET' || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export']))) {

    // (no need for $exportType since you only want CSV)
    $stmt = $conn->prepare("
        SELECT 
            ei.employee_id,
            ei.last_name,
            ei.first_name,
            ei.position,
            ei.status,
            ei.board_lodging,
            ei.lodging_address,
            ei.food_allowance,
            ei.rate_1_daily_minimum_wage,
            ei.rate_2_sunday_rest_day,
            ei.rate_3_legal_holiday,
            ei.rate_4_special_holiday,
            ei.rate_5_regular_overtime_perhour,
            ei.rate_6_special_overtime_perhour,
            ei.rate_7_special_holiday_overtime_perhour,
            ei.rate_8_regular_holiday_overtime_perhour,
            ei.rate_9_cater,

            pt.year,
            pt.payroll_id,
            pd.week,
            pt.num_of_days_for_rate_1,
            pt.num_of_days_for_rate_2,
            pt.num_of_days_for_rate_3,
            pt.num_of_days_for_rate_4,
            pt.num_of_hours_for_rate_5,
            pt.num_of_hours_for_rate_6,
            pt.num_of_hours_for_rate_7,
            pt.num_of_hours_for_rate_8,
            pt.num_of_days_for_rate_9,
            pt.cater_deductions,
            pt.advance_deductions

        FROM employee_info_and_rates ei
        LEFT JOIN payroll_transactions pt 
            ON ei.employee_id = pt.employee_id
        LEFT JOIN payroll_dates pd 
            ON pt.payroll_id = pd.payroll_id
    ");

    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        die("No employee or payroll transaction data found.");
    }

        // Collect rows and compute payroll
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $gross_pay =
                ($row['num_of_days_for_rate_1'] * $row['rate_1_daily_minimum_wage']) +
                ($row['num_of_days_for_rate_2'] * $row['rate_2_sunday_rest_day']) +
                ($row['num_of_days_for_rate_3'] * $row['rate_3_legal_holiday']) +
                ($row['num_of_days_for_rate_4'] * $row['rate_4_special_holiday']) +
                ($row['num_of_hours_for_rate_5'] * $row['rate_5_regular_overtime_perhour']) +
                ($row['num_of_hours_for_rate_6'] * $row['rate_6_special_overtime_perhour']) +
                ($row['num_of_hours_for_rate_7'] * $row['rate_7_special_holiday_overtime_perhour']) +
                ($row['num_of_hours_for_rate_8'] * $row['rate_8_regular_holiday_overtime_perhour']) +
                ($row['num_of_days_for_rate_9'] * $row['rate_9_cater']);

            $sss        = $gross_pay * 0.05; 
            $pagibig    = $gross_pay * 0.02;    
            $philhealth = $gross_pay * 0.025; 

            $total_deductions = $sss + $pagibig + $philhealth + ($row['cater_deductions'] ?? 0) + ($row['advance_deductions'] ?? 0);
            $net_pay = $gross_pay - $total_deductions;

            $row['gross_pay'] = round($gross_pay, 2);
            $row['sss'] = round($sss, 2);
            $row['pagibig'] = round($pagibig, 2);
            $row['philhealth'] = round($philhealth, 2);
            $row['total_deductions'] = round($total_deductions, 2);
            $row['net_pay'] = round($net_pay, 2);

            $data[] = $row;
        }

        // Friendly headers for client UX
        $friendlyHeaders = [
            'year' => 'Year',
            'week' => 'Payroll Week',
            'employee_id' => 'Employee ID',
            'last_name' => 'Last Name',
            'first_name' => 'First Name',
            'position' => 'Position',
            'status' => 'Status',
            'board_lodging' => 'Board & Lodging',
            'lodging_address' => 'Lodging Address',
            'food_allowance' => 'Food Allowance',
            'num_of_days_for_rate_1' => 'Days',
            'rate_1_daily_minimum_wage' => 'Daily Minimum Wage',
            'num_of_days_for_rate_2' => 'Sunday/Rest Days',
            'rate_2_sunday_rest_day' => 'Sunday/Rest Rate',
            'num_of_days_for_rate_3' => 'Legal Holiday Days',
            'rate_3_legal_holiday' => 'Legal Holiday Rate',
            'num_of_days_for_rate_4' => 'Special Holiday Days',
            'rate_4_special_holiday' => 'Special Holiday Rate',
            'num_of_hours_for_rate_5' => 'Hours',
            'rate_5_regular_overtime_perhour' => 'Regular Overtime Rate',
            'num_of_hours_for_rate_6' => 'Hours',
            'rate_6_special_overtime_perhour' => 'Special Overtime Rate',
            'num_of_hours_for_rate_7' => 'Hours',
            'rate_7_special_holiday_overtime_perhour' => 'Special Holiday OT Rate',
            'num_of_hours_for_rate_8' => 'Hours',
            'rate_8_regular_holiday_overtime_perhour' => 'Regular Holiday OT Rate',
            'num_of_days_for_rate_9' => 'Days',
            'rate_9_cater' => 'Cater',
            'gross_pay' => 'Gross Pay',
            'sss' => 'SSS',
            'pagibig' => 'Pag-IBIG',
            'philhealth' => 'PhilHealth',
            'cater_deductions' => 'Cater Deductions',
            'advance_deductions' => 'Advance Deductions',
            'total_deductions' => 'Total Deductions',
            'net_pay' => 'Net Pay'
        ];

        // Clean buffer
        if (ob_get_length()) ob_end_clean();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Ai_Resto_Payroll.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel UTF-8

        // Write friendly headers
        fputcsv($output, array_values($friendlyHeaders));

        // Write rows
        foreach ($data as $row) {
            $rowData = [];
            foreach (array_keys($friendlyHeaders) as $field) {
                $rowData[] = $row[$field] ?? '';
            }
            fputcsv($output, $rowData);
        }

        fclose($output);
        exit;
}
?>
