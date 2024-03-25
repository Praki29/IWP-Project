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
$collection = $client->$dbName->movies;

// Form submission handling for adding movie with custom seats
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $movieName = $_POST['movie_name'];
    $posterUrl = $_POST['poster_url'];
    $rows = $_POST['rows'];
    $columns = $_POST['columns'];
    
    // Insert movie data with custom seats
    $insertData = ['movie_name' => $movieName, 'poster_url' => $posterUrl, 'seats' => ['rows' => $rows, 'columns' => $columns, 'booked_seats' => []]];
    $result = $collection->insertOne($insertData);
    
    if ($result->getInsertedId()) {
        $successMessage = "Movie added successfully.";
    } else {
        $errorMessage = "Failed to add movie.";
    }
}
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
            <div class="col-md-6">
                <h2>Add Movie with Custom Seats</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="movie_name">Movie Name</label>
                        <input type="text" class="form-control" id="movie_name" name="movie_name" required>
                    </div>
                    <div class="form-group">
                        <label for="poster_url">Poster URL</label>
                        <input type="url" class="form-control" id="poster_url" name="poster_url" required>
                    </div>
                    <div class="form-group">
                        <label for="rows">Number of Rows</label>
                        <input type="number" class="form-control" id="rows" name="rows" required>
                    </div>
                    <div class="form-group">
                        <label for="columns">Number of Columns</label>
                        <input type="number" class="form-control" id="columns" name="columns" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Movie</button>
                </form>
                <?php if (isset($successMessage)) { ?>
                    <div class="alert alert-success mt-3" role="alert">
                        <?php echo $successMessage; ?>
                    </div>
                <?php } ?>
                <?php if (isset($errorMessage)) { ?>
                    <div class="alert alert-danger mt-3" role="alert">
                        <?php echo $errorMessage; ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>
