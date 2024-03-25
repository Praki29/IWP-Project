<?php
// Start the session
session_start();

// Check if user is not logged in, redirect to login page
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">User Details</h2>
                        <p id="welcomeMessage">Welcome, <?php echo $_SESSION['user_name']; ?>!</p>
                        <div class="text-right">
                            <a href="index.php" class="mt-3">Back to Homepage</a>
                        </div>
                        <button id="logoutButton" class="btn btn-primary">Logout</button>
                        <div class="mt-3">
                            <a href="edit_username.php" class="btn btn-secondary">Edit Username</a>
                            <a href="edit_password.php" class="btn btn-secondary">Edit Password</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript to handle logout -->
    <script>
        // Add event listener to the logout button
        document.getElementById("logoutButton").addEventListener("click", function() {
            // Remove session details from browser cache
            localStorage.removeItem("user_id");
            localStorage.removeItem("user_name");
            // Redirect to logout page
            window.location.href = "logout.php";
        });
    </script>
</body>
</html>
