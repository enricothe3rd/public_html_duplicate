<?php
require 'Instructor.php';
$pdo = Database::connect();
$instructor = new Instructor($pdo);

if (isset($_GET['department_id'])) {
    $departmentId = intval($_GET['department_id']);
    $courses = $instructor->getCoursesByDepartment($departmentId);
    echo json_encode($courses);
}
?>
