<?php
require '../../db/db_connection3.php';
$pdo = Database::connect();

if (isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];

    try {
        $stmt = $pdo->prepare("SELECT id, name FROM sections WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->execute();
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($sections);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
