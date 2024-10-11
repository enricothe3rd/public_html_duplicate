<?php
session_start(); // Start the session

// Unset the desired session variables
unset($_SESSION['last_semester_name']);
unset($_SESSION['last_start_date']);
unset($_SESSION['last_end_date']);

// Redirect back to the referring page (the page the user came from)
if (isset($_SERVER['HTTP_REFERER'])) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
} else {
    // Fallback if HTTP_REFERER is not set
    header('Location: create_semester.php'); // Default to create_semester.php or your preferred page
    exit();
}
?>
