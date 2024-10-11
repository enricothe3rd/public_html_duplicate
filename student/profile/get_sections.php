<?php
require '../../db/db_connection3.php';
$pdo = Database::connect();

if (isset($_GET['course_id'])) {
    $course_id = $_GET['course_id'];

    try {
        // Prepare the SQL statement to fetch sections for the selected course
        $stmt = $pdo->prepare("SELECT id, name FROM sections WHERE course_id = :course_id");
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();

        // Fetch sections as an associative array
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return sections as JSON
        echo json_encode($sections);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
