<?php
session_start();

// Check if user is an admin
if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === true) {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to the login page
    header("Location: ./login.php");
    exit;
} else {
    // If the user is not an admin, redirect to an appropriate page
    header("Location: ../index.php"); // Or any other page
    exit;
}
?>
