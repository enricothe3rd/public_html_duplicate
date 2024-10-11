<?php
session_start();

require '../../db/db_connection3.php';
$pdo = Database::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input data
    $student_number = trim($_POST['student_number']);
    $firstname = trim($_POST['firstname']);
    $middlename = trim($_POST['middlename']);
    $lastname = trim($_POST['lastname']);
    $suffix = trim($_POST['suffix']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $contact_no = trim($_POST['contact_no']);
    $course_id = (int)$_POST['course_id'];
    $section_id = (int)$_POST['section_id'];
    $department_id = (int)$_POST['department_id'];
    $subject_id = (int)$_POST['subject_id'];
    $schedule_id = (int)$_POST['schedule_id'];
    $semester = (int)$_POST['semester'];
    $school_year = trim($_POST['school_year']);
    $sex = trim($_POST['sex']);
    $dob = $_POST['dob'];

    try {
        // Validate required fields
        if (empty($student_number) || empty($firstname) || empty($lastname) || empty($email)) {
            throw new Exception('Required fields cannot be empty.');
        }

        // Begin transaction
        $pdo->beginTransaction();

        // Update the enrollments table
        $stmt = $pdo->prepare("
            UPDATE enrollments
            SET firstname = :firstname,
                middlename = :middlename,
                lastname = :lastname,
                suffix = :suffix,
                email = :email,
                contact_no = :contact_no,
                sex = :sex,
                dob = :dob,
                updated_at = NOW()
            WHERE student_number = :student_number
        ");

        // Bind parameters for enrollments
        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':middlename', $middlename);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':suffix', $suffix);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contact_no', $contact_no);
        $stmt->bindParam(':sex', $sex);
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':student_number', $student_number);

        // Execute the enrollments update
        if (!$stmt->execute()) {
            throw new Exception("Error updating enrollments: " . implode(", ", $stmt->errorInfo()));
        }

        // Update the subject_enrollments table
        $stmt2 = $pdo->prepare("
            UPDATE subject_enrollments
            SET course_id = :course_id,
                section_id = :section_id,
                department_id = :department_id,
                subject_id = :subject_id,
                schedule_id = :schedule_id,
                semester = :semester,
                school_year = :school_year,
                updated_at = NOW()
            WHERE student_number = :student_number
        ");

        // Bind parameters for the subject_enrollments update
        $stmt2->bindParam(':course_id', $course_id);
        $stmt2->bindParam(':section_id', $section_id);
        $stmt2->bindParam(':department_id', $department_id);
        $stmt2->bindParam(':subject_id', $subject_id);
        $stmt2->bindParam(':schedule_id', $schedule_id);
        $stmt2->bindParam(':semester', $semester);
        $stmt2->bindParam(':school_year', $school_year);
        $stmt2->bindParam(':student_number', $student_number);

        // Execute the subject_enrollments update
        if (!$stmt2->execute()) {
            throw new Exception("Error updating subject_enrollments: " . implode(", ", $stmt2->errorInfo()));
        }

        // Commit the transaction
        $pdo->commit();

        // Redirect to success page
        header("Location: display_all_student.php");
        exit;

    } catch (PDOException $e) {
        // Rollback the transaction on SQL error
        $pdo->rollBack();
        echo "Database Error: " . $e->getMessage();
    } catch (Exception $e) {
        // Handle other errors (like validation errors)
        echo "Error: " . $e->getMessage();
    }
}
?>
