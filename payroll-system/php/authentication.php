<?php
// Start the session (for storing login status if needed)
session_start();

// Check if the user is already logged in (Optional)
if (isset($_SESSION['loggedin'])) {
    header('Location: dashboard.php'); // Redirect to dashboard if logged in
    exit();
}

// Handle the login logic (this should be improved with better validation and security like hashed passwords)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the login credentials from the POST request
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Sample credentials (you should replace this with a database check)
    $valid_username = "admin";  // Example username
    $valid_password = "password123";  // Example password

    // Check if the entered credentials match the valid ones
    if ($username === $valid_username && $password === $valid_password) {
        // Set session variable and redirect to dashboard
        $_SESSION['loggedin'] = true;
    header('Location: dashboard.php');  // Replace with your desired page
        exit();
    } else {
        $error_message = "Invalid username or password.";
    }
}
?>

<?php include '../html/authentication.html'; ?>
