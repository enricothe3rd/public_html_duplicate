<?php
require '../../db/db_connection3.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get the raw POST data
        $data = json_decode(file_get_contents("php://input"), true);

        // Debugging: log the raw data received
        file_put_contents('php://stderr', print_r($data, true));  // Log to error log

        // Extracting the variables from the decoded data
        $student_number = $data['student_number'] ?? null;
        $subject_id = $data['subject_id'] ?? null;  // Add subject_id
        $grade = $data['grade'] ?? null;
        $term = strtolower($data['term'] ?? null);

        // Debugging: Check if all necessary data is provided
        file_put_contents('php://stderr', "Student Number: $student_number, Subject ID: $subject_id, Grade: $grade, Term: $term\n");

        // Validate the input
        if (!$student_number) {
            echo json_encode(["success" => false, "message" => "Student number is required"]);
            exit;
        }

        if (!$subject_id) {
            echo json_encode(["success" => false, "message" => "Subject ID is required"]);
            exit;
        }

        if (!$grade) {
            echo json_encode(["success" => false, "message" => "Grade is required"]);
            exit;
        }

        if (!$term || !in_array($term, ['prelim', 'midterm', 'finals'])) {
            echo json_encode(["success" => false, "message" => "Invalid term. It should be one of 'prelim', 'midterm', or 'finals'"]);
            exit;
        }

        $db = Database::connect();

        // Check if a grade record exists for this student, subject, and term
        $stmt = $db->prepare("SELECT id FROM grades WHERE student_number = ? AND subject_id = ?");
        $stmt->execute([$student_number, $subject_id]);
        $existingGrade = $stmt->fetch();

        // Debugging: Log the result of the existing grade check
        file_put_contents('php://stderr', print_r($existingGrade, true));  // Log existing grade record

        if ($existingGrade) {
            // Update the grade for the specific term and subject
            $updateQuery = "UPDATE grades SET {$term} = ?, updated_at = NOW() WHERE id = ?";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([$grade, $existingGrade['id']]);

            // Debugging: Log successful update
            file_put_contents('php://stderr', "Grade updated for student $student_number, subject $subject_id, term $term\n");
        } else {
            // Insert a new grade record with the subject_id
            $insertQuery = "INSERT INTO grades (student_number, subject_id, {$term}, created_at) VALUES (?, ?, ?, NOW())";
            $insertStmt = $db->prepare($insertQuery);
            $insertStmt->execute([$student_number, $subject_id, $grade]);

            // Debugging: Log successful insert
            file_put_contents('php://stderr', "New grade inserted for student $student_number, subject $subject_id, term $term\n");
        }

        echo json_encode(["success" => true, "message" => ucfirst($term) . " grade updated successfully"]);
    } catch (PDOException $e) {
        // Debugging: Log the error message
        file_put_contents('php://stderr', "Error: " . $e->getMessage() . "\n");
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
}
