<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php
    // Check if error parameter is set in URL
    if (isset($_GET['error'])) {
        // Check the value of the error parameter
        $errorMessage = ($_GET['error'] == 1) ? "Invalid Credentials" : "Account not created";
        echo "<p style='color: red;'>$errorMessage</p>";
    }
    ?>
    <form method="POST" action="login.php">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" value="Login">
    </form>
</body>
</html>


<?php
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Start session
session_start();

// Connect to MongoDB
$mongoDBURL = $_ENV['MONGODB_URL'];
$mongoClient = new MongoDB\Client($mongoDBURL);
$collection = $mongoClient->selectCollection("mydatabase", "users");

// Get data from POST request
$email = $_POST['email'];
$password = $_POST['password'];

// Find user in MongoDB
$user = $collection->findOne(['email' => $email]);

// Check if user exists and password is correct
if ($user && password_verify($password, $user['password'])) {
    // Set session variables
    $_SESSION['user_name'] = $user['name'];
    
    // Redirect to user details page
    header("Location: user_details.php");
    exit;
} else {
    // Redirect back to login page with error message
    header("Location: login.html?error=1");
    exit;
}


?>

