# Active Context

## Current Work Focus
Implementing new features for employee data management, specifically adding an hourly rate input and automating non-taxable deduction calculations. Currently focused on resolving dependency management issues for the PhpSpreadsheet library.

## Recent Changes
- **Fixed Redirect Loop:** Modified `php/authentication.php` to use `$_SESSION['loggedin']` consistently for login status checks, resolving the infinite redirect issue.
- **Resolved `session_start()` Notice:** Added a check `if (session_status() == PHP_SESSION_NONE)` in `php/rates.php` to prevent the `session_start()` notice when a session is already active.
- **Fixed Delete Functionality:** Modified `php/delete.php` to correctly handle multiple employee IDs passed via the `ids` GET parameter from `php/dashboard.php`. This resolved the "No employee ID specified" error.
- **Updated Add Employee Form (`php/add.php`):**
    - Added an "Hour Rate" input field.
    - Removed manual input fields for SSS, Pag-IBIG, and PhilHealth deductions.
    - Implemented PHP logic to automatically calculate SSS (5%), Pag-IBIG (2%), and PhilHealth (2.5%) based on `hour_rate * 160` (monthly salary assumption).
    - Updated the `INSERT` query and `bind_param` to accommodate the new `hour_rate` and calculated deduction fields.
- **Composer and PhpSpreadsheet Installation Troubleshooting:**
    - **PHP PATH configured:** Guided user to add `C:\xampp\php` to system's PATH.
    - **`php.ini` updated:** Corrected `curl.cainfo` and `openssl.cafile` to point to `cacert.pem`. Enabled `gd` and `zip` extensions.
    - **Composer TLS disabled:** Configured Composer globally to bypass SSL/TLS verification (`php composer.phar config -g -- disable-tls true`).
    - **Git Ownership fixed:** Added project directory to Git's safe list (`git config --global --add safe.directory C:/xampp/htdocs/payroll_system`).
    - **`composer.json` created:** Added `composer.json` with `phpoffice/phpspreadsheet` dependency.
    - **Persistent Composer Installation Failure:** Despite numerous attempts, `php composer.phar install` and `php composer.phar update` consistently reported "Nothing to install, update or remove" and failed to create the `vendor` directory, indicating a deep environmental/permissions issue preventing file writes.
- **Decision for Manual Installation:** Due to Composer's inability to install files, a manual installation of PhpSpreadsheet and its core dependencies has been initiated.
- **Added "Archive" button to dashboard:** Added an archive button to the dashboard header.

## Next Steps
- **Complete Manual PhpSpreadsheet Installation:** User is currently in the process of manually creating the `vendor` directory structure, downloading PhpSpreadsheet and its dependencies, and placing them in the correct locations.
- **Create `vendor/autoload.php`:** User needs to create this file manually as part of the manual installation.
- **Include Autoloader in `php/export.php`:** Once manual installation is complete, `php/export.php` needs to include `vendor/autoload.php` to resolve the "PhpSpreadsheet library not found" error.
- **Database Schema Update:** The `employee_info` table in the database needs to be updated to include the new columns:
    - `hour_rate` (e.g., `DECIMAL(10,2)`)
    - `sss_deduction` (e.g., `DECIMAL(10,2)`)
    - `pagibig_deduction` (e.g., `DECIMAL(10,2)`)
    - `philhealth_deduction` (e.g., `DECIMAL(10,2)`)
- **Display Deductions:** Update `php/dashboard.php` or `php/payslip.php` to display the newly calculated deductions.
- **Edit Functionality:** Update `php/edit.php` to handle the new `hour_rate` and calculated deductions.
- **Copied CSS to archive.css:** Copied relevant CSS styles from `dashboard.css` to `archive.css`, including styles for `#header_container`.
