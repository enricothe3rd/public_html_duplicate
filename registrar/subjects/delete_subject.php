<?php
require 'Subject.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    $subject = new Subject();

    // Attempt to delete the subject
    if ($subject->delete($id)) {
        header('Location: read_subjects.php?message=Subject deleted successfully.'); // Redirect on successful deletion
        exit();
    } else {
        // If deletion fails, you can set a specific message if needed
        header('Location: read_subjects.php?id=' . $id . '&message=Unable to delete the subject.'); // Redirect with an error message
        exit();
    }
} else {
    echo 'Error: No subject ID provided.'; // Message if no ID is given
}
?>
