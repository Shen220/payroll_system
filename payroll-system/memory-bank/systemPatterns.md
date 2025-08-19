# System Patterns

## Architecture
The system follows a traditional client-server architecture, with PHP handling server-side logic and MySQL as the database. The frontend is built using HTML, CSS, and JavaScript.

## Key Technical Decisions
- **PHP for Backend:** Chosen for its simplicity and widespread use in web development.
- **MySQL for Database:** A robust and popular relational database management system.
- **GET Parameters for ID Passing:** Used for operations like editing and deleting specific records.
- **Session-based Authentication:** Basic session management for user login.

## Design Patterns in Use
- **Model-View-Controller (MVC) (Partial):** While not a strict MVC framework, there's a separation of concerns with PHP scripts acting as controllers/models and HTML files as views.
- **Database Abstraction (Basic):** Using `mysqli` prepared statements for database interactions.

## Component Relationships
- `index.php`: Login page, redirects to `dashboard.php` on successful login.
- `dashboard.php`: Displays employee list, provides navigation to add, edit, delete, export, and logout functionalities.
- `add.php`: Form for adding new employee records.
- `edit.php`: Form for editing existing employee records.
- `delete.php`: Handles deletion of employee records.
- `payslip.php`: Generates payslips for employees.
- `db.php`: Centralized database connection.
- `css/`: Contains styling for different parts of the application.
- `images/`: Stores application logos and other images.
