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

// Function to fetch all users
function getAllUsers($collection) {
    return $collection->find([]);
}

// Function to update user data
function updateUser($collection, $userId, $newUsername, $newPassword, $isAdmin) {
    $updateData = ['$set' => ['username' => $newUsername, 'password' => $newPassword, 'isAdmin' => $isAdmin]];
    $result = $collection->updateOne(['_id' => new MongoDB\BSON\ObjectID($userId)], $updateData);
    return $result->getModifiedCount() > 0;
}

// Function to delete a user
function deleteUser($collection, $userId) {
    $result = $collection->deleteOne(['_id' => new MongoDB\BSON\ObjectID($userId)]);
    return $result->getDeletedCount() > 0;
}

// Function to register a new user
function registerUser($collection, $username, $email, $password, $isAdmin) {
    $insertData = ['username' => $username, 'email' => $email, 'password' => $password, 'isAdmin' => $isAdmin];
    $result = $collection->insertOne($insertData);
    return $result->getInsertedId() ? true : false;
}

// Form submission handling for updating user data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $userId = $_POST['user_id'];
    $newUsername = $_POST['new_username'];
    $newPassword = $_POST['new_password'];
    $isAdmin = isset($_POST['is_admin']) ? true : false;
    
    if (updateUser($collection, $userId, $newUsername, $newPassword, $isAdmin)) {
        $updateSuccess = true;
    } else {
        $updateError = "Failed to update user data.";
    }
}

// Form submission handling for deleting a user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $userId = $_POST['user_id'];
    
    if (deleteUser($collection, $userId)) {
        $deleteSuccess = true;
    } else {
        $deleteError = "Failed to delete user.";
    }
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

// Fetch all users
$users = getAllUsers($collection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <h2 class="mb-3">Admin Management</h2>

                <!-- Display success messages if any -->
                <?php if (isset($updateSuccess)) { ?>
                    <div class="alert alert-success" role="alert">
                        User data updated successfully.
                    </div>
                <?php } ?>
                <?php if (isset($deleteSuccess)) { ?>
                    <div class="alert alert-success" role="alert">
                        User deleted successfully.
                    </div>
                <?php } ?>
                <?php if (isset($registerSuccess)) { ?>
                    <div class="alert alert-success" role="alert">
                        User registered successfully.
                    </div>
                <?php } ?>

                <!-- Display error messages if any -->
                <?php if (isset($updateError)) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $updateError; ?>
                    </div>
                <?php } ?>
                <?php if (isset($deleteError)) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $deleteError; ?>
                    </div>
                <?php } ?>
                <?php if (isset($registerError)) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $registerError; ?>
                    </div>
                <?php } ?>

                <!-- Users Table -->
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Username</th>
                            <th scope="col">Email</th>
                            <th scope="col">Is Admin</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) { ?>
                            <tr>
                                <td><?php echo $user['username']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['isAdmin'] ? 'Yes' : 'No'; ?></td>
                                <td>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                        <input type="hidden" name="user_id" value="<?php echo $user['_id']; ?>">
                                        <div class="btn-group" role="group" aria-label="User Actions">
                                            <button type="submit" name="update" class="btn btn-primary btn-sm">Update</button>
                                            <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <!-- Register New User Form -->
                <div class="card mt-5">
                    <div class="card-body">
                        <h5 class="card-title">Register New User</h5>
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
                            <button type="submit" name="register" class="btn btn-primary mt-3">Register</button
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
