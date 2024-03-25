<?php
require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Start session
session_start();

// Connect to MongoDB
$mongoDBURL = $_ENV['MONGODB_URL'];
$mongoClient = new MongoDB\Client($mongoDBURL);
$collection = $mongoClient->selectCollection("mydatabase", "users");

// Get data from POST request
$name = $_POST['name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Insert user data into MongoDB
$insertOneResult = $collection->insertOne([
    'name' => $name,
    'email' => $email,
    'password' => $password
]);

if ($insertOneResult->getInsertedCount() == 1) {
    echo "User registered successfully!";
} else {
    echo "Error registering user.";
}
?>
