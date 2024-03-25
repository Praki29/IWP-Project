<?php
session_start();

// Check if the user ID is stored in the browser session
if (!isset($_SESSION['user_id'])) {
    // If not, redirect the user to the login page or display an error message
    // Redirecting to the login page
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

// Form submission handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $new_username = $_POST['new_username'];

    // Check if the 'user_id' session variable is set
    if (isset($_SESSION['user_id'])) {
        // Update user's username in MongoDB
        $result = $collection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectID($_SESSION['user_id'])],
            ['$set' => ['username' => $new_username]]
        );

        // Check if the update was successful
        if ($result->getModifiedCount() > 0) {
            // Update user's username in session
            $_SESSION['user_name'] = $new_username;

            // Redirect back to user details page
            header("Location: user_details.php");
            exit;
        } else {
            echo "Failed to update username.";
        }
    } else {
        echo "User session not found. Please login again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Username</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Edit Username</h2>
                        <!-- Update username form -->
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group">
                                <label for="new_username">New Username</label>
                                <input type="text" name="new_username" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Username</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
