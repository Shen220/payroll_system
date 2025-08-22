-- CREATION OF EMPLOYEE INFO TABLE
CREATE TABLE IF NOT EXISTS employee_info (
    employee_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    last_name VARCHAR(20) NOT NULL,
    first_name VARCHAR(20) NOT NULL,
    position VARCHAR(100) NOT NULL,
    status ENUM('Permanent', 'On-Call') NOT NULL,
    lodging_address VARCHAR(255) DEFAULT NULL,
    board_lodging ENUM('Yes', 'No') NOT NULL,
    food_allowance ENUM('Full', 'Partial', 'None') NOT NULL DEFAULT 'Full'
);

-- CREATION OF EMPLOYEE PAYROLL TABLE
CREATE TABLE IF NOT EXISTS employee_payroll (
    payroll_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNSIGNED NOT NULL,
    
    w1_daily_minimum_wage INT DEFAULT 0,
    w2_sunday_rest_day INT DEFAULT 0,
    w3_legal_holiday INT DEFAULT 0,
    w4_special_holiday INT DEFAULT 0,
    w5_regular_overtime_perhour INT DEFAULT 0,
    w6_special_overtime_perhour INT DEFAULT 0,
    w7_special_holiday_overtime_perhour INT DEFAULT 0,
    w8_regular_holiday_overtime_perhour INT DEFAULT 0,
    w9_cater INT DEFAULT 0,

    CONSTRAINT fk_employee
        FOREIGN KEY (employee_id) REFERENCES employee_info(employee_id)
        ON DELETE CASCADE
);

-- CREATION OF PAYROLL COMPUTATION TABLE
CREATE TABLE IF NOT EXISTS payroll_computation (
    computation_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payroll_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,

    -- product of 'entered days/hours from employee_payroll' and 'entered values from the form'
    w1 DECIMAL(10,2) DEFAULT 0.00,
    w2 DECIMAL(10,2) DEFAULT 0.00,
    w3 DECIMAL(10,2) DEFAULT 0.00,
    w4 DECIMAL(10,2) DEFAULT 0.00,
    w5 DECIMAL(10,2) DEFAULT 0.00,
    w6 DECIMAL(10,2) DEFAULT 0.00,
    w7 DECIMAL(10,2) DEFAULT 0.00,
    w8 DECIMAL(10,2) DEFAULT 0.00,
    w9 DECIMAL(10,2) DEFAULT 0.00,

    -- gross pay formula
    gross_pay DECIMAL(10,2) GENERATED ALWAYS AS (
        w1 + w2 + w3 + w4 + w5 + w6 + w7 + w8 + w9
    ) STORED,

    -- deductions
    sss DECIMAL(10,2) GENERATED ALWAYS AS (0.05 * gross_pay) STORED,
    philhealth DECIMAL(10,2) GENERATED ALWAYS AS (0.025 * gross_pay) STORED,
    pagibig DECIMAL(10,2) GENERATED ALWAYS AS (0.02 * gross_pay) STORED,
    cater1 DECIMAL(10,2) DEFAULT 0.00,
    advance DECIMAL(10,2) DEFAULT 0.00,

    -- total deductions formula
    total_deductions DECIMAL(10,2) GENERATED ALWAYS AS (
        sss + philhealth + pagibig + cater1 + advance
    ) STORED,
    
    -- net pay formula
    net_pay DECIMAL(10,2) GENERATED ALWAYS AS (
        gross_pay - total_deductions
    ) STORED,

    CONSTRAINT fk_computation_employee FOREIGN KEY (employee_id)
        REFERENCES employee_info(employee_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_computation_payroll FOREIGN KEY (payroll_id)
        REFERENCES employee_payroll(payroll_id)
        ON DELETE CASCADE
);
