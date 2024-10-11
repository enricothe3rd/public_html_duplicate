<?php
require 'Instructor_subject.php'; // Adjust the path if necessary

$instructor = new InstructorSubject();

$section_id = isset($_GET['section_id']) ? (int)$_GET['section_id'] : 0;
$semester_id = isset($_GET['semester_id']) ? (int)$_GET['semester_id'] : 0;

$subjects = $instructor->getSubjectsBySectionAndSemester($section_id, $semester_id);

echo json_encode($subjects);
?>

