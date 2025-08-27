<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

include 'db.php'; // DB connection

// Get the employee ID from the URL
$employee_id = $_GET['employee_id'] ?? null;

if (!$employee_id) {
    die("No employee ID specified.");
}

// Start a transaction for safety
$conn->begin_transaction();

// 🔹 For Payroll List tab (filter by same status)
$query1 = "
SELECT 
  ei.employee_id,
  pt.year,
  ei.last_name,
  ei.first_name,
  ei.status,
  pd.payroll_id,
  pd.week
FROM employee_info_and_rates ei
LEFT JOIN payroll_transactions pt ON ei.employee_id = pt.employee_id
LEFT JOIN payroll_dates pd ON pt.payroll_id = pd.payroll_id
WHERE ei.status = ?
";
$stmt1 = $conn->prepare($query1);
$stmt1->bind_param("s", $statusFilter);
$stmt1->execute();
$result1 = $stmt1->get_result();
if (!$result1) {
    die("Error retrieving payroll info: " . $conn->error);
}

try {
    // Insert record into archived_employees
    $sql_insert = "
        INSERT INTO archived_employees (
            employee_id,
            last_name,
            first_name,
            position,
            status,
            board_lodging,
            lodging_address,
            food_allowance,
            rate_1_daily_minimum_wage,
            rate_2_sunday_rest_day,
            rate_3_legal_holiday,
            rate_4_special_holiday,
            rate_5_regular_overtime_perhour,
            rate_6_special_overtime_perhour,
            rate_7_special_holiday_overtime_perhour,
            rate_8_regular_holiday_overtime_perhour,
            rate_9_cater
        )
        SELECT 
            employee_id,
            last_name,
            first_name,
            position,
            status,
            board_lodging,
            lodging_address,
            food_allowance,
            rate_1_daily_minimum_wage,
            rate_2_sunday_rest_day,
            rate_3_legal_holiday,
            rate_4_special_holiday,
            rate_5_regular_overtime_perhour,
            rate_6_special_overtime_perhour,
            rate_7_special_holiday_overtime_perhour,
            rate_8_regular_holiday_overtime_perhour,
            rate_9_cater
        FROM employee_info_and_rates
        WHERE employee_id = ?
    ";

    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param('i', $employee_id);

    if (!$stmt_insert->execute()) {
        throw new Exception("Insert failed: " . $stmt_insert->error);
    }
    
    $sql_delete = "DELETE FROM employee_info_and_rates WHERE employee_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param('i', $employee_id);

    if (!$stmt_delete->execute()) {
        throw new Exception("Delete failed: " . $stmt_delete->error);
    }

    $conn->commit();

    header("Location: dashboard.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Error archiving employee: " . $e->getMessage();
}




function calculatePayroll($conn, $employee_id, $payroll_id) {
    // Get rates from employee_info_and_rates
    $queryRates = "SELECT 
        rate_1_daily_minimum_wage,
        rate_2_sunday_rest_day,
        rate_3_legal_holiday,
        rate_4_special_holiday,
        rate_5_regular_overtime_perhour,
        rate_6_special_overtime_perhour,
        rate_7_special_holiday_overtime_perhour,
        rate_8_regular_holiday_overtime_perhour,
        rate_9_cater
        FROM employee_info_and_rates 
        WHERE employee_id = ?";
        $stmtRates = $conn->prepare($queryRates);
        $stmtRates->bind_param("i", $employee_id);
        $stmtRates->execute();
        $rates = $stmtRates->get_result()->fetch_assoc();

    if (!$rates) {
        return null; // no rates found
    }

    // Get hours/deductions from payroll_transactions
    $queryTrans = "SELECT 
        num_of_days_for_rate_1,
        num_of_days_for_rate_2,
        num_of_days_for_rate_3,
        num_of_days_for_rate_4,
        num_of_hours_for_rate_5,
        num_of_hours_for_rate_6,
        num_of_hours_for_rate_7,
        num_of_hours_for_rate_8,
        num_of_days_for_rate_9,
        cater_deductions,
        advance_deductions
    FROM payroll_transactions
    WHERE employee_id = ? AND payroll_id = ?";
    $stmtTrans = $conn->prepare($queryTrans);
    $stmtTrans->bind_param("ii", $employee_id, $payroll_id);
    $stmtTrans->execute();
    $trans = $stmtTrans->get_result()->fetch_assoc();

    if (!$trans) {
        return null; // no transactions found
    }

    // Calculate Gross Pay
    $grossPay = 
        ($rates['rate_1_daily_minimum_wage'] * $trans['num_of_days_for_rate_1']) +
        ($rates['rate_2_sunday_rest_day'] * $trans['num_of_days_for_rate_2']) +
        ($rates['rate_3_legal_holiday'] * $trans['num_of_days_for_rate_3']) +
        ($rates['rate_4_special_holiday'] * $trans['num_of_days_for_rate_4']) +
        ($rates['rate_5_regular_overtime_perhour'] * $trans['num_of_hours_for_rate_5']) +
        ($rates['rate_6_special_overtime_perhour'] * $trans['num_of_hours_for_rate_6']) +
        ($rates['rate_7_special_holiday_overtime_perhour'] * $trans['num_of_hours_for_rate_7']) +
        ($rates['rate_8_regular_holiday_overtime_perhour'] * $trans['num_of_hours_for_rate_8']) +
        ($rates['rate_9_cater'] * $trans['num_of_days_for_rate_9']);

    // Calculate Deductions
    $pagibig = 0.02 * $grossPay;
    $sss = 0.05 * $grossPay;
    $philHealth = 0.025 * $grossPay;
    $cater = $trans['cater_deductions'];
    $advance = $trans['advance_deductions'];

    $totalDeductions = $pagibig + $sss + $philHealth + $cater + $advance;

    // Net Pay
    $netPay = $grossPay - $totalDeductions;

    return [
        'gross_pay' => $grossPay,
        'total_deductions' => $totalDeductions,
        'net_pay' => $netPay
    ];
    
    $stmt = $conn->prepare("
  SELECT pt.*, pd.week, e.*
  FROM payroll_transactions pt
  JOIN payroll_dates pd ON pt.payroll_id = pd.payroll_id
  JOIN employee_info_and_rates e ON pt.employee_id = e.employee_id
  WHERE pt.employee_id = ? AND pt.year = ? AND pd.week = ?
");
$stmt->bind_param("iis", $employee_id, $year, $week);
$stmt->execute();
}
?>