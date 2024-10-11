<?php
require 'Department.php';

if (isset($_GET['id'])) {
    $department = new Department();

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
    
        // Attempt to delete the department
        $department->delete($id);
    } else {
        // Redirect if no ID is provided
        header('Location: department.php?message=error');
        exit;
    }
}