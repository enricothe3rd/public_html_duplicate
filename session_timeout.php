<?php
// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Time in seconds after which the session will expire (e.g., 30 minutes)
    $session_timeout = 1800; // 30 minutes

    // Check last activity time
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
        // If last activity was more than session_timeout seconds ago, logout the user
        session_unset();     // Unset all session variables
        session_destroy();   // Destroy the session
        header("Location: login.php"); // Redirect to login page
        exit();
    }

    // Update last activity time upon user activity
    $_SESSION['last_activity'] = time();
}
?>
