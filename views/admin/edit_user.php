<?php
// Include the database connection file
require '../../db/db_connection1.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $account_locked = $_POST['account_locked'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Initialize failed_attempts to null
    $failed_attempts = null;

    // If account is unlocked, reset failed_attempts
    if ($account_locked == 0) {
        $failed_attempts = 0; // Clear failed attempts
    }

    // Prepare the base query
    $query = "UPDATE users SET email = :email, role = :role, status = :status, account_locked = :account_locked";

    // Append failed_attempts to the query if it's set
    if ($failed_attempts !== null) {
        $query .= ", failed_attempts = :failed_attempts";
    }

    // If password is changed, update it as well
    if (!empty($new_password) && $new_password === $confirm_password) {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $query .= ", password = :password";
    }

    // Complete the query
    $query .= " WHERE id = :id";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':account_locked', $account_locked);
    $stmt->bindParam(':id', $id);

    // Bind failed_attempts if it is being reset
    if ($failed_attempts !== null) {
        $stmt->bindParam(':failed_attempts', $failed_attempts);
    }

    // Bind password if changed
    if (!empty($new_password) && $new_password === $confirm_password) {
        $stmt->bindParam(':password', $hashed_password);
    }

    $stmt->execute();

    // Redirect back to the user management page
    header('Location: users_management.php');
    exit();
}

?>
