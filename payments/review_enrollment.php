<?php
session_start(); // Start the session

// Include your database connection
require '../db/db_connection3.php'; // Adjust the path as necessary

// Create a new PDO instance
$pdo = Database::connect();

$enrollmentData = null; // Initialize variable to hold enrollment data

try {
    // Check if student_number is set in the session
    if (isset($_SESSION['student_number'])) {
        $student_number = $_SESSION['student_number'];

        // Prepare the SQL statement with JOINs to fetch course, section, and department info
        $stmt = $pdo->prepare("
            SELECT e.*, 
                   c.course_name, 
                   s.name AS section_name, 
                   d.name AS department_name
            FROM enrollments e
            LEFT JOIN courses c ON e.course_id = c.id
            LEFT JOIN sections s ON e.section_id = s.id
            LEFT JOIN departments d ON c.department_id = d.id
            WHERE e.student_number = :student_number
        ");
        $stmt->bindParam(':student_number', $student_number, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch the results
        $enrollmentData = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Handle any errors
    $error_message = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="">
    <div class="flex justify-center p-8">
        <div class=" max-w-4xl w-full p-8">
            <h1 class="text-4xl font-bold text-gray-800 text-center mb-6">Enrollment Details</h1>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-500 text-white p-4 rounded-lg mb-6 text-center">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php elseif ($enrollmentData): ?>
                <div class="bg-gray-100 p-6 rounded-lg">
                
 
                    <h2 class="text-2xl font-semibold text-gray-700 mb-4">Student Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div><strong class="font-medium text-gray-600">First Name:</strong> <?= htmlspecialchars($enrollmentData['firstname']) ?></div>
                        <div><strong class="font-medium text-gray-600">Middle Name:</strong> <?= htmlspecialchars($enrollmentData['middlename']) ?></div>
                        <div><strong class="font-medium text-gray-600">Last Name:</strong> <?= htmlspecialchars($enrollmentData['lastname']) ?></div>
                        <div><strong class="font-medium text-gray-600">Suffix:</strong> <?= htmlspecialchars($enrollmentData['suffix']) ?></div>
                        <div><strong class="font-medium text-gray-600">Student Type:</strong> <?= htmlspecialchars($enrollmentData['student_type']) ?></div>
                        <div><strong class="font-medium text-gray-600">Sex:</strong> <?= htmlspecialchars($enrollmentData['sex']) ?></div>
                        <div><strong class="font-medium text-gray-600">Date of Birth:</strong> <?= htmlspecialchars($enrollmentData['dob']) ?></div>
                        <div><strong class="font-medium text-gray-600">Email:</strong> <?= htmlspecialchars($enrollmentData['email']) ?></div>
                        <div><strong class="font-medium text-gray-600">Contact No:</strong> <?= htmlspecialchars($enrollmentData['contact_no']) ?></div>
                        <div><strong class="font-medium text-gray-600">Address:</strong> <?= htmlspecialchars($enrollmentData['address']) ?></div>
                        <div><strong class="font-medium text-gray-600">School Year:</strong> <?= htmlspecialchars($enrollmentData['school_year']) ?></div>
                        <div><strong class="font-medium text-gray-600">Status:</strong> <?= htmlspecialchars($enrollmentData['status']) ?></div>
                        <div><strong class="font-medium text-gray-600">Course:</strong> <?= htmlspecialchars($enrollmentData['course_name']) ?></div>
                        <div><strong class="font-medium text-gray-600">Section:</strong> <?= htmlspecialchars($enrollmentData['section_name']) ?></div>
                        <div><strong class="font-medium text-gray-600">Department:</strong> <?= htmlspecialchars($enrollmentData['department_name']) ?></div>
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

