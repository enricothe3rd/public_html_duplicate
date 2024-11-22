<?php
// Start the session at the beginning of the script
session_start();

// Check if the session email is not set, redirect to spinner.php
if (!isset($_SESSION['user_email'])) {
    header("Location: spinner.php");
    exit(); // Stop further execution
}

// Include the Database class file
require_once 'db/db_connection3.php'; // Adjust the path to your Database class

// Call the connect method to get PDO instance
$pdo = Database::connect();

// Fetch the email from the session (do not overwrite session variables here)
$email = $_SESSION['user_email'];

// Query the users table to check email confirmation status
$sql = "SELECT email_confirmed, role FROM users WHERE email = :email LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);

// Execute the query
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user exists and their email is confirmed
if (!$user || $user['email_confirmed'] == 0) {
    header("Location: spinner.php");
    exit(); // Stop further execution
}

// Set the session role if not already set
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = $user['role'];
}

// Fetch the role from the session
$role = $_SESSION['role'];

// Check if the role is not 'admin', redirect to spinner.php
if ($role !== 'college_department') {
    header("Location: spinner.php");
    exit(); // Stop further execution
}

// Now check if the instructor exists in instructor_subjects based on the user's email
$sql = "
    SELECT 
        i.id AS instructor_id, 
        i.first_name, 
        i.last_name, 
        i.email
    FROM 
        instructors i
    JOIN 
        instructor_subjects isub ON i.id = isub.instructor_id
    WHERE 
        i.email = :user_email;
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_email', $email, PDO::PARAM_STR);
$stmt->execute();

$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if instructor exists
if (!$result) {
    // Instructor does not exist, redirect to spinner.php
    header("Location: spinner.php");
    exit(); // Stop further execution
}

// Continue with your script logic...
?>
