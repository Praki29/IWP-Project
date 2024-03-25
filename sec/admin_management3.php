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

// Select database and collection for movies
$moviesCollection = $client->$dbName->movies;

// Function to create a new movie card
function createMovieCard($collection, $movieName, $posterUrl) {
    $insertData = ['movie_name' => $movieName, 'poster_url' => $posterUrl];
    $result = $collection->insertOne($insertData);
    return $result->getInsertedId();
}

// Handle form submission for adding a new movie card
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_movie'])) {
    // Get movie details from the form
    $movieName = $_POST['movie_name'];
    $posterUrl = $_POST['poster_url'];

    // Create a new movie card in MongoDB
    $movieId = createMovieCard($moviesCollection, $movieName, $posterUrl);

    // Redirect back to the admin management page after adding the movie card
    header("Location: admin_management.php");
    exit;
}

// Fetch all movies
$movies = $moviesCollection->find();
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

                <!-- Add Movie Card Form -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Add Movie Card</h5>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group">
                                <label for="movie_name">Movie Name</label>
                                <input type="text" class="form-control" id="movie_name" name="movie_name" required>
                            </div>
                            <div class="form-group">
                                <label for="poster_url">Poster URL</label>
                                <input type="url" class="form-control" id="poster_url" name="poster_url" required>
                            </div>
                            <button type="submit" name="add_movie" class="btn btn-primary">Add Movie Card</button>
                        </form>
                    </div>
                </div>

                <!-- Display Existing Movies -->
                <div class="mt-5">
                    <h3>Existing Movies</h3>
                    <div class="row">
                        <?php foreach ($movies as $movie) { ?>
                            <div class="col-md-4">
                                <div class="card mt-3">
                                    <img src="<?php echo $movie['poster_url']; ?>" class="card-img-top" alt="Movie Poster">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $movie['movie_name']; ?></h5>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
