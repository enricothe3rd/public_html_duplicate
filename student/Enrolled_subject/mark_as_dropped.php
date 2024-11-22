<?php
// Include your database connection file
require '../../db/db_connection3.php';

header('Content-Type: application/json');

try {
    // Get the JSON input
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    // Validate the input
    if (!isset($data['student_number'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Student number is required.'
        ]);
        exit;
    }

    // Assign input data to variable
    $studentNumber = $data['student_number'];

    // Connect to the database
    $db = Database::connect();

    // Fetch student details
    $fetchQuery = "
        SELECT student_number, firstname, lastname
        FROM enrollments 
        WHERE student_number = :student_number
    ";
    $fetchStmt = $db->prepare($fetchQuery);
    $fetchStmt->execute([':student_number' => $studentNumber]);
    $studentData = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    if (!$studentData) {
        echo json_encode([
            'success' => false,
            'message' => 'Student not found.'
        ]);
        exit;
    }

    // Archive the student details (no need to pass created_at as we're using NOW() for the archive entry)
    $archiveQuery = "
        INSERT INTO archived_students (student_number, firstname, lastname, created_at) 
        VALUES (:student_number, :firstname, :lastname, NOW())
    ";
    $archiveStmt = $db->prepare($archiveQuery);
    $archiveStmt->execute([
        ':student_number' => $studentData['student_number'],
        ':firstname' => $studentData['firstname'],  // Ensure correct column names
        ':lastname' => $studentData['lastname']     // Ensure correct column names
    ]);

    if (!$archiveStmt->rowCount()) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to archive student details.'
        ]);
        exit;
    }

    // Mark the student as dropped in the enrollments table
    $updateQuery = "UPDATE enrollments SET status = 'dropped' WHERE student_number = :student_number";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->execute([':student_number' => $studentNumber]);

    if ($updateStmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Student marked as dropped and archived successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to mark student as dropped. Please try again.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?>
