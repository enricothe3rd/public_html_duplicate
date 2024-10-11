<?php
require 'Instructor.php';
$pdo = Database::connect();
$instructor = new Instructor($pdo);

if (isset($_GET['course_id'])) {
    $courseId = intval($_GET['course_id']);
    $sections = $instructor->getSectionsByCourse($courseId);
    echo json_encode($sections);
}
?>
