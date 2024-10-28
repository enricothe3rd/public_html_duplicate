<?php
session_start();
require '../../db/db_connection3.php';
require '../../message.php';

$pdo = Database::connect();
require '../../session_student.php';

// Check if the session variables are set
if (isset($_SESSION['student_number']) && isset($_SESSION['user_email'])) {
    $student_number = $_SESSION['student_number'];
    $email = $_SESSION['user_email'];

    // Check if the student has made payments with the desired status and method
    $checkPaymentStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM payments 
        WHERE student_number = :student_number
        AND payment_status = 'completed'
        AND (payment_method = 'cash' OR payment_method = 'installment')
    ");
    
    // Bind the student number
    $checkPaymentStmt->bindParam(':student_number', $student_number, PDO::PARAM_STR);
    
    // Execute the payment check
    $checkPaymentStmt->execute();
    $paymentValid = $checkPaymentStmt->fetchColumn();

    // Initialize subjects array
    $subjects = [];

    // Only fetch subjects if the payment is valid
    if ($paymentValid > 0) {
        // Prepare the SQL statement to fetch subject enrollments
        $SubjectStmt = $pdo->prepare("
        SELECT 
            se.id,
            se.student_number,
            se.school_year,  -- Adding school_year
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
            sch.room AS room
        FROM subject_enrollments se
        LEFT JOIN sections s ON se.section_id = s.id
        LEFT JOIN departments d ON se.department_id = d.id
        LEFT JOIN courses c ON se.course_id = c.id
        LEFT JOIN subjects sub ON se.subject_id = sub.id
        LEFT JOIN semesters sem ON sub.semester_id = sem.id
        LEFT JOIN schedules sch ON se.schedule_id = sch.id
        WHERE se.student_number = :student_number
        ORDER BY se.school_year, sem.semester_name -- Grouping by school year and semester
    ");
    
    $SubjectStmt->bindParam(':student_number', $student_number, PDO::PARAM_STR);
    $SubjectStmt->execute();
    $subjects = $SubjectStmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // If no valid payment, inform the user
        displayMessage('error', 'Payment Required', 'No valid payment found for this student. Please complete your payment using cash to view subjects.');
        exit; // Exit to prevent further processing
    }
} else {
    // Handle case where session variables are not set
    displayMessage('warning', 'Session Error', 'No valid session found. Please log in.');
    exit; // Exit if session is invalid
}

// Extract course details if subjects are available
$semester_name = !empty($subjects) ? $subjects[0]['semester_name'] : null;
$section_name = !empty($subjects) ? $subjects[0]['section_name'] : null;
$course_name = !empty($subjects) ? $subjects[0]['course_name'] : null;
$department_name = !empty($subjects) ? $subjects[0]['department_name'] : null;

// Function to convert time to 12-hour format with AM/PM
function formatTime($time) {
    return date("g:i A", strtotime($time));
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Enrollments</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <h1 class="text-2xl font-bold mb-4 text-center uppercase text-red-800">Enrolled Subjects</h1>

    <?php if (!empty($subjects)): ?>
    <?php 
        $currentSchoolYear = null; 
        $lastDisplayedSemester = null;
        
    ?>

    <!-- Responsive layout for small devices -->
    <div class="block sm:hidden">
        <?php foreach ($subjects as $subject): ?>
            <?php 
                // Check if the school year has changed
                if ($currentSchoolYear !== $subject['school_year']): 
                    $currentSchoolYear = $subject['school_year'];
                    $lastDisplayedSemester = null; // Reset the semester when the school year changes
            ?>
                <div class="bg-red-100 p-2 rounded-lg mb-2">
                    <span class="font-bold text-lg text-red-800">School Year: <?php echo htmlspecialchars($currentSchoolYear); ?></span>
                </div>
            <?php endif; ?>

            <?php 
                // Check if the semester has already been displayed for the current school year
                if ($lastDisplayedSemester !== $subject['semester_name']): 
                    $lastDisplayedSemester = $subject['semester_name'];
            ?>
                <div class="bg-red-100 p-2 rounded-lg mb-2">
                    <span class="font-bold text-lg text-red-800"> <?php echo htmlspecialchars($lastDisplayedSemester); ?></span>
                </div>
            <?php endif; ?>

            <!-- Subject Details -->
            <div class="bg-white shadow-md rounded-lg mb-4 p-4">
                <div class="flex flex-col space-y-2">
                    <div class="flex justify-between">
                        <span class="font-bold text-red-800">Section Name:</span>
                        <span><?php echo htmlspecialchars($subject['section_name']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-bold text-red-800">Subject Code:</span>
                        <span><?php echo htmlspecialchars($subject['subject_code']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-bold text-red-800">Subject Title:</span>
                        <span><?php echo htmlspecialchars($subject['subject_title']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-bold text-red-800">Units:</span>
                        <span><?php echo htmlspecialchars($subject['subject_units']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-bold text-red-800">Schedule:</span>
                        <span>
                            <?php echo htmlspecialchars($subject['day_of_week']); ?>, 
                            <?php echo formatTime($subject['start_time']); ?> - 
                            <?php echo formatTime($subject['end_time']); ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-bold text-red-800">Room:</span>
                        <span><?php echo htmlspecialchars($subject['room']); ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>




    <!-- Table layout for larger devices -->
    <div class="overflow-x-auto hidden sm:block">
        <table class="min-w-full bg-white shadow-md rounded-lg sm:table">
            <thead class="bg-gray-200">
                <tr class="bg-red-800">
                    <th class="px-4 py-4 border-b text-left text-white">Section Name</th>
                    <th class="px-4 py-4 border-b text-left text-white">Subject Code</th>
                    <th class="px-4 py-4 border-b text-left text-white">Subject Title</th>
                    <th class="px-4 py-4 border-b text-left text-white">Units</th>
                    <th class="px-4 py-4 border-b text-left text-white">Schedule (Day, Time)</th>
                    <th class="px-4 py-4 border-b text-left text-white">Room</th>
                </tr>
            </thead>
            <tbody>
                
    <?php if (!empty($subjects)): ?>
    <?php 
        $currentSchoolYear = null; 
        $lastDisplayedSemester = null;
        
    ?>
                <?php foreach ($subjects as $subject): ?>
                    <?php 
                        // Display school year if it's a new group
                        if ($currentSchoolYear !== $subject['school_year']): 
                            $currentSchoolYear = $subject['school_year'];
                            $lastDisplayedSemester = null; // Reset the semester when the school year changes
                    ?>
                        <tr class="bg-gray-100">
                            <td colspan="6" class="font-bold text-xl text-red-800 pt-4">
                                School Year: <?php echo htmlspecialchars($currentSchoolYear); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php
                    // Display semester if it's a new semester within the current school year
                    if ($lastDisplayedSemester !== $subject['semester_name']): 
                        $lastDisplayedSemester = $subject['semester_name'];
                    ?>
                        <tr class="bg-gray-100">
                            <td colspan="6" class="font-bold text-lg text-red-800 pt-2">
                                 <?php echo htmlspecialchars($lastDisplayedSemester); ?>
                            </td>
                        </tr>
                    <?php endif; ?>



                    <tr class="border-b bg-red-50 hover:bg-red-200">
                        <td class="border-t px-4 py-3 text-sm md:text-base"><?php echo htmlspecialchars($subject['section_name']); ?></td>
                        <td class="border-t px-4 py-3 text-sm md:text-base"><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                        <td class="border-t px-4 py-3 text-sm md:text-base"><?php echo htmlspecialchars($subject['subject_title']); ?></td>
                        <td class="border-t px-4 py-3 text-sm md:text-base"><?php echo htmlspecialchars($subject['subject_units']); ?></td>
                        <td class="border-t px-4 py-3 text-sm md:text-base">
                            <?php echo htmlspecialchars($subject['day_of_week']); ?>, 
                            <?php echo formatTime($subject['start_time']); ?> - 
                            <?php echo formatTime($subject['end_time']); ?>
                        </td>
                        <td class="border-t px-4 py-3 text-sm md:text-base"><?php echo htmlspecialchars($subject['room']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php else: ?>
        <p class="text-center text-red-500">No subjects found for this student.</p>
    <?php endif; ?>
    <?php endif; ?>

</body>
</html>
