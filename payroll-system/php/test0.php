<?php
// dashboard.php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

include 'db.php';

// Fetch employees with their latest payroll transactions
$sql = "
    SELECT e.employee_id, e.first_name, e.last_name, e.position,
           pt.year, pd.week
    FROM employee_info_and_rates e
    LEFT JOIN payroll_transactions pt 
        ON e.employee_id = pt.employee_id
    LEFT JOIN payroll_dates pd
        ON pt.payroll_id = pd.payroll_id
    ORDER BY e.last_name, e.first_name, pt.year DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip Dashboard</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #ccc;
        }
        th {
            background: #f4f4f4;
        }
        a.edit-btn {
            display: inline-block;
            padding: 5px 10px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }
        a.edit-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <h2>Payslip Dashboard</h2>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Position</th>
                <th>Year</th>
                <th>Payroll Period</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?></td>
                    <td><?= htmlspecialchars($row['position']) ?></td>
                    <td><?= htmlspecialchars($row['year'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['week'] ?? '-') ?></td>
                    <td>
                        <a class="edit-btn" 
                           href="test.php?employee_id=<?= $row['employee_id'] ?>&year=<?= $row['year'] ?>&week=<?= urlencode($row['week']) ?>">
                           Edit
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">No employees found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php $conn->close(); ?>
