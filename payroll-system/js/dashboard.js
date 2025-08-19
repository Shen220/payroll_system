

function initPayslipEditor(container) {
    const root = container || document;
    const parseNum = v => {
        if (v === null || v === undefined) return 0;
        return parseFloat(String(v).replace(/[^0-9.\-]/g, '')) || 0;
    };

    function recalcGrossNet() {
        let gross = 0;
        // sum category hidden inputs
        root.querySelectorAll('.category-total-input').forEach(el => {
            gross += parseNum(el.value);
        });

        // update gross displays and hidden
        const grossDisplay = root.querySelector('#gross_pay_display');
        const grossHidden = root.querySelector('#gross_pay_input');
        if (grossDisplay) grossDisplay.textContent = '₱' + gross.toFixed(2);
        if (grossHidden) grossHidden.value = gross.toFixed(2);

        // static deductions (from hidden inputs)
        const sss = parseNum(root.querySelector('#sss_input')?.value);
        const philhealth = parseNum(root.querySelector('#philhealth_input')?.value);
        const pagibig = parseNum(root.querySelector('#pagibig_input')?.value);

        // editable deductions
        const cater = parseNum(root.querySelector('#cater1_input')?.value);
        const advance = parseNum(root.querySelector('#advance_input')?.value);

        const totalDeductions = sss + philhealth + pagibig + cater + advance;

        const totalDedDisplay = root.querySelector('#total_deductions_display');
        const totalDedHidden = root.querySelector('#total_deductions_input');
        if (totalDedDisplay) totalDedDisplay.textContent = '₱' + totalDeductions.toFixed(2);
        if (totalDedHidden) totalDedHidden.value = totalDeductions.toFixed(2);

        // net pay
        const net = gross - totalDeductions;
        const netDisplay = root.querySelector('#net_pay_display');
        const netHidden = root.querySelector('#net_pay_input');
        if (netDisplay) netDisplay.textContent = '₱' + net.toFixed(2);
        if (netHidden) netHidden.value = net.toFixed(2);
    }

    function updateRowTotalsFromInput(input) {
        let hoursInput, rateInput;
        if (input.classList.contains('hours')) {
            hoursInput = input;
            rateInput = root.querySelector(`input[name='${input.dataset.rate}']`);
        } else if (input.classList.contains('rate')) {
            rateInput = input;
            hoursInput = root.querySelector(`input[name='${input.dataset.hours}']`);
        } else {
            return;
        }

        const hours = parseNum(hoursInput?.value);
        const rate = parseNum(rateInput?.value);
        const total = hours * rate;

        const totalField = input.dataset.target;
        const display = root.querySelector(`#${totalField}_display`);
        const hidden = root.querySelector(`#${totalField}_input`);

        if (display) display.textContent = '₱' + total.toFixed(2);
        if (hidden) hidden.value = total.toFixed(2);

        // update gross & net
        recalcGrossNet();
    }

    // attach listeners
    const hoursAndRates = root.querySelectorAll('.hours, .rate');
    hoursAndRates.forEach(inp => {
        inp.removeEventListener('input', inp._payslipHandler); // defensive remove
        inp._payslipHandler = (e) => updateRowTotalsFromInput(e.target);
        inp.addEventListener('input', inp._payslipHandler);
    });

    // deduction inputs
    ['#cater1_input', '#advance_input'].forEach(selector => {
        const el = root.querySelector(selector);
        if (el) {
            el.removeEventListener('input', el._payslipDedHandler);
            el._payslipDedHandler = recalcGrossNet;
            el.addEventListener('input', el._payslipDedHandler);
        }
    });

    // initial recalc to normalize UI
    recalcGrossNet();

    // for debugging (optional)
    // console.log('initPayslipEditor: listeners attached', hoursAndRates.length);
}
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

                    // Optionally, reattach event if form still exists in returned HTML
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
