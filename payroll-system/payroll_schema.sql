CREATE TABLE employee_info_and_rates (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    last_name VARCHAR(20) NOT NULL,
    first_name VARCHAR(20) NOT NULL,
    position VARCHAR(100) NOT NULL,
    status ENUM('Permanent', 'On-Call') NOT NULL,
    board_lodging ENUM('Yes', 'No') NOT NULL DEFAULT 'No',
    lodging_address VARCHAR(255) DEFAULT NULL,
    food_allowance ENUM('Full', 'Partial', 'None') NOT NULL DEFAULT 'Full',
    rate_1_daily_minimum_wage DECIMAL(10, 2) DEFAULT 470.00,
    rate_2_sunday_rest_day DECIMAL(10, 2) DEFAULT 611.00,
    rate_3_legal_holiday DECIMAL(10, 2) DEFAULT 940.00,
    rate_4_special_holiday DECIMAL(10, 2) DEFAULT 611.00,
    rate_5_regular_overtime_perhour DECIMAL(10, 2) DEFAULT 73.44,
    rate_6_special_overtime_perhour DECIMAL(10, 2) DEFAULT 76.38,
    rate_7_special_holiday_overtime_perhour DECIMAL(10, 2) DEFAULT 99.29,
    rate_8_regular_holiday_overtime_perhour DECIMAL(10, 2) DEFAULT 152.75,
    rate_9_cater DECIMAL(10, 2) DEFAULT 1000.00
);

CREATE TABLE payroll_dates (
    payroll_id INT AUTO_INCREMENT PRIMARY KEY,
    week VARCHAR(50) NOT NULL
);

INSERT INTO payroll_dates (week) VALUES
('January 1 - 15'),
('January 16 - 31'),
('February 1 - 15'),
('February 16 - 29'),
('March 1 - 15'),
('March 16 - 31'),
('April 1 - 15'),
('April 16 - 30'),
('May 1 - 15'),
('May 16 - 31'),
('June 1 - 15'),
('June 16 - 30'),
('July 1 - 15'),
('July 16 - 31'),
('August 1 - 15'),
('August 16 - 31'),
('September 1 - 15'),
('September 16 - 30'),
('October 1 - 15'),
('October 16 - 31'),
('November 1 - 15'),
('November 16 - 30'),
('December 1 - 15'),
('December 16 - 31');

CREATE TABLE payroll_transactions (
    year INT NOT NULL,  -- stores the chosen year
    payroll_id INT NOT NULL,
    employee_id INT NOT NULL,
    num_of_days_for_rate_1 INT UNSIGNED DEFAULT 0,
    num_of_days_for_rate_2 INT UNSIGNED DEFAULT 0,
    num_of_days_for_rate_3 INT UNSIGNED DEFAULT 0,
    num_of_days_for_rate_4 INT UNSIGNED DEFAULT 0,
    num_of_hours_for_rate_5 INT UNSIGNED DEFAULT 0,
    num_of_hours_for_rate_6 INT UNSIGNED DEFAULT 0,
    num_of_hours_for_rate_7 INT UNSIGNED DEFAULT 0,
    num_of_hours_for_rate_8 INT UNSIGNED DEFAULT 0,
    num_of_days_for_rate_9 INT UNSIGNED DEFAULT 0,
    cater_deductions DECIMAL(10,2) DEFAULT 0.00,
    advance_deductions DECIMAL(10,2) DEFAULT 0.00,

    FOREIGN KEY (employee_id) REFERENCES employee_info_and_rates (employee_id),
    FOREIGN KEY (payroll_id) REFERENCES payroll_dates (payroll_id),
    PRIMARY KEY (year, payroll_id, employee_id), -- composite key
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE archived_employees (
    year INT NOT NULL,  -- stores the chosen year
    employee_id INT PRIMARY KEY,    
    payroll_id INT,
    last_name VARCHAR(20) NOT NULL,
    first_name VARCHAR(20) NOT NULL,
    position VARCHAR(100) NOT NULL,
    status ENUM('Permanent', 'On-Call') NOT NULL,
    board_lodging ENUM('Yes', 'No') NOT NULL DEFAULT 'No',
    lodging_address VARCHAR(255) DEFAULT NULL,
    food_allowance ENUM('Full', 'Partial', 'None') NOT NULL DEFAULT 'Full',
    rate_1_daily_minimum_wage DECIMAL(10, 2) DEFAULT 470.00,
    rate_2_sunday_rest_day DECIMAL(10, 2) DEFAULT 611.00,
    rate_3_legal_holiday DECIMAL(10, 2) DEFAULT 940.00,
    rate_4_special_holiday DECIMAL(10, 2) DEFAULT 611.00,
    rate_5_regular_overtime_perhour DECIMAL(10, 2) DEFAULT 73.44,
    rate_6_special_overtime_perhour DECIMAL(10, 2) DEFAULT 76.38,
    rate_7_special_holiday_overtime_perhour DECIMAL(10, 2) DEFAULT 99.29,
    rate_8_regular_holiday_overtime_perhour DECIMAL(10, 2) DEFAULT 152.75,
    rate_9_cater DECIMAL(10, 2) DEFAULT 1000.00,
    deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
