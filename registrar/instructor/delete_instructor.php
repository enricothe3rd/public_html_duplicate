<?php
require 'Instructor.php';

// Get the PDO connection
$pdo = Database::connect();

// Create an instance of the Instructor class
$instructor = new Instructor($pdo);

// Ensure the `id` parameter is present and valid
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Call the delete method and pass the `id`
    if ($instructor->delete($id)) {
        // Redirect to the instructor list page after successful deletion
        header('Location: read_instructors.php');
        exit();
    } else {
        // Display error message if deletion fails
        echo "Failed to delete the instructor. Please try again. <a href='read_instructors.php'>Go Back to Instructor List</a>";
    }
} else {
    // Display error message if no valid `id` is provided
    echo "Invalid instructor ID. Please check the link and try again. <a href='read_instructors.php'>Go Back to Instructor List</a>";
}
?>