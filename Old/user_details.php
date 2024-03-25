<?php
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_name'])) {
    // If user is not logged in, redirect to login page
    header("Location: login.html");
    exit;
}

// Connect to MongoDB
$mongoDBURL = $_ENV['MONGODB_URL'];
$mongoClient = new MongoDB\Client($mongoDBURL);
$collection = $mongoClient->selectCollection("mydatabase", "users");

// Get user details from MongoDB
$user = $collection->findOne(['name' => $_SESSION['user_name']]);

// Display user details
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
</head>
<body>
    <h2>User Details</h2>
    <?php
    if ($user) {
        echo "<p>Welcome, {$user['name']}!</p>";
        echo '<p><a href="logout.php">Logout</a></p>';
    } else {
        echo "<p>User not found.</p>";
    }
    ?>
</body>
</html>
