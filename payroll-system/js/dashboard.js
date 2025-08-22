
const selectedRows = new Set();

  document.querySelectorAll('tbody tr').forEach(row => {
    row.addEventListener('click', (event) => {
      // Do not trigger row selection if a button or link inside the row is clicked
      if (event.target.tagName === 'A' || event.target.tagName === 'BUTTON' || event.target.closest('a') || event.target.closest('button')) {
        return;
      }

      const id = row.getAttribute('data-id');

      if (selectedRows.has(id)) {
        row.classList.remove('selected');
        selectedRows.clear();
      } else {
        clearSelections();
        row.classList.add('selected');
        selectedRows.add(id);
      }

      updateSelectedInput();
    });
  });

    function enterDeleteMode() {
    mode = 'multiple';
    document.getElementById('deselect_btn').style.display = 'inline-block';

    if (selectedRows.size === 0) {
      alert("Select employees to delete by clicking their rows. Click 'Deselect All' to cancel.");
      return;
    }

    const ids = Array.from(selectedRows);
    const confirmDelete = confirm(`Are you sure you want to delete ${ids.length} employee(s)?`);
    if (confirmDelete) {
      window.location.href = `delete.php?ids=${ids.join(',')}`;
    }
  }

  function updateSelectedInput() {
    document.getElementById('selected_ids').value = Array.from(selectedRows).join(',');
  }

  function clearSelections() {
    selectedRows.clear();
    document.querySelectorAll('tr.selected').forEach(row => row.classList.remove('selected'));
    updateSelectedInput();
  }

  function payslip(id) {
    // If an ID is passed directly (from a row button), use it.
    if (id !== undefined && id !== null) {
      window.location.href = `payslip.php?id=${id}`;
      return;
    }
  }
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

// multiple pazyslip generation
  document.getElementById("selectAllPayslips").addEventListener("change", function() {
    const checkboxes = document.querySelectorAll(".payslipCheckbox");
    checkboxes.forEach(cb => cb.checked = this.checked);
  });

  function generateBatchPayslips() {
    const selected = Array.from(document.querySelectorAll(".payslipCheckbox:checked"))
                         .map(cb => cb.value);

    if (selected.length === 0) {
      alert("Please select at least one payslip.");
      return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../php/batch_payslip.php';
    form.target = '_blank';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'payroll_ids';
    input.value = JSON.stringify(selected);

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
  }

  

    function toggleLodgingAddress(value) {
        document.getElementById("add-lodging_input").style.display = value === "Yes" ? "block" : "none";
    }

     function toggleLodgingAddress1(value, inputId) {
        document.getElementById(inputId).style.display = (value === "Yes") ? "block" : "none";
    }

    document.addEventListener('DOMContentLoaded', function () {
        const lodgingSelect = document.querySelector('select[name="board_lodging"]');
        toggleLodgingAddress1(lodgingSelect.value, 'edit_lodging_input');
    });

        function toggleLodgingAddress1(value) {
        document.getElementById("edit_lodging_input").style.display = value === "Yes" ? "block" : "none";
    }

// multiple pazyslip generation
  document.getElementById("selectAllPayslips").addEventListener("change", function() {
    const checkboxes = document.querySelectorAll(".payslipCheckbox");
    checkboxes.forEach(cb => cb.checked = this.checked);
  });

  function generateBatchPayslips() {
    const selected = Array.from(document.querySelectorAll(".payslipCheckbox:checked"))
                         .map(cb => cb.value);

    if (selected.length === 0) {
      alert("Please select at least one payslip.");
      return;
    }

    if (selected.length > 4) {
        alert("⚠ You can only generate a maximum of 4 payslips at a time.");
        return;
    }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../php/batch_payslip.php';
    form.target = '_blank';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'payroll_ids';
    input.value = JSON.stringify(selected);

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
  }

  function loadEditModal(employee_id) {
    fetch(`/payroll-system/html/edit_employee_html.php?id=${employee_id}`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(response => response.text())
    .then(data => {
      document.getElementById("editEmployeeBody").innerHTML = data;
      const modalElement = document.getElementById("editEmployeeModal");
      const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: false
      });

      modal.show();
    })
    .catch(error => console.error("Error loading modal:", error));
  }

