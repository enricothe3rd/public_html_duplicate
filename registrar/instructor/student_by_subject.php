<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../db/db_connection3.php';

// Connect to the database using the Database class
$db = Database::connect();

session_start(); // Start session to access instructor data

// Get the instructor's email from the session
$email = $_SESSION['user_email'] ?? null; // Use null if not set

// Initialize instructor_id
$instructor_id = null;

// Ensure the email is valid
if ($email) {
    // Fetch the instructor's ID based on email
    $stmt = $db->prepare("SELECT id FROM instructors WHERE email = ?");
    $stmt->execute([$email]);
    $instructor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($instructor) {
        $instructor_id = $instructor['id']; // Set the instructor_id if found
    } else {
        die("Instructor not found."); // Handle case where instructor does not exist
    }
} else {
    die("No instructor email found in session."); // Handle case where email is not in session
}

// Fetch subjects assigned to this instructor
$assigned_subjects = [];
if ($instructor_id) {
    $stmt = $db->prepare("
    SELECT 
        s.id, 
        s.title, 
        sec.name AS section_name,
        c.course_name  -- Add the course name here
    FROM instructor_subjects isub 
    JOIN subjects s ON isub.subject_id = s.id
    JOIN sections sec ON s.section_id = sec.id 
    JOIN courses c ON sec.course_id = c.id  -- Join with courses to get the course name
    WHERE isub.instructor_id = ?
");

    $stmt->execute([$instructor_id]);
    $assigned_subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch students based on the selected subject
$students = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['subject_id'])) {
    $subject_id = $_POST['subject_id'];
    
    $stmt = $db->prepare("
    SELECT 
        e.student_number, 
        e.firstname, 
        e.lastname, 
        e.suffix,                  
        sub.title AS subject_title,  
        c.course_name,              
        sec.name AS section_name     
    FROM subject_enrollments se 
    JOIN enrollments e ON se.student_number = e.student_number 
    JOIN subjects sub ON se.subject_id = sub.id  
    JOIN sections sec ON sub.section_id = sec.id       
    JOIN courses c ON sec.course_id = c.id  -- Join with courses table using course_id from sections
    WHERE se.subject_id = ?
");




    $stmt->execute([$subject_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check if no students were enrolled in the selected subject
        if (empty($students)) {
            $_SESSION['error_message'] = "No students enrolled in that subject."; // Set error message
        } else {
            $_SESSION['success_message'] = "Students are enrolled in that subject."; // Set success message
        }
}

// Include the message handler to display messages
include '../message/message_handler.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <title>View Students by Subject</title>
</head>
<body class=" font-sans leading-normal tracking-normal">
    <div class="">
        <h1 class="text-2xl font-semibold text-red-800 mb-4">View Students by Subject</h1>

        <!-- Form for Selecting Subject -->
        <form method="POST" action="" class="mb-6">
            <h2 class="text-xl font-semibold mb-4 text-red-800">Select Subject to View Students</h2>
            <div class="mb-4">
                <label for="subject_id" class="block px-3 text-red-700 font-medium">Subject:</label>
                <select name="subject_id" class="border rounded p-2 w-full border-red-300 outline-none" required>
                    <option value="">-- Select a Subject --</option>
                    <?php foreach ($assigned_subjects as $subject): ?>
                        <option value="<?= $subject['id']; ?>"><?= htmlspecialchars(strtoupper($subject['title'] . ' -  ' . $subject['section_name']). ' - ' . $subject['course_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="bg-red-700 text-white rounded py-2 px-4 hover:bg-red-800">View Students</button>
        </form>

        <!-- If subject_id is selected, display students -->
        <?php if (!empty($students)): ?>
            <h2 class="text-xl font-semibold mb-4 text-red-700">Students Enrolled in Selected Subject</h2>
            <table  class="w-full border-collapse  shadow-md rounded-lg">
                <thead class="bg-red-800">
                    <tr>
                        <th class="px-4 py-4 border-b text-left text-white">Student Number</th>
                        <th class="px-4 py-4 border-b text-left text-white">Student Name</th>
                        <th class="px-4 py-4 border-b text-left text-white">Subject</th>
                        <th class="px-4 py-4 border-b text-left text-white">Course </th>
                        <th class="px-4 py-4 border-b text-left text-white">Section </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="px-4 py-4"><?= htmlspecialchars(strtoupper($student['student_number'])); ?></td>
                            <td class="px-4 py-4"><?= htmlspecialchars(strtoupper($student['firstname'] . ' , ' . $student['lastname']. ' ' . $student['suffix'])); ?></td>
                            <td class="px-4 py-4"><?= htmlspecialchars(strtoupper($student['subject_title'])); ?></td>
                            <td class="px-4 py-4"><?= htmlspecialchars(strtoupper($student['course_name'])); ?></td>
                            <td class="px-4 py-4"><?= htmlspecialchars(strtoupper($student['section_name'])); ?></td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>
    </div>
</body>
</html>
