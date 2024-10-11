<?php
session_start();
require 'db_connection.php'; // Adjust this according to your database connection script

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $email = $_GET['email'] ?? '';

    // Retrieve user details
    $sql = "SELECT id, firstname, middlename, lastname FROM users WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }

    $user_id = $user['id'];

    // Retrieve enrollment status
    $sql = "SELECT id, status FROM enrollment WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$enrollment) {
        die("Enrollment not found.");
    }

    // Display enrollment status
    echo "<h2>Enrollment Status</h2>";
    echo "<p>Enrollment ID: " . $enrollment['id'] . "</p>";
    echo "<p>Name: " . $user['firstname'] . " " . $user['middlename'] . " " . $user['lastname'] . "</p>";
    echo "<p>Status: " . $enrollment['status'] . "</p>";
}
?>
