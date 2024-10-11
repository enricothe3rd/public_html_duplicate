<?php
require '../../db/db_connection3.php';
$pdo = Database::connect();

if (isset($_GET['department_id'])) {
    $department_id = $_GET['department_id'];

    try {
        $stmt = $pdo->prepare("SELECT id, course_name FROM courses WHERE department_id = :department_id");
        $stmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
        $stmt->execute();
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($courses);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
