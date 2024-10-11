<?php
session_start();
require '../db/db_connection3.php'; // Adjust the filename as needed

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectId = $_POST['subject_id'] ?? null;

    // Check if the subject ID is provided
    if (!$subjectId) {
        echo json_encode(['error' => 'Subject ID is required.']);
        exit;
    }

    try {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM schedules WHERE subject_id = :subject_id");
        $stmt->bindParam(':subject_id', $subjectId, PDO::PARAM_INT);
        $stmt->execute();
        
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Return schedules if found
        if ($schedules) {
            echo json_encode($schedules);
        } else {
            echo json_encode(['error' => 'No schedules found for the selected subject.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error fetching schedules: ' . $e->getMessage()]);
    }
}
?>
