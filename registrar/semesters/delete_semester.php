<?php
require 'Semester.php';

$semester = new Semester();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Check if the semester exists before attempting to delete
    if (!$semester->getSemesterById($id)) {
        header('Location: read_semesters.php?message=not_found'); // Redirect with error message
        exit();
    }

    // Proceed to delete the semester
    if ($semester->deleteSemester($id)) {
        header('Location: read_semesters.php?message=deleted_successfully'); // Redirect with success message
    } else {
        header('Location: read_semesters.php?message=delete_failed'); // Redirect with error message
    }
    exit();
} else {
    header('Location: read_semesters.php'); // Redirect if no ID is provided
    exit();
}
?>
