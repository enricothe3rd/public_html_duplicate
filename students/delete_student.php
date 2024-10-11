<?php
require 'Student.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $student = new Student();

    if ($student->deleteStudent($id)) {
        header('Location: read_students.php'); // Redirect to the students list page
        exit();
    } else {
        echo 'Failed to delete student.';
    }
} else {
    echo 'No student ID provided.';
}
?>
