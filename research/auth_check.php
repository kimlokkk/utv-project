<?php
session_start();
include '../db_connect/db_connect.php'; // Adjust path if needed

// Check if the user is not logged in or doesn't have the required role
if (!isset($_SESSION['Research Assistant'])) {
    echo '<script>
            alert("You are not authorized to access this page. Please log in as Research Assistant.");
            window.location.href = "/index.php"; // Redirect to the login page
          </script>';
    exit(); // Stop script execution
}

// If the user is logged in, fetch user data from the database
$email = $_SESSION['Research Assistant'];

// Prepare and execute the query to fetch user data
$query = "SELECT * FROM research_assistant WHERE email = '$email'";
$result = mysqli_query($db, $query);

// Fetch the data if the query is successful
if ($result) {
    $userData = mysqli_fetch_assoc($result); // Retrieve user data as an associative array

    // Optionally, store user data in the session or a global variable
    $_SESSION['user_data'] = $userData; // Save entire user data array to session
} else {
    echo '<script>alert("Error fetching user data.");</script>';
    exit();
}
?>
