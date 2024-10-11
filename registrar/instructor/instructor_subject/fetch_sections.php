<?php
require 'Instructor_subject.php';

$course_id = $_GET['course_id'];
$instructor = new InstructorSubject();
$sections = $instructor->getSectionsByCourse($course_id);

echo json_encode($sections);


?>