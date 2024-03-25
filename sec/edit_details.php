<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== true) {
    header("Location: ../login.php"); // Redirect to login page if not an admin
    exit;
}

// Load environment variables from .env file
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use MongoDB\Client;

// MongoDB connection
$mongoDBURL = $_ENV['MONGODB_URL'];
$client = new Client($mongoDBURL);

// Database name
$dbName = $_ENV['MONGODB_DATABASE'];

// Select database and collection
$collection = $client->$dbName->users;

// Function to get user details by ID
function getUserById($collection, $userId) {
    return $collection->findOne(['_id' => new MongoDB\BSON\ObjectID($userId)]);
}

// Function to update username
function updateUsername($collection, $userId, $newUsername) {
    $updateData = ['$set' => ['username' => $newUsername]];
    $result = $collection->updateOne(['_id' => new MongoDB\BSON\ObjectID($userId)], $updateData);
    return $result->getModifiedCount() > 0;
}

// Function to update password
function updatePassword($collection, $userId, $newPassword) {
    $updateData = ['$set' => ['password' => $newPassword]];
    $result = $collection->updateOne(['_id' => new MongoDB\BSON\ObjectID($userId)], $updateData);
    return $result->getModifiedCount() > 0;
}

// Handle form submission for updating username
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_username'])) {
    $userId = $_GET['id']; // Get user ID from URL
    $newUsername = $_POST['new_username'];
    
    if (updateUsername($collection, $userId, $newUsername)) {
        $updateSuccess = true;
    } else {
        $updateError = "Failed to update username.";
    }
}

// Handle form submission for updating password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $userId = $_GET['id']; // Get user ID from URL
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmNewPassword = $_POST['confirm_new_password'];

    // Check if current password matches the one in the database
    $user = getUserById($collection, $userId);
    if (password_verify($currentPassword, $user['password'])) {
        // Check if new password and confirm new password match
        if ($newPassword === $confirmNewPassword) {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            // Update password
            if (updatePassword($collection, $userId, $hashedPassword)) {
                $updateSuccess = true;
            } else {
                $updateError = "Failed to update password.";
            }
        } else {
            $updateError = "New password and confirm new password do not match.";
        }
    } else {
        $updateError = "Current password is incorrect.";
    }
}

// Fetch user details by ID
$user = getUserById($collection, $_GET['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Details</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Edit User Details</h2>
                        <?php if (isset($updateSuccess)) { ?>
                            <div class="alert alert-success" role="alert">
                                Update successful.
                            </div>
                        <?php } ?>
                        <?php if (isset($updateError)) { ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $updateError; ?>
                            </div>
                        <?php } ?>
                        <!-- Form to update username -->
                        <div class="text-right">
                            <a href="admin_management.php" class="mt-3">Back to Admin Management</a>
                        </div>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $user['_id']; ?>" method="post">
                            <div class="form-group">
                                <label for="new_username">New Username</label>
                                <input type="text" name="new_username" class="form-control" value="<?php echo $user['username']; ?>" required>
                            </div>
                            <button type="submit" name="update_username" class="btn btn-primary">Update Username</button>
                        </form>
                        <hr>
                        <!-- Form to update password -->
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $user['_id']; ?>" method="post">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_new_password">Confirm New Password</label>
                                <input type="password" name="confirm_new_password" class="form-control" required>
                            </div>
                            <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
