<?php
require '../../db/db_connection3.php';
$db = Database::connect();

if (isset($_GET['student_number']) && isset($_GET['subject_id'])) {
    $student_number = $_GET['student_number'];
    $subject_id = $_GET['subject_id'];

    $stmt = $db->prepare("
        SELECT g.prelim, g.midterm, g.finals 
        FROM grades g 
        JOIN enrollments e ON g.student_id = e.id 
        WHERE e.student_number = ? AND g.subject_id = ?
    ");
    $stmt->execute([$student_number, $subject_id]);
    $grades = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($grades);
} else {
    echo json_encode(null);
}
