<?php
require '../../db/db_connection1.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $email_confirmed = $_POST['email_confirmed'];
    $failed_attempts = $_POST['failed_attempts'];
    $account_locked = $_POST['account_locked'];
    $lock_time = $_POST['lock_time'];

    $stmt = $pdo->prepare("
        UPDATE users SET 
            email = ?, 
            password = ?, 
            role = ?, 
            status = ?, 
            email_confirmed = ?, 
            failed_attempts = ?, 
            account_locked = ?, 
            lock_time = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $email,
        $password,
        $role,
        $status,
        $email_confirmed,
        $failed_attempts,
        $account_locked,
        $lock_time,
        $id
    ]);

    header('Location: user_management.php');
    exit;
}
?>
