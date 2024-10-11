<?php
// Include the database connection file
include 'db/db_connection1.php'; // Make sure this path is correct

if (isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
    echo select_subjects($course_id);
}

function select_subjects($course_id = null) {
    global $pdo; // Access the global $pdo variable

    // Check if $pdo is a valid PDO object
    if (!($pdo instanceof PDO)) {
        die('Database connection is not valid.');
    }

    // Initialize the container for subjects
    $html = '';

    // Fetch section IDs for the given course
    if ($course_id) {
        $query = "SELECT id FROM sections WHERE course_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(1, $course_id, PDO::PARAM_INT);
        $stmt->execute();
        $sections = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        // Fetch subjects for the obtained section IDs
        if ($sections) {
            $placeholders = implode(',', array_fill(0, count($sections), '?'));
            $query = "SELECT id, title FROM subjects WHERE section_id IN ($placeholders) ORDER BY title ASC";
            $stmt = $pdo->prepare($query);
            $stmt->execute($sections);
            $results = $stmt->fetchAll();

            // Populate the subjects
            foreach ($results as $row) {
                $html .= '<p>' . htmlspecialchars($row['title']) . '</p>';
            }
        }
    }

    // Return the generated HTML
    return $html;
}
?>
