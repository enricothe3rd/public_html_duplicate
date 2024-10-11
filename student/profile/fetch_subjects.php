<?php
require '../../db/db_connection3.php';
$pdo = Database::connect();

if (isset($_GET['section_id'])) {
    $section_id = $_GET['section_id'];

    try {
        // Prepare SQL query to fetch subjects by section ID
        $stmt = $pdo->prepare("SELECT id, title FROM subjects WHERE section_id = :section_id");
        $stmt->bindParam(':section_id', $section_id, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch all subjects associated with the section
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($subjects);
    } catch (PDOException $e) {
        // Return an empty array in case of an error
        echo json_encode([]);
    }
} else {
    // Return an empty array if section_id is not set
    echo json_encode([]);
}
