<?php
require '../../db/db_connection1.php';
session_start();

// Define paths to custom icons
$iconPaths = [
    'success' => '../../assets/images/modal-icons/checked.png',
    'error' => '../../assets/images/modal-icons/cancel.png'
];

$message = '';
$messageType = '';
$customIcon = '';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delete_ids = isset($_POST['delete_ids']) ? $_POST['delete_ids'] : [];

    if (!empty($delete_ids)) {
        // Prepare the DELETE query using placeholders
        $placeholders = implode(',', array_fill(0, count($delete_ids), '?'));

        // Delete selected users
        $stmt = $pdo->prepare("DELETE FROM users WHERE id IN ($placeholders)");
        $stmt->execute($delete_ids);

        // Set success message and icon
        $message = 'Selected users were successfully deleted.';
        $messageType = 'success';
        $customIcon = '<img src="' . $iconPaths['success'] . '" alt="Success Icon" class="w-12 h-12 mx-auto mb-4">';
    } else {
        // No users selected
        $message = 'No users were selected for deletion.';
        $messageType = 'error';
        $customIcon = '<img src="' . $iconPaths['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
    }

    // Store message in session and redirect
    $_SESSION['message'] = $message;
    $_SESSION['messageType'] = $messageType;
    $_SESSION['customIcon'] = $customIcon;
    header('Location: users_management.php');
    exit();
} else {
    // Redirect if not a POST request
    header('Location: users_management.php');
    exit();
}
?>
