<?php
require '../../db/db_connection3.php';
$pdo = Database::connect();

if (isset($_GET['subject_id'])) {
    $subject_id = $_GET['subject_id'];

    try {
        // Prepare SQL query to fetch schedules by subject ID
        $stmt = $pdo->prepare("SELECT id, day_of_week, start_time, end_time, room FROM schedules WHERE subject_id = :subject_id");
        $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch all schedules associated with the subject
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($schedules);
    } catch (PDOException $e) {
        // Return an empty array in case of an error
        echo json_encode([]);
    }
} else {
    // Return an empty array if subject_id is not set
    echo json_encode([]);
}
