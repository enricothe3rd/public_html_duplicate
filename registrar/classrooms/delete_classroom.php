<?php
require 'Classroom.php';
$classroom = new Classroom();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    if ($classroom->deleteClassroom($id)) {
        header('Location: read_classrooms.php'); // Redirect to the list of classrooms
        exit();
    } else {
        echo 'Failed to delete classroom.';
    }
} else {
    echo 'No ID specified!';
}
?>
