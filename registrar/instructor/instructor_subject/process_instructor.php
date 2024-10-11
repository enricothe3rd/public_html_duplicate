<?php
require 'Instructor_subject.php'; // Adjust the path if necessary

$instructor = new InstructorSubject();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instructor_id = $_POST['instructor'];
    $subject_id = $_POST['subject'];
    $semester_id = $_POST['semester'];

    $instructor->addSubjectToInstructor($instructor_id, $subject_id, $semester_id);

    // Redirect or provide feedback
    header('Location: read_instructor_subject.php');
}
?>
