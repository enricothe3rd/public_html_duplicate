<?php
require '../db/db_connection3.php';
require '../subjects/subject.php'; // Adjust the path as needed

// Get course_id and semester_id from query parameters
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$semesterId = isset($_GET['semester_id']) ? (int)$_GET['semester_id'] : 0;

if ($semesterId) {
    $subject = new Subject();
    
    // Get subjects by semester
    $subjects = $subject->getSubjectsBySemester($semesterId);
    
    // Filter subjects based on selected course (if necessary)
    if ($courseId) {
        $subjects = array_filter($subjects, function($subject) use ($courseId) {
            return $subject['course_id'] == $courseId;
        });
    }
    
    echo json_encode($subjects);
} else {
    echo json_encode([]);
}
?>
