<?php
session_start();

// Check if user is not logged in, then redirect to login page
if (!isset ($_SESSION['user_email'])) {
    header("Location: login.php");
    exit;
}


// Load environment variables from .env file
require_once __DIR__ . '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '../');
$dotenv->load();

use MongoDB\Client;

// Get MongoDB connection URL and database name from environment variables
$mongoDBURL = $_ENV['MONGODB_URL'] ?? '';
$databaseName = $_ENV['MONGODB_DATABASE'] ?? '';

// Establish MongoDB connection
$client = new Client($mongoDBURL);

// Select the database
$database = $client->$databaseName;

// Get movie ID from URL parameter
if (isset ($_GET['id'])) {
    $movieId = $_GET['id'];

    // Fetch movie details from the database
    $collection = $database->movies;
    $movie = $collection->findOne(['_id' => new MongoDB\BSON\ObjectID($movieId)]);

    if (!$movie) {
        echo "Invalid movie ID.";
        exit;
    }

    // Retrieve seats collection for the movie
    $seatsCollectionName = 'seats_' . $movieId;
    $seatsCollection = $database->$seatsCollectionName;

    // Define static number of rows and columns
    $rows = $movie['rows'] ?? 10;
    $columns = $movie['columns'] ?? 10;

    // Fetch booked seats from the database
    $bookedSeatsDocument = $seatsCollection->findOne(['_id' => 'booked_seats']);
    $bookedSeatsArray = $bookedSeatsDocument ? $bookedSeatsDocument['seats']->getArrayCopy() : [];


    // Create an array to represent all seats
    $allSeatsArray = [];
    for ($i = 1; $i <= $rows; $i++) {
        for ($j = 1; $j <= $columns; $j++) {
            $seatNumber = 'Seat_' . $i . '_' . $j;
            $isBooked = in_array($seatNumber, $bookedSeatsArray);
            $allSeatsArray[] = ['number' => $seatNumber, 'booked' => $isBooked];
        }
    }

    // Form submission handling
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset ($_POST['selected_seats'])) {
        $selectedSeats = $_POST['selected_seats'];

        // Update booked seats array
        foreach ($selectedSeats as $seat) {
            if (!in_array($seat, $bookedSeatsArray)) {
                $bookedSeatsArray[] = $seat;
            }
        }

        // Update booked seats in the database
        $seatsCollection->updateOne(
            ['_id' => 'booked_seats'],
            ['$set' => ['seats' => $bookedSeatsArray]],
            ['upsert' => true]
        );

        // Redirect to the same booking page after booking
        header("Location: booking.php?id=" . $movieId);
        exit;
    }
} else {
    echo "Movie ID not provided.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Seats</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .seat {
            display: inline-block;
            width: 20px;
            /* Reduced width */
            height: 20px;
            /* Reduced height */
            margin: 2px;
            /* Reduced margin */
            background-color: greenyellow;
            border: 1px solid #888;
            text-align: center;
            line-height: 25px;
            /* Adjusted line height */
            font-size: 10px;
            /* Adjusted font size */
        }

        .booked {
            background-color: #000;
            color: #fff;
            cursor: not-allowed;
        }

        #screen {
            width: 400px;
            /* Adjust width as needed */
            height: 20px;
            /* Adjust height as needed */
            border: 1px solid #000;
            margin: auto;
            margin-bottom: 20px;
            position: relative;
        }

        #screen-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>

<body>


    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">Book Tickets for
            <?php echo $movie['title']; ?>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Home</a>
            </li>


                <?php if (isset ($_SESSION['user_email'])) { ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Welcome,
                            <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="user_details.php">User Details</a>
                            <a class="dropdown-item" href="logout.php">Logout</a>
                        </div>
                    </li>
                <?php } else { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <p>Screen:
            <?php echo $movie['screen']; ?>
        </p>
        <p>Time:
            <?php echo $movie['time']; ?>
        </p>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $movieId; ?>" method="post">
            <label>Select Seats:</label><br>
            <div id="screen">
                <div id="screen-text">Screen</div>
            </div>
            <div class="form-group">
                <?php
                for ($i = 1; $i <= $rows; $i++) {
                    echo '<div class="row">';
                    for ($j = 1; $j <= $columns; $j++) {
                        $seatNumber = 'Seat_' . $i . '_' . $j;
                        $isBooked = in_array($seatNumber, $bookedSeatsArray);
                        $disabled = $isBooked ? 'disabled' : '';
                        $checked = $isBooked ? 'checked' : '';
                        echo '<div class="col">';
                        echo '<div class="seat ' . ($isBooked ? 'booked' : '') . '">';
                        echo '<input type="checkbox" name="selected_seats[]" id="seat_' . $i . '_' . $j . '" value="' . $seatNumber . '" ' . $disabled . ' ' . $checked . '>';
                        echo '</div>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
                ?>
            </div>
            <button type="submit" class="btn btn-primary">Book</button>
        </form>
    </div>


    <!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>