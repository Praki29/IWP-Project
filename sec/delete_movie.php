<?php
session_start();

// Load environment variables from .env file
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use MongoDB\Client;

// Get MongoDB connection URL and database name from environment variables
$mongoDBURL = $_ENV['MONGODB_URL'] ?? '';
$databaseName = $_ENV['MONGODB_DATABASE'] ?? '';

// Establish MongoDB connection
$client = new Client($mongoDBURL);

// Select the database
$database = $client->$databaseName;

// Select the movies collection
$collection = $database->movies;

// Check if the movie ID is provided in the URL
if (isset ($_GET['id'])) {
    $movieId = $_GET['id'];

    // Convert the movie ID to MongoDB ObjectId
    $objectId = new MongoDB\BSON\ObjectId($movieId);

    // Delete the movie from the collection
    $deleteResult = $collection->deleteOne(['_id' => $objectId]);

    // Check if the movie was successfully deleted
    if ($deleteResult->getDeletedCount() > 0) {
        $successMessage = "Movie deleted successfully!";
    } else {
        $errorMessage = "Failed to delete movie.";
    }
} else {
    // If movie ID is not provided, redirect to admin management page
    header("Location: admin_management.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Movie</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Delete Movie</h2>
                        <!-- Display success or error message -->
                        <?php if (isset ($successMessage)) { ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $successMessage; ?>
                            </div>
                        <?php } ?>
                        <?php if (isset ($errorMessage)) { ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $errorMessage; ?>
                            </div>
                        <?php } ?>
                        <div class="text-right">
                            <a href="add_movies.php" class="mt-3">Back to Admin Management</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>