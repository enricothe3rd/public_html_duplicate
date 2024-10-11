<?php

require 'Course.php';

$course = new Course();
$id = $_GET['id'];

if ($course->delete($id)) { // Update to call the correct method
    header('Location: read_courses.php?id=' . $id . '&message=deleted');
    exit;
} else {
    echo "Error deleting course";
}
?>
