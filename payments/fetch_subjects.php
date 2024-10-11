<?php

session_start();
require '../db/db_connection3.php'; // Adjust the filename as needed

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section_id = $_POST['section_id'] ?? null;
    $school_year_id = $_POST['school_year_id'] ?? null; // Get school year ID
    $semester_id = $_POST['semester_id'] ?? null; // Get semester ID

    if ($section_id && $school_year_id && $semester_id) {
        try {
            $db = Database::connect();

            // Prepare the query to fetch subjects based on school year and semester
            $stmt = $db->prepare("
                SELECT * FROM subjects 
                WHERE school_year_id = :school_year_id 
                AND semester_id = :semester_id 
                AND section_id = :section_id
            ");
            $stmt->bindParam(':school_year_id', $school_year_id);
            $stmt->bindParam(':semester_id', $semester_id);
            $stmt->bindParam(':section_id', $section_id);
            $stmt->execute();

            // Fetch all subjects
            $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Check if subjects are empty
            if (empty($subjects)) {
                echo json_encode(['message' => 'No subjects found for this section.']);
            } else {
                echo json_encode($subjects);
            }
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error fetching subjects: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Invalid parameters.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}


