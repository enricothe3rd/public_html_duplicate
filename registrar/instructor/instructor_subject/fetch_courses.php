<?php 

require 'Instructor_subject.php';

$department_id = $_GET['department_id'];
$instructor = new InstructorSubject();
$courses = $instructor->getCoursesByDepartment($department_id);

echo json_encode($courses);


?>