<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
        <?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!DOCTYPE html>
<html>
<head>
   <meta charset="UTF-8">
  <title>Employee List</title>
  <link href="../css/payslip.css" rel="stylesheet">
  <!-- Bootstrap CSS (if not already included) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Bundle JS (includes Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../css/dashboard.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body>

<div id="header_container">
  <div id="header_text" class="controls">
    <img src="../images/logo-removebg-preview.png" alt="Company Logo" class="logo">
    <h1 id="the_text">AI Korean Restaurant</h1>
  </div>

  <div id="btn_actions">
    <div class="icon_label">
      <button id="add_btn" onclick="loadAddEmployeeModal()" class="btn btn-primary" title="Add Employee">
        <i id="add_icon" class="fa-solid fa-user-plus"></i>
      </button>
    </div>
    <div class="icon_label">
      <a id="logout_btn" href="logout.php" class="icon_btn" title="Logout">
        <i id="add_icon" class="fa-solid fa-right-from-bracket fa-2x"></i>
      </a>
    </div>
  </div>
</div>

<div class="tab">
  <button class="tablinks" style="margin-left: 500px" onclick="openTab(event, 'employeeList')">Employee List</button>
  <button class="tablinks" onclick="openTab(event, 'payrollInfo')">Payroll Information</button>
<button type="button" 
        class="special-tablinks" 
        id="generatePayslipsBtn" 
        style="margin-left: 200px; visibility: hidden;" 
        onclick="generateBatchPayslips()">
    Generate Selected Payslips
</button>
</div>

<div id="employeeList" class="tabcontent">
<?php if ($result && $result->num_rows > 0): ?>
  <form id="employee-form">
    <input type="hidden" name="selected_ids" id="selected_ids">
    <table class="employee_table">
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
              <a href="#" class="action-edit-btn" onclick="loadEditModal(<?= $row['employee_id'] ?>)">Edit</a>
              <a href="delete_employee.php?employee_id=<?= $row['employee_id'] ?>" class="action-delete-btn" onclick="return confirm('Are you sure you want to delete this employee?');">Archive</a>
            </td>
         
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </form>
<?php else: ?>
  <p style="text-align:center;">No employees found.</p>
<?php endif; ?>
</div>

<div id="payrollInfo" class="tabcontent">
  <?php if ($result1 && $result1->num_rows > 0): ?>
    <table class="employee_table">
      <thead>
        <tr>
           <th><input type="checkbox" id="selectAllPayslips"></th>
          <th>ID</th>
          <th>Last Name</th>
          <th>First Name</th>
          <th>Gross Pay</th>
          <th>Total Deductions</th>
          <th>Net Pay</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $result1->fetch_assoc()): ?>
          <tr>
            <td><input type="checkbox" class="payslipCheckbox" value="<?= $row['payroll_id'] ?>"></td>
            <td><?= htmlspecialchars($row['employee_id']) ?></td>
            <td><?= mb_strtoupper(htmlspecialchars($row['last_name'])) ?></td>
            <td><?= mb_strtoupper(htmlspecialchars($row['first_name'])) ?></td>
            <td>₱ <?= isset($row['gross_pay']) ? number_format($row['gross_pay'], 2) : 'N/A' ?></td>
            <td>₱ <?= isset($row['total_deductions']) ? number_format($row['total_deductions'], 2) : 'N/A' ?></td>
            <td>₱ <?= isset($row['net_pay']) ? number_format($row['net_pay'], 2) : 'N/A' ?></td>
            <td>
                <a href="#" class="action-edit-btn" onclick="loadEditPayslipModal(<?= $row['payroll_id'] ?>)">Edit</a>
                <a href="#" class="action-edit-btn" onclick="loadPayslipModal(<?= $row['payroll_id'] ?>); return false;">Payslip</a>

            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

  <?php else: ?>
    <p>No payroll records found.</p>
  <?php endif; ?>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Employee Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="editEmployeeBody">
        <!-- Dynamic content loaded here -->
      </div>
    </div>
  </div>
</div>

<!-- Edit Payslip Modal -->
<div class="modal fade" id="editPayslipModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="display: flex; justify-content: center; margin-right: 500px">
      <div class="modal-content modal-content-payslip">
        <div class="modal-header">
          <h5 class="modal-title">Edit Payslip Information </h5> <p>Date: <?= date("F j, Y") ?></p>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="editPayslipBody">
          <!-- Loaded via JS -->
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add employee modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content" style="height: 800px">
      <div class="modal-header">
        <h5 class="modal-title">Add Employee Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Body (content goes here) -->
      <div class="modal-body" id="addEmployeeBody">
        <!-- Loaded via AJAX -->
      </div>
    </div>
  </div>
</div>

<!-- View Payslip Modal -->
<div class="modal fade" id="viewPayslipModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl">
    <div class="modal-content" style="width: 800px;">
      <div class="modal-header">
        <h5 class="modal-title">Payslip</h5>
          <form method="post" action="export.php" id="export-form">
                <input type="hidden" name="employee_id" value="<?= htmlspecialchars($value['employee_id']) ?>">
                <button style="margin-left: 500px" id="ppayslip-export" type="submit" name="export" title="Export Payslip" value="csv"><i class="fa-solid fa-file-export"></i></button>
            </form>

        <button style="margin-left: 20px" id="ppayslip-print" onclick="window.print()" title="Print Payslip"><i class="fa-solid fa-print"></i></button>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="viewPayslipBody">
        <!-- Payslip content will be loaded here -->
      </div>
    </div>
  </div>
</div>


<script src="../js/dashboard.js"></script>
</body>
</html>
