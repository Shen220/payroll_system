<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: authentication.php");
    exit();
}

include '../php/db.php';

// 🔹 For Employee List tab (get all archived employees)
$query = "SELECT employee_id, last_name, first_name, position, status, board_lodging, lodging_address, food_allowance 
          FROM archived_employees";
$result = $conn->query($query);
if (!$result) {
    die("Error retrieving employee info: " . $conn->error);
}

// 🔹 For Payroll List tab (get all archived employees + payroll)
$query1 = "
SELECT 
  ae.employee_id,
  ae.last_name,
  ae.first_name,
  ae.status,
  pd.payroll_id,
  pd.week
FROM archived_employees ae
LEFT JOIN payroll_dates pd ON ae.employee_id = ae.employee_id
";
$result1 = $conn->query($query1);
if (!$result1) {
    die("Error retrieving payroll info: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Archived Employees</title>
    <link href="../css/payslip.css" rel="stylesheet">
    <!-- Bootstrap CSS (if not already included) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Bundle JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/archive.css">
</head>
<body>
    <div id="header_container">
        <div id="header_text" class="controls">
            <img src="../images/logo-removebg-preview.png" alt="Company Logo" class="logo">
            <h1 id="the_text">Archive</h1>
        </div>

        <div id="btn_actions">
            <div class="input-group" style="width:275px;">
            <input type="text" id="employeeSearch" class="form-control" placeholder="Search employee...">
            <button class="btn btn-outline-secondary" id="search-btn" type="button" onclick="searchEmployee()">Search</button>
            </div>
            <div class="icon_label">
        </div>
        </div>
    </div>



        <div id="employeeList" class="tabcontent">
        <?php if ($result && $result->num_rows > 0): ?>
        <form id="employee-form">
            <input type="hidden" name="selected_ids" id="selected_ids">
            <table class="employee_table" id="employeeTable">
            <thead>
                <tr>
                <th>ID</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Position</th>
                <th>Status</th>
                <th>Board & Lodging</th>
                <th>Food Allowance</th>
                <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr data-id="<?= $row['employee_id'] ?>">
                    <td><?= mb_strtoupper(htmlspecialchars($row['employee_id'])) ?></td>
                    <td><?= mb_strtoupper(htmlspecialchars($row['last_name'])) ?></td>
                    <td><?= mb_strtoupper(htmlspecialchars($row['first_name'])) ?></td>
                    <td><?= mb_strtoupper(htmlspecialchars($row['position'])) ?></td>
                    <td><?= mb_strtoupper(htmlspecialchars($row['status'])) ?></td>
                    <td><?= mb_strtoupper($row['board_lodging'] === 'Yes' ? htmlspecialchars($row['lodging_address']) : 'No') ?></td>
                    <td><?= mb_strtoupper(htmlspecialchars($row['food_allowance'])) ?></td>
                    <td>
                        <a href="../php/restore_archive.php?id=<?= $row['employee_id'] ?>" class="action-edit-btn">Return</a>
                        <a href="../php/delete_archive.php?id=<?= $row['employee_id'] ?>" class="action-delete-btn" onclick="return confirm('Are you sure you want to delete this employee?');">Delete</a>
                    </td>
                
                </tr>
                <?php endwhile; ?>
            </tbody>
            </table>
        </form>
        <?php else: ?>
        <p style="text-align:center; margin-top: 200px;">No employees found.</p>
        <?php endif; ?>
        </div>

       

    <a href="/payroll-system/php/dashboard.php" id="dashboard-btn" class="btn btn-secondary" title="Dashboard">
        View Dashboard
    </a>

<script>
function searchEmployee() {
    const input = document.getElementById("employeeSearch").value.toLowerCase();

    // 🔹 Employee List Table
    const empRows = document.querySelectorAll("#employeeTable tbody tr");
    empRows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });

   
}

// 🔹 Live search while typing
document.getElementById("employeeSearch").addEventListener("keyup", searchEmployee);
</script>

<script>
    function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
    }

    // Set the first tab as active by default
    document.getElementsByClassName("tablinks")[0].click();
</script>

</body>
</html>
