<?php
// Include database connection
require '../../db/db_connection3.php'; // Ensure you have your DB connection

// Get the PDO instance from your Database class
$pdo = Database::connect();

// Check if the ID is set in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Prepare and execute the delete statement
        $stmt = $pdo->prepare("DELETE FROM payments WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Redirect to the payments overview after deletion
        header("Location: view_payments.php");
        exit();
    } catch (PDOException $e) {
        die("Could not delete payment: " . $e->getMessage());
    }
} else {
    // If no ID is provided, redirect back to payments overview
    header("Location: view_payments.php");
    exit();
}
