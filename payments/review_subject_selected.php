<?php
// Start the session
session_start();

// Include your database connection
require '../db/db_connection3.php';

try {
    // Create a new PDO connection
    $db = Database::connect(); // Assuming Database::connect() returns a PDO instance

    // Adjust column names to match your actual database structure
    $stmt = $db->prepare("
        SELECT 
            se.id,
            se.student_number,
            s.name AS section_name,
            d.name AS department_name,
            c.course_name AS course_name,
            sub.code AS subject_code,
            sub.title AS subject_title,
            sub.units AS subject_units,
            sem.semester_name AS semester_name,
            sch.day_of_week AS schedule_day,
            sch.start_time AS schedule_start_time,
            sch.end_time AS schedule_end_time,
            sch.room AS schedule_room
        FROM subject_enrollments se
        LEFT JOIN sections s ON se.section_id = s.id
        LEFT JOIN departments d ON se.department_id = d.id
        LEFT JOIN courses c ON se.course_id = c.id
        LEFT JOIN subjects sub ON se.subject_id = sub.id
        LEFT JOIN semesters sem ON sub.semester_id = sem.id
        LEFT JOIN schedules sch ON se.schedule_id = sch.id
        WHERE se.student_number = :student_number
    ");

    // Bind the session student number to the SQL statement
    $stmt->bindParam(':student_number', $_SESSION['student_number'], PDO::PARAM_STR);

    // Execute the statement
    $stmt->execute();

    // Fetch the result (assuming only one enrollment record)
    $enrollmentData = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Selection Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="">
    <div class="flex justify-center">
        <div class="max-w-4xl w-full p-8">
            <h1 class="text-4xl font-bold text-gray-800 text-center mb-6">Subject Selection Details</h1>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-500 text-white p-4 rounded-lg mb-6 text-center">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php elseif ($enrollmentData): ?>
                <div class="bg-gray-100 p-6 rounded-lg">
                    <h2 class="text-2xl font-semibold text-gray-700 mb-4">Subject Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><strong class="font-medium text-gray-600">Student Number:</strong> <?= htmlspecialchars($enrollmentData['student_number']) ?></div>
                        <div><strong class="font-medium text-gray-600">Section:</strong> <?= htmlspecialchars($enrollmentData['section_name']) ?></div>
                        <div><strong class="font-medium text-gray-600">Department:</strong> <?= htmlspecialchars($enrollmentData['department_name']) ?></div>
                        <div><strong class="font-medium text-gray-600">Course:</strong> <?= htmlspecialchars($enrollmentData['course_name']) ?></div>
                        <div><strong class="font-medium text-gray-600">Subject Code:</strong> <?= htmlspecialchars($enrollmentData['subject_code']) ?></div>
                        <div><strong class="font-medium text-gray-600">Subject Title:</strong> <?= htmlspecialchars($enrollmentData['subject_title']) ?></div>
                        <div><strong class="font-medium text-gray-600">Units:</strong> <?= htmlspecialchars($enrollmentData['subject_units']) ?></div>
                        <div><strong class="font-medium text-gray-600">Semester:</strong> <?= htmlspecialchars($enrollmentData['semester_name']) ?></div>
                        <div><strong class="font-medium text-gray-600">Schedule Day:</strong> <?= htmlspecialchars($enrollmentData['schedule_day']) ?></div>
                        <div><strong class="font-medium text-gray-600">Start Time:</strong> <?= htmlspecialchars($enrollmentData['schedule_start_time']) ?></div>
                        <div><strong class="font-medium text-gray-600">End Time:</strong> <?= htmlspecialchars($enrollmentData['schedule_end_time']) ?></div>
                        <div><strong class="font-medium text-gray-600">Room:</strong> <?= htmlspecialchars($enrollmentData['schedule_room']) ?></div>


      
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-yellow-500 text-white p-4 rounded-lg mb-6 text-center">
                    No enrollment found for Student Number: <?= htmlspecialchars($_SESSION['student_number']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
