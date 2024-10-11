<?php
// Include database connection
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

// Check if the request is a POST request and delete_ids is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ids'])) {
    $delete_ids = $_POST['delete_ids'];

    if (empty($delete_ids)) {
        // No checkboxes were selected
        $message = 'No users selected for deletion.';
        $messageType = 'error';
        $customIcon = '<img src="' . $iconPaths['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
        $_SESSION['message'] = $message;
        $_SESSION['messageType'] = $messageType;
        $_SESSION['customIcon'] = $customIcon;
        header('Location: user_registration_management.php');
        exit();
    }

    // Prepare the DELETE query using placeholders
    $placeholders = implode(',', array_fill(0, count($delete_ids), '?'));

    // Delete selected users
    $stmt = $pdo->prepare("DELETE FROM users WHERE id IN ($placeholders)");
    $stmt->execute($delete_ids);

    // Set success message and icon
    $message = 'Selected users have been deleted successfully.';
    $messageType = 'success';
    $customIcon = '<img src="' . $iconPaths['success'] . '" alt="Success Icon" class="w-12 h-12 mx-auto mb-4">';
    $_SESSION['message'] = $message;
    $_SESSION['messageType'] = $messageType;
    $_SESSION['customIcon'] = $customIcon;
    header('Location: user_registration_management.php');
    exit();
} else {
    // No items selected or invalid request
    $message = 'No items selected for deletion.';
    $messageType = 'error';
    $customIcon = '<img src="' . $iconPaths['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
    $_SESSION['message'] = $message;
    $_SESSION['messageType'] = $messageType;
    $_SESSION['customIcon'] = $customIcon;
    header('Location: user_registration_management.php');
    exit();
}
?>
