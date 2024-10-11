<?php
// Include the database connection file
require '../../db/db_connection1.php';
session_start();

// Define custom icons for each message type
$icons = [
    'success' => '../../assets/images/modal-icons/checked.png', // Replace with the path to your success icon
    'error' => '../../assets/images/modal-icons/cancel.png'     // Replace with the path to your error icon
];

if (isset($_POST['delete_ids'])) {
    $ids = $_POST['delete_ids'];

    // Prepare the SQL statement with placeholders
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // Prepare the deletion query
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE id IN ($placeholders)");

    try {
        // Execute the statement
        $stmt->execute($ids);

        // Set success message and custom icon in session
        $_SESSION['message'] = 'Selected password resets have been successfully deleted.';
        $_SESSION['messageType'] = 'success';
        $_SESSION['customIcon'] = '<img src="' . $icons['success'] . '" alt="Success Icon" class="w-12 h-12 mx-auto mb-4">';
    } catch (PDOException $e) {
        // Set error message and custom icon in session if something goes wrong
        $_SESSION['message'] = 'An error occurred while deleting the password resets.';
        $_SESSION['messageType'] = 'error';
        $_SESSION['customIcon'] = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
    }

    // Redirect back to password reset management page
    header("Location: password_reset_management.php");
    exit();
} else {
    // No items were selected
    $_SESSION['message'] = 'No items were selected for deletion.';
    $_SESSION['messageType'] = 'error';
    $_SESSION['customIcon'] = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';

    // Redirect back to password reset management page
    header("Location: password_reset_management.php");
    exit();
}
?>
