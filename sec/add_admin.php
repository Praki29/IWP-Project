<?php
session_start();

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

// Secret key for admin registration
$secretKey = "praki";

// Form submission handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if secret key matches
    if ($_POST['secret_key'] === $secretKey) {
        // Set session variable to indicate admin access
        $_SESSION['isAdmin'] = true;
        
        // Redirect to admin management page
        header("Location: admin_management.php");
        exit;
    } else {
        $errorMessage = "Invalid secret key!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Add Admin</h2>
                        <div class="text-right">
                            <a href="admin_management.php" class="mt-3">Back to Admin Management</a>
                        </div>
                        <!-- Add admin form -->
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group">
                                <label for="secret_key">Secret Key</label>
                                <input type="password" name="secret_key" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                        <?php if (isset($errorMessage)) { ?>
                            <div class="alert alert-danger mt-3" role="alert">
                                <?php echo $errorMessage; ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
