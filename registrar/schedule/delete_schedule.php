<?php
require 'Schedule.php';

$schedule = new Schedule();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    if ($schedule->delete($id)) {
        header('Location: read_schedules.php'); // Redirect to a success page
        exit();
    } else {
        echo 'Failed to delete schedule.';
    }
} else {
    echo 'Invalid request.';
}
?>
