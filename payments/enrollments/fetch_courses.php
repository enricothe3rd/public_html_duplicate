<?php
require '../../db/db_connection3.php';

if (isset($_POST['department_id'])) {
    $department_id = $_POST['department_id'];

    try {
        $db = Database::connect();

        // Prepare and execute the query to fetch courses based on department
        $stmt = $db->prepare("SELECT id, course_name FROM courses WHERE department_id = :department_id");
        $stmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
        $stmt->execute();

        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the courses as JSON
        echo json_encode($courses);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'No department selected.']);
}
?>
