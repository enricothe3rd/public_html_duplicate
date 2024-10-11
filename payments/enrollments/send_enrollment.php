<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the Enrollment class
require 'Enrollment.php';

// Start session
session_start();
$user_email = $_SESSION['user_email'] ?? ''; // Assuming the user's email is stored in the session

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $lastname = $_POST['lastname'] ?? '';
    $firstname = $_POST['firstname'] ?? '';
    $middlename = $_POST['middlename'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $address = $_POST['address'] ?? '';
    $contact_no = $_POST['contact_no'] ?? '';
    $sex = $_POST['sex'] ?? '';
    $suffix = $_POST['suffix'] ?? '';
    $status = $_POST['status'] ?? ''; // Assuming status is something you still need
    $suffix = $_POST['suffix'] ?? '';
    $year = $_POST['year'] ?? ''; // Assuming status is something you still need

    // One-by-one validation for required fields

    // Validate lastname
    if (empty($lastname)) {
        $_SESSION['error_message'] = "Please provide your last name.";
        header("Location: create_enrollment.php");
        exit;
    }

    // Validate firstname
    if (empty($firstname)) {
        $_SESSION['error_message'] = "Please provide your first name.";
        header("Location: create_enrollment.php");
        exit;
    }

    // Validate dob
    if (empty($dob)) {
        $_SESSION['error_message'] = "Please provide your date of birth.";
        header("Location: create_enrollment.php");
        exit;
    }

    // Validate address
    if (empty($address)) {
        $_SESSION['error_message'] = "Please provide your address.";
        header("Location: create_enrollment.php");
        exit;
    }

    // Validate contact_no
    if (empty($contact_no)) {
        $_SESSION['error_message'] = "Please provide your contact number.";
        header("Location: create_enrollment.php");
        exit;
    }

    // Validate sex
    if (empty($sex)) {
        $_SESSION['error_message'] = "Please specify your gender.";
        header("Location: create_enrollment.php");
        exit;
    }

    // Validate email from session
    if (empty($user_email)) {
        $_SESSION['error_message'] = "User email not found. Please log in.";
        header("Location: create_enrollment.php");
        exit;
    }

    // If validation passes, insert or update the enrollment data
    try {
        $enrollment = new Enrollment(); // Assuming this class is in Enrollment.php

        // Check if the user already has an enrollment record
        $existingEnrollment = $enrollment->getEnrollmentByEmail($user_email);

        if ($existingEnrollment) {
            // Update the existing enrollment record
            $result = $enrollment->updateEnrollment([
                'lastname' => $lastname,
                'firstname' => $firstname,
                'middlename' => $middlename,
                'email' => $user_email, // Use the session email
                'dob' => $dob,
                'address' => $address,
                'contact_no' => $contact_no,
                'sex' => $sex,
                'suffix' => $suffix,
                'status' => $status,
                'year' => $year,
            ], $existingEnrollment['id']); // Assuming the ID is used for the update

            if ($result) {
                $_SESSION['success_message'] = "Enrollment updated successfully!";
                header("Location: ../select_courses.php");
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to update enrollment. Please try again.";
                header("Location: create_enrollment.php");
                exit;
            }
        } else {
            // If no existing record, create a new one
            $student_number = $enrollment->generateStudentNumber();

            // Create the enrollment record
            $result = $enrollment->createEnrollment([
                'student_number' => $student_number, // Include the generated student number
                'lastname' => $lastname,
                'firstname' => $firstname,
                'middlename' => $middlename,
                'email' => $user_email, // Use the session email
                'dob' => $dob,
                'address' => $address,
                'contact_no' => $contact_no,
                'sex' => $sex,
                'suffix' => $suffix,
                'status' => $status,
                'year' => $year
            ]);

            if ($result) {
                $_SESSION['success_message'] = "Enrollment created successfully!";
                header("Location: ../select_courses.php");
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to enroll. Please try again.";
                header("Location: create_enrollment.php");
                exit;
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header("Location: create_enrollment.php");
        exit;
    }
}

// Fetch existing enrollment data to populate the form if it exists
$existingEnrollment = $enrollment->getEnrollmentByEmail($user_email);
if ($existingEnrollment) {
    $lastname = $existingEnrollment['lastname'];
    $firstname = $existingEnrollment['firstname'];
    $middlename = $existingEnrollment['middlename'];
    $dob = $existingEnrollment['dob'];
    $address = $existingEnrollment['address'];
    $contact_no = $existingEnrollment['contact_no'];
    $sex = $existingEnrollment['sex'];
    $suffix = $existingEnrollment['suffix'];
    $status = $existingEnrollment['status'];
    $year = $existingEnrollment['year'];
} else {
    // Default values if no existing enrollment
    $lastname = '';
    $firstname = '';
    $middlename = '';
    $dob = '';
    $address = '';
    $contact_no = '';
    $sex = '';
    $suffix = '';
    $status = '';
    $year = '';
}
?>
