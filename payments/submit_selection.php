<?php
session_start();
require '../db/db_connection3.php';

// Check if the user email and student number are set in the session
$user_email = $_SESSION['user_email'] ?? '';
if (empty($user_email)) {
    exit("Error: User email is not set in the session.");
}

$student_number = $_SESSION['student_number'] ?? null;
if (empty($student_number)) {
    exit("Error: Student number is not set in the session.");
}

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit("Error: Invalid request method.");
}

// Retrieve the submitted department, course, sections, subjects, schedules, semester, and school year
$departmentId = $_POST['department'] ?? null;
$courseId = $_POST['course'] ?? null;
$selectedSections = $_POST['sections'] ?? [];
$selectedSubjects = $_POST['subjects'] ?? [];
$selectedSchedules = $_POST['schedules'] ?? [];

// New fields: semester and school year
$semesterId = $_POST['semester'] ?? null; // this is the ID
$schoolYearId = $_POST['school_year'] ?? null; // this is the ID

// Validate department, course, semester, and school year
if (empty($departmentId) || empty($courseId)) {
    exit("Error: Please select both a department and a course.");
}

if (empty($semesterId) || empty($schoolYearId)) {
    exit("Error: Please select a semester and provide the school year.");
}

// Initialize an array to hold data for console log or debugging
$alertData = [];

try {
    $db = Database::connect();

    // Fetch the semester name
    $semesterStmt = $db->prepare("SELECT semester_name FROM semesters WHERE id = :semester_id");
    $semesterStmt->bindParam(':semester_id', $semesterId, PDO::PARAM_INT);
    $semesterStmt->execute();
    $semesterName = $semesterStmt->fetchColumn();

    // Fetch the school year name
    $schoolYearStmt = $db->prepare("SELECT year FROM school_years WHERE id = :school_year_id");
    $schoolYearStmt->bindParam(':school_year_id', $schoolYearId, PDO::PARAM_INT);
    $schoolYearStmt->execute();
    $schoolYearName = $schoolYearStmt->fetchColumn();

    // Iterate through each selected section
    foreach ($selectedSections as $sectionId) {
        $subjectIds = $selectedSubjects[$sectionId] ?? [];

        // Iterate through each selected subject in the current section
        foreach ($subjectIds as $subject_id) {
            // Fetch the corresponding schedule ID for the current subject
            $schedule_id = $selectedSchedules[$subject_id][0] ?? null;

            // If no schedule ID is found for the subject, skip to the next iteration
            if (is_null($schedule_id)) {
                echo "Warning: No schedule ID found for subject ID: $subject_id<br>";
                continue;
            }

            // Check for existing enrollment to prevent duplication
            $checkStmt = $db->prepare("
                SELECT COUNT(*) FROM subject_enrollments 
                WHERE student_number = :student_number 
                AND section_id = :section_id 
                AND department_id = :department_id 
                AND course_id = :course_id 
                AND subject_id = :subject_id
                AND schedule_id = :schedule_id
                AND semester = :semester
                AND school_year = :school_year
            ");
            
            // Bind parameters for the check statement
            $checkStmt->bindParam(':student_number', $student_number, PDO::PARAM_STR);
            $checkStmt->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
            $checkStmt->bindParam(':department_id', $departmentId, PDO::PARAM_INT);
            $checkStmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
            $checkStmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
            $checkStmt->bindParam(':schedule_id', $schedule_id, PDO::PARAM_INT);
            $checkStmt->bindParam(':semester', $semesterName, PDO::PARAM_STR); // Send the semester name
            $checkStmt->bindParam(':school_year', $schoolYearName, PDO::PARAM_STR); // Send the school year name

            // Execute the check statement
            $checkStmt->execute();
            $exists = $checkStmt->fetchColumn() > 0;

            if ($exists) {
                echo "This student is already enrolled in the specified section and subject.<br>";
                continue; // Skip to the next subject if a duplicate is found
            }

            // Add data to alert array for console logging
            $alertData[] = [
                'student_number' => $student_number,
                'department_id' => $departmentId,
                'course_id' => $courseId,
                'section_id' => $sectionId,
                'subject_id' => $subject_id,
                'schedule_id' => $schedule_id,
                'semester' => $semesterName, // Store the name
                'school_year' => $schoolYearName, // Store the name
            ];

            // Prepare the insert statement
            $sectionStmt = $db->prepare("
                INSERT INTO subject_enrollments 
                (student_number, department_id, course_id, section_id, subject_id, schedule_id, semester, school_year)
                VALUES 
                (:student_number, :department_id, :course_id, :section_id, :subject_id, :schedule_id, :semester, :school_year)
            ");

            // Bind parameters for the insert statement
            $sectionStmt->bindParam(':student_number', $student_number, PDO::PARAM_STR);
            $sectionStmt->bindParam(':department_id', $departmentId, PDO::PARAM_INT);
            $sectionStmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
            $sectionStmt->bindParam(':section_id', $sectionId, PDO::PARAM_INT);
            $sectionStmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
            $sectionStmt->bindParam(':schedule_id', $schedule_id, PDO::PARAM_INT);
            $sectionStmt->bindParam(':semester', $semesterName, PDO::PARAM_STR); // Send the semester name
            $sectionStmt->bindParam(':school_year', $schoolYearName, PDO::PARAM_STR); // Send the school year name

            // Execute the query
            $sectionStmt->execute();
        }
    }

    // Redirect to the payment form after successful enrollment
    header("Location: payment_form.php");
    exit;

} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}

?>
