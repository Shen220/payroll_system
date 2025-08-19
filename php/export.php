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
                ei.lodging_address,
                ei.board_lodging,
                ei.food_allowance,

                ep.payroll_id,
                ep.w1_daily_minimum_wage,
                ep.w2_sunday_rest_day,
                ep.w3_legal_holiday,
                ep.w4_special_holiday,
                ep.w5_regular_overtime_perhour,
                ep.w6_special_overtime_perhour,
                ep.w7_special_holiday_overtime_perhour,
                ep.w8_regular_holiday_overtime_perhour,
                ep.w9_cater,

                pc.w1,
                pc.w2,
                pc.w3,
                pc.w4,
                pc.w5,
                pc.w6,
                pc.w7,
                pc.w8,
                pc.w9,
                pc.gross_pay,
                pc.sss,
                pc.philhealth,
                pc.pagibig,
                pc.cater1,
                pc.advance,
                pc.total_deductions,
                pc.net_pay

            FROM employee_info ei
            LEFT JOIN employee_payroll ep ON ei.employee_id = ep.employee_id
            LEFT JOIN payroll_computation pc ON ep.payroll_id = pc.payroll_id AND ei.employee_id = pc.employee_id
        ");

        if (!$stmt) {
            die("SQL error: " . $conn->error);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result || $result->num_rows === 0) {
            die("No employee payroll or computation data found.");
        }

        // Collect all data and dynamic headers
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

        // Output each row of data
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
