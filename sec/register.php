<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== true) {
    header("Location: login.php");
    exit;
}

// Load environment variables from .env file
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use MongoDB\Client;

// MongoDB connection
$mongoDBURL = $_ENV['MONGODB_URL'];
$client = new Client($mongoDBURL);

// Database name
$dbName = $_ENV['MONGODB_DATABASE'];

// Select database and collection
$collection = $client->$dbName->users;

// Function to register a new user
function registerUser($collection, $username, $email, $password, $isAdmin) {
    $insertData = ['username' => $username, 'email' => $email, 'password' => $password, 'isAdmin' => $isAdmin];
    $result = $collection->insertOne($insertData);
    return $result->getInsertedId() ? true : false;
}

// Form submission handling for registering a new user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $isAdmin = isset($_POST['is_admin']) ? true : false;
    
    if (registerUser($collection, $username, $email, $password, $isAdmin)) {
        $registerSuccess = true;
    } else {
        $registerError = "Failed to register user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Register New User</h2>
                        <!-- Register form -->
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_admin" id="is_admin">
                                <label class="form-check-label" for="is_admin">
                                    Is Admin
                                </label>
                            </div>
                            <button type="submit" name="register" class="btn btn-primary mt-3">Register</button>
                        </form>
                        <?php if (isset($registerSuccess)) { ?>
                            <div class="alert alert-success mt-3" role="alert">
                                User registered successfully.
                            </div>
                        <?php } ?>
                        <?php if (isset($registerError)) { ?>
                            <div class="alert alert-danger mt-3" role="alert">
                                <?php echo $registerError; ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
