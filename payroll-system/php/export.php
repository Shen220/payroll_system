<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {
    $exportType = $_POST['export'];

    if ($exportType === 'csv') {
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

        // Collect all rows
        $data = [];
        $headers = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
            $headers = array_unique(array_merge($headers, array_keys($row)));
        }

        sort($headers);

        // Output CSV headers
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="employee_full_payroll_data.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);

        // Output rows
        foreach ($data as $row) {
            $rowData = [];
            foreach ($headers as $header) {
                $rowData[] = $row[$header] ?? '';
            }
            fputcsv($output, $rowData);
        }

        fclose($output);
        exit;
    }
}
?>
