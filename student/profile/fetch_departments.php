<?php
require '../../db/db_connection3.php';
$pdo = Database::connect();

try {
    $stmt = $pdo->query("SELECT id, name FROM departments");
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($departments);
} catch (PDOException $e) {
    echo json_encode([]);
}
