<?php
require 'Section.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    $section = new Section();
    $section->delete($id);
    header('Location: read_sections.php');
    exit();
} else {
    echo 'No section ID provided.';
}
?>
