<?php

// Fetch the student_number and email from the session
$student_number = $_SESSION['student_number'] ?? null;
$email = $_SESSION['user_email'] ?? null;

// Set 'user_role' to 'student' if it's not already set
if (!isset($_SESSION['user_role'])) {
    $_SESSION['user_role'] = 'student';
}

$role = $_SESSION['user_role'];

// Check if any of the session variables are missing
if (!$student_number || !$email || !$role) {
    // Redirect to spinner.php if any session variable is missing
    header("Location: spinner.php");
    exit(); // Stop further execution
}

?>