<?php

// Check if the session email is not set, redirect to spinner.php
if (!isset($_SESSION['user_email'])) {
    header("Location: spinner.php");
    exit(); // Stop further execution
}

// Include the Database class file
require_once 'db/db_connection3.php'; // Adjust the path to your Database class

// Call the connect method to get PDO instance
$pdo = Database::connect();

// Fetch the email from the session
$email = $_SESSION['user_email'];

// Query the users table to check email confirmation status
$sql = "SELECT email_confirmed FROM users WHERE email = :email LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);

// Execute the query
$stmt->execute();
$userConfirmation = $stmt->fetch(PDO::FETCH_ASSOC);

// If the email is not confirmed, redirect to spinner.php
if ($userConfirmation['email_confirmed'] == 0) {
    header("Location: spinner.php");
    exit(); // Stop further execution
}



// Query the users table to fetch the role based on the email
$sql = "SELECT role FROM users WHERE email = :email LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);

// Execute the query
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If a record is found, set the role in the session
if ($user) {
    $_SESSION['role'] = $user['role'];
} else {
    // If no role is found, redirect to an error or login page
    header("Location: spinner.php");
    exit(); // Stop further execution
}

// Fetch the role from the session
$role = $_SESSION['role'];

// Check if any of the session variables are missing
if ( !$email || !$role) {
    // Redirect to spinner.php if any session variable is missing
    header("Location: spinner.php");
    exit(); // Stop further execution
}
?>
