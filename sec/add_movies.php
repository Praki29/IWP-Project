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

// Form submission handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $title = $_POST['title'];
    $screen = $_POST['screen'];
    $time = $_POST['time'];
    $posterUrl = $_POST['poster_url'];
    $rows = $_POST['rows'];
    $columns = $_POST['columns'];

    // Insert movie data into MongoDB collection
    $insertResult = $collection->insertOne([
        'title' => $title,
        'screen' => $screen,
        'time' => $time,
        'poster_url' => $posterUrl,
        'rows' => $rows,
        'columns' => $columns
    ]);

    // Check if the movie was successfully added
    if ($insertResult->getInsertedCount() > 0) {
        $successMessage = "Movie added successfully!";
        
        // Create seats collection for the new movie
        $seatsCollectionName = 'seats_' . $insertResult->getInsertedId();
        $database->createCollection($seatsCollectionName);
    } else {
        $errorMessage = "Failed to add movie.";
    }
}

// Fetch all movies from the collection
$movies = $collection->find();

// Convert MongoDB cursor to array
$movieList = iterator_to_array($movies);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Movie</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Manage Movie</h2>
                        <div class="text-right">
                            <a href="admin_management.php" class="mt-3">Back to Admin Management</a>
                        </div>
                        <!-- Add movie form -->
                        <h3 class="card-title">Add Movie</h3>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="screen">Screen</label>
                                <input type="text" name="screen" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="time">Time</label>
                                <input type="text" name="time" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="poster_url">Poster URL</label>
                                <input type="url" name="poster_url" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="rows">Rows</label>
                                <input type="number" name="rows" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="columns">Columns</label>
                                <input type="number" name="columns" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Movie</button>
                        </form>
                        <!-- Display success or error message -->
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

                        <!-- Display added movies -->
                        <?php if (!empty($movieList)) { ?>
                            <h3 class="mt-4">Movies List</h3>
                            <ul class="list-group">
                                <?php foreach ($movieList as $movie) { ?>
                                    <li class="list-group-item">
                                        <?php echo $movie['title']; ?> - 
                                        <a href="delete_movie.php?id=<?php echo $movie['_id']; ?>" class="text-danger">Delete</a>
                                    </li>
                                <?php } ?>
                            </ul>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
