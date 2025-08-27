<?php
// Start the session (for storing login status if needed)
session_start();

// Check if the user is already logged in (Optional)
if (isset($_SESSION['username'])) {
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
        $_SESSION['username'] = $username;
        header('Location: dashboard.php');  // Replace with your desired page
        exit();
    } else {
        $error_message = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../css/authentication.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Login</title>
</head>
<body>

<div class="circle small"></div>
<div class="circle small two"></div>
<div class="circle medium"></div>
<div class="circle medium three"></div>

<div id="parent_div">
    <div class="login-container">
        <!-- Replace the logo image URL with your actual logo -->
<img src="../images/logo_authentication.png" alt="Logo">
        
        <!-- Display error message if credentials are wrong -->
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Login Form -->
        <form id="details_form" method="POST" action="">
            <div id="elements_in_form">
                <input id="detail" type="text" name="username" placeholder="Username" required>
            </div>
            <div id="elements_in_form">
                <input id="detail" type="password" name="password" placeholder="Password" required>
            </div>
            <div id="elements_in_form">
                <button id="btn_detail" type="submit">Login</button>
            </div>
        </form>

        <a href="#">Forgot Password?</a>
    </div>
</div>

</body>
</html>
