<?php
$dsn = 'mysql:host=localhost;dbname=u918134096_token_db2';
// $username = 'u918134096_localhost2';
// $password = 'Enrico@12';

$username = 'root';
$password = '';
try {
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}
?>