function loadEditPayslipModal(employee_id) {
    const modalBody = document.getElementById("editPayslipBody");
    const modalElement = document.getElementById("editPayslipModal");

    modalBody.innerHTML = "Loading...";
    const modal = new bootstrap.Modal(modalElement, { backdrop: 'static', keyboard: false });
    modal.show();

    fetch(`/payroll-system/html/add_payslip_html.php?employee_id=${employee_id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.text())
    .then(html => {
        modalBody.innerHTML = html;

        // attach live computation once form loads
        attachLiveComputation(modalBody);

        // Attach submit event AFTER form is injected
        const form = modalBody.querySelector('#payslipForm');
        if (form) {
            form.addEventListener('submit', function(e){
                e.preventDefault();
                const formData = new FormData(this);

                fetch('/payroll-system/html/add_payslip_html.php', { 
                    method: 'POST', 
                    body: formData 
                })
                .then(res => res.text())
                .then(html => {
                    modalBody.innerHTML = html;

                    // Reattach computation + form submit after reload
                    attachLiveComputation(modalBody);

                    const newForm = modalBody.querySelector('#payslipForm');
                    if(newForm) loadFormSubmitListener(newForm, modalBody);
                });
            });
        }
    })
    .catch(error => console.error("Error loading payslip modal:", error));
}

  function loadAddEmployeeModal() {
    fetch('/payroll-system/html/add_html.php', {
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(response => response.text())
    .then(data => {
      document.getElementById('addEmployeeBody').innerHTML = data;
      const modalElement = document.getElementById('addEmployeeModal');
      const modal= new bootstrap.Modal(modalElement,{
        backdrop: 'static',
        keyboard: false
      })

      modal.show();
    })
    .catch(error => console.error("Error loading add employee modal:", error));
  }

  function loadPayslipModal(id) {
    fetch(`/payroll-system/html/payslip_html.php?id=${id}`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(response => response.text())
    .then(data => {
      document.getElementById("viewPayslipBody").innerHTML = data;
      console.log("Payslip data loaded:", data); // Add this line
      const modalElement = document.getElementById("viewPayslipModal");
      const modal= new bootstrap.Modal(modalElement,{
        backdrop: 'static',
        keyboard: false
      })
      modal.show();
    })
    .catch(error => console.error("Error loading payslip modal:", error));
  }

function updateGenerateButtonVisibility() {
    const checkedCount = document.querySelectorAll(".payslipCheckbox:checked").length;
    const button = document.getElementById("generatePayslipsBtn");
    button.style.visibility = (checkedCount > 0) ? "visible" : "hidden";
}



// Watch for individual checkbox changes
document.querySelectorAll(".payslipCheckbox").forEach(cb => {
    cb.addEventListener("change", updateGenerateButtonVisibility);
});

// Also trigger when "select all" changes
document.getElementById("selectAllPayslips").addEventListener("change", updateGenerateButtonVisibility);

// Initialize on load
updateGenerateButtonVisibility();

function attachLiveComputation(container) {
    // Display spans
    const totalAmountSpan = document.getElementById("total_amount");
    const sssSpan = document.getElementById("sss");
    const pagibigSpan = document.getElementById("pagibig");
    const philhealthSpan = document.getElementById("philhealth");
    const totalDeductionsSpan = document.getElementById("total_deductions");
    const netPaySpan = document.getElementById("net_pay");

    // Manual deduction inputs
    const caterInput = document.getElementById("cater_deductions");
    const advanceInput = document.getElementById("advance_deductions");

    // Hidden inputs (for form submit)
    const totalAmountHidden = document.getElementById("hidden_total_amount");
    const sssHidden = document.getElementById("hidden_sss");
    const pagibigHidden = document.getElementById("hidden_pagibig");
    const philhealthHidden = document.getElementById("hidden_philhealth");
    const totalDeductionsHidden = document.getElementById("hidden_total_deductions");
    const netPayHidden = document.getElementById("hidden_net_pay");

    // Payroll select + gov block
    const payrollSelect = document.getElementById("payrollPeriod") ||
                          container.querySelector('select[name="payroll_id"]');
    const govDeductionsBlock = document.getElementById("govDeductions");

    // --- Helpers ---
    function isSecondCutoff() {
        if (!payrollSelect) return false;
        const txt = payrollSelect.options[payrollSelect.selectedIndex]?.text || "";
        return /\(\s*2\s*\)/.test(txt); // match "(2)" even with spaces
    }

    function toggleGovVisibility(hide) {
        if (govDeductionsBlock) {
            govDeductionsBlock.classList.toggle("hidden", hide);
        }
    }

    // --- Main computation ---
    function updateTotal() {
        let total = 0;

        // compute gross
        container.querySelectorAll("input[data-multiplier]").forEach(input => {
            const multiplier = parseFloat(input.dataset.multiplier) || 0;
            const value = parseFloat(input.value) || 0;
            total += value * multiplier;
        });

        totalAmountSpan.textContent = total.toFixed(2);
        if (totalAmountHidden) totalAmountHidden.value = total.toFixed(2);

        const secondHalf = isSecondCutoff();
        toggleGovVisibility(secondHalf);

        // government deductions (0 in second cutoff)
        const sss = secondHalf ? 0 : total * 0.05;
        const pagibig = secondHalf ? 0 : total * 0.02;
        const philhealth = secondHalf ? 0 : total * 0.025;

        // manual deductions
        const cater = parseFloat(caterInput?.value) || 0;
        const advance = parseFloat(advanceInput?.value) || 0;

        // total deductions
        const totalDeductions = sss + pagibig + philhealth + cater + advance;

        // update UI
        sssSpan.textContent = sss.toFixed(2);
        pagibigSpan.textContent = pagibig.toFixed(2);
        philhealthSpan.textContent = philhealth.toFixed(2);
        totalDeductionsSpan.textContent = totalDeductions.toFixed(2);

        // sync hidden inputs
        if (sssHidden) sssHidden.value = sss.toFixed(2);
        if (pagibigHidden) pagibigHidden.value = pagibig.toFixed(2);
        if (philhealthHidden) philhealthHidden.value = philhealth.toFixed(2);
        if (totalDeductionsHidden) totalDeductionsHidden.value = totalDeductions.toFixed(2);

        // net pay
        const netPay = total - totalDeductions;
        netPaySpan.textContent = netPay.toFixed(2);
        if (netPayHidden) netPayHidden.value = netPay.toFixed(2);
    }

    // --- Event listeners ---
    container.querySelectorAll("input[data-multiplier]").forEach(input => {
        const multiplier = parseFloat(input.dataset.multiplier) || 0;
        const resultSpan = container.querySelector("#result_" + input.id.split("input_")[1]);

        input.addEventListener("input", () => {
            const value = parseFloat(input.value) || 0;
            resultSpan.textContent = (value * multiplier).toFixed(2);
            updateTotal();
        });
    });

    if (caterInput) caterInput.addEventListener("input", updateTotal);
    if (advanceInput) advanceInput.addEventListener("input", updateTotal);
    if (payrollSelect) payrollSelect.addEventListener("change", updateTotal);

    // initial run
    updateTotal();
}
