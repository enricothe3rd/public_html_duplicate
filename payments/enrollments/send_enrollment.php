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
    $status = $_POST['status'] ?? ''; // Added validation for status
    $year = $_POST['year'] ?? ''; // Added validation for academic year

    // One-by-one validation for required fields

    // Validate lastname
    if (empty($lastname)) {
        $_SESSION['error_message'] = "Please provide your last name.";
    } elseif (empty($firstname)) {
        // Validate firstname
        $_SESSION['error_message'] = "Please provide your first name.";
    } elseif (empty($dob)) {
        // Validate dob
        $_SESSION['error_message'] = "Please provide your date of birth.";
    } elseif (empty($address)) {
        // Validate address
        $_SESSION['error_message'] = "Please provide your address.";
    } elseif (empty($contact_no)) {
        // Validate contact_no
        $_SESSION['error_message'] = "Please provide your contact number.";
    } elseif (empty($sex)) {
        // Validate sex
        $_SESSION['error_message'] = "Please specify your gender.";
    } elseif (empty($status)) {
        // Validate status
        $_SESSION['error_message'] = "Please select your status.";
    } elseif (empty($year)) {
        // Validate year
        $_SESSION['error_message'] = "Please select your academic year.";
    } elseif (empty($user_email)) {
        // Validate email from session
        $_SESSION['error_message'] = "User email not found. Please log in.";
    } else {
        // Proceed with enrollment creation or update...
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
                    header("Location: create_enrollment.php");
                    exit;
                } else {
                    $_SESSION['error_message'] = "Failed to update enrollment. Please try again.";
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
                    header("Location: create_enrollment.php");
                    exit;
                } else {
                    $_SESSION['error_message'] = "Failed to enroll. Please try again.";
                }
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
    }

    // Store the form data in session if validation fails
    $_SESSION['form_data'] = [
        'lastname' => $lastname,
        'firstname' => $firstname,
        'middlename' => $middlename,
        'dob' => $dob,
        'address' => $address,
        'contact_no' => $contact_no,
        'sex' => $sex,
        'suffix' => $suffix,
        'status' => $status,
        'year' => $year,
    ];
    header("Location: create_enrollment.php");
    exit;
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
    // Default values if no existing enrollment or if there is form data stored in the session
    $lastname = $_SESSION['form_data']['lastname'] ?? '';
    $firstname = $_SESSION['form_data']['firstname'] ?? '';
    $middlename = $_SESSION['form_data']['middlename'] ?? '';
    $dob = $_SESSION['form_data']['dob'] ?? '';
    $address = $_SESSION['form_data']['address'] ?? '';
    $contact_no = $_SESSION['form_data']['contact_no'] ?? '';
    $sex = $_SESSION['form_data']['sex'] ?? '';
    $suffix = $_SESSION['form_data']['suffix'] ?? '';
    $status = $_SESSION['form_data']['status'] ?? '';
    $year = $_SESSION['form_data']['year'] ?? '';
}

// Clear the form data from the session after populating
unset($_SESSION['form_data']);
?>
