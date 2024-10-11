
<?php
require '../db/db_connection3.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = $_POST['course_id'];
    $semesterId = $_POST['semester_id'];

    // Fetch subjects and schedules based on selected course and semester
    $query = "
        SELECT s.id AS subject_id, s.code, s.title, s.units, sc.day_of_week, sc.start_time, sc.end_time, sc.room
        FROM subjects s
        JOIN schedules sc ON s.id = sc.subject_id
        WHERE s.course_id = :course_id
          AND s.semester_id = :semester_id
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['course_id' => $courseId, 'semester_id' => $semesterId]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Display subjects and schedules
    echo '<h2>Subjects and Schedules</h2>';
    echo '<table class="min-w-full divide-y divide-gray-200">';
    echo '<thead><tr><th>Code</th><th>Title</th><th>Units</th><th>Room</th><th>Day</th><th>Start Time</th><th>End Time</th></tr></thead>';
    echo '<tbody>';
    foreach ($subjects as $subject) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($subject['code']) . '</td>';
        echo '<td>' . htmlspecialchars($subject['title']) . '</td>';
        echo '<td>' . htmlspecialchars($subject['units']) . '</td>';
        echo '<td>' . htmlspecialchars($subject['room']) . '</td>';
        echo '<td>' . htmlspecialchars($subject['day_of_week']) . '</td>';
        echo '<td>' . htmlspecialchars($subject['start_time']) . '</td>';
        echo '<td>' . htmlspecialchars($subject['end_time']) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}
?>
