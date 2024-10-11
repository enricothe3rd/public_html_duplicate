<?php
require 'Instructor_subject.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    $instructorSubject = new InstructorSubject();
    $instructorSubject->deleteAssignment($id); // Ensure this method exists
    header('Location: read_instructor_subject.php'); // Adjust the redirect as needed
    exit();
} else {
    echo 'No instructor subject ID provided.';
}
?>
