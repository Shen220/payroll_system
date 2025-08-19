import React, { useState, useEffect } from 'react';

function Payslip() {
  const [payslip, setPayslip] = useState(null);

  useEffect(() => {
    async function fetchPayslip() {
      try {
        const response = await fetch('http://localhost/payroll_system/php/payslip.php?id=1');
        const data = await response.json();
        setPayslip(data);
      } catch (error) {
        console.error('Error fetching payslip:', error);
      }
    }

    fetchPayslip();
  }, []);

  if (!payslip) {
    return <div>Loading...</div>;
  }

  return (
    <div>
      <h2>Payslip</h2>
      <p>Employee ID: {payslip.employee_id}</p>
      <p>Name: {payslip.first_name} {payslip.last_name}</p>
      <p>Gross Pay: {payslip.gross_pay}</p>
      <p>Net Pay: {payslip.net_pay}</p>
      <p>Deductions: {payslip.deductions}</p>
    </div>
  );
}

export default Payslip;
