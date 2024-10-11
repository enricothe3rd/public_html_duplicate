<?php
// Start session
session_start();

require '../../db/db_connection3.php';
$pdo = Database::connect();

// Fetching student number from URL
$student_number = isset($_GET['student_number']) ? $_GET['student_number'] : '';

if ($student_number) {
    try {
        // Prepare the SQL statement to fetch all details for a specific student
        $stmt = $pdo->prepare("
            SELECT e.student_number, 
                   e.firstname, 
                   e.middlename, 
                   e.lastname, 
                   e.suffix, 
                   c.course_name, 
                   s.name AS section_name, 
                   d.name AS department_name,
                   e.sex, 
                   e.dob, 
                   e.email, 
                   e.contact_no, 
                   e.created_at
            FROM enrollments e
            LEFT JOIN subject_enrollments se ON e.student_number = se.student_number
            LEFT JOIN courses c ON se.course_id = c.id
            LEFT JOIN sections s ON se.section_id = s.id
            LEFT JOIN departments d ON c.department_id = d.id
            WHERE e.student_number = :student_number
        ");
        $stmt->bindParam(':student_number', $student_number, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch the detailed data
        $enrollmentData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Prepare the SQL statement to fetch subjects for the specific student
        $subjectStmt = $pdo->prepare("
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
                sch.day_of_week AS day_of_week,
                sch.start_time AS start_time,
                sch.end_time AS end_time,
                sch.room AS room,
                se.school_year
            FROM subject_enrollments se
            LEFT JOIN sections s ON se.section_id = s.id
            LEFT JOIN departments d ON se.department_id = d.id
            LEFT JOIN courses c ON se.course_id = c.id
            LEFT JOIN subjects sub ON se.subject_id = sub.id
            LEFT JOIN semesters sem ON sub.semester_id = sem.id
            LEFT JOIN schedules sch ON se.schedule_id = sch.id
            WHERE se.student_number = :student_number
        ");

        $subjectStmt->bindParam(':student_number', $student_number, PDO::PARAM_STR);
        $subjectStmt->execute();

        // Fetch the subjects
        $subjects = $subjectStmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // Handle any SQL errors
        echo "Error: " . $e->getMessage();
        exit;
    }
} else {
    echo "No student number provided.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <title>Enrollment Details</title>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <button 
            onclick="goBack()" 
            class="mb-4 px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition duration-200 flex items-center"
        >
            <i class="fas fa-arrow-left mr-2"></i> 
            Back
        </button>
        <h1 class="text-2xl font-bold mb-4 text-red-700">Enrollment Details for <?= htmlspecialchars($enrollmentData['firstname'] . ' ' . $enrollmentData['lastname']) ?></h1>
        
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div class="border-b pb-2">
                    <strong class="text-red-700">Full Name:</strong> 
                    <span class="text-gray-600"><?= htmlspecialchars($enrollmentData['firstname'] . ' ' . $enrollmentData['middlename'] . ' ' . $enrollmentData['lastname'] . ' ' . $enrollmentData['suffix']) ?></span>
                </div>
                <div class="border-b pb-2">
                    <strong class="text-red-700">Course:</strong> 
                    <span class="text-gray-600"><?= htmlspecialchars($enrollmentData['course_name']) ?></span>
                </div>
                <div class="border-b pb-2">
                    <strong class="text-red-700">Section:</strong> 
                    <span class="text-gray-600"><?= htmlspecialchars($enrollmentData['section_name']) ?></span>
                </div>
                <div class="border-b pb-2">
                    <strong class="text-red-700">Department:</strong> 
                    <span class="text-gray-600"><?= htmlspecialchars($enrollmentData['department_name']) ?></span>
                </div>
                <div class="border-b pb-2">
                    <strong class="text-red-700">Sex:</strong> 
                    <span class="text-gray-600"><?= htmlspecialchars($enrollmentData['sex']) ?></span>
                </div>
                <div class="border-b pb-2">
                    <strong class="text-red-700">Student Number:</strong> 
                    <span class="text-gray-600"><?= htmlspecialchars($enrollmentData['student_number']) ?></span>
                </div>
                <div class="border-b pb-2">
                    <strong class="text-red-700">Date of Birth:</strong> 
                    <span class="text-gray-600"><?= htmlspecialchars($enrollmentData['dob']) ?></span>
                </div>
                <div class="border-b pb-2">
                    <strong class="text-red-700">Email:</strong> 
                    <span class="text-gray-600"><?= htmlspecialchars($enrollmentData['email']) ?></span>
                </div>
                <div class="border-b pb-2">
                    <strong class="text-red-700">Contact No:</strong> 
                    <span class="text-gray-600"><?= htmlspecialchars($enrollmentData['contact_no']) ?></span>
                </div>
                <div>
                    <strong class="text-red-700">Enrollment Date:</strong> 
                    <span class="text-gray-600"><?= htmlspecialchars($enrollmentData['created_at']) ?></span>
                </div>
            </div>
        </div>

        <!-- Subjects Table -->
        <h2 class="text-2xl font-semibold text-red-800 mb-4">Subjects Enrolled</h2>
        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="min-w-full border-collapse shadow-md rounded-lg">
                <thead class="bg-red-800">
                    <tr>
                        <th class="px-4 py-4 border-b text-left text-white">Subject Code</th>
                        <th class="px-4 py-4 border-b text-left text-white">Subject Title</th>
                        <th class="px-4 py-4 border-b text-left text-white">Units</th>
                        <th class="px-4 py-4 border-b text-left text-white">Semester</th>
                        <th class="px-4 py-4 border-b text-left text-white">Day</th>
                        <th class="px-4 py-4 border-b text-left text-white">Time</th>
                        <th class="px-4 py-4 border-b text-left text-white">Room</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($subjects) > 0): ?>
                        <?php foreach ($subjects as $subject): ?>
                        <tr class="border-b bg-red-50 hover:bg-red-200">
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($subject['subject_code']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($subject['subject_title']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($subject['subject_units']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($subject['semester_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($subject['day_of_week']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($subject['start_time'] . ' - ' . $subject['end_time']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($subject['room']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center">No subjects found for this student.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>
