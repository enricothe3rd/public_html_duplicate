<?php
// Start the session
session_start();

// Include your database connection
require '../db/db_connection3.php';
require_once '../vendor/fpdf.php'; // Include FPDF library
$subjects = []; // Initialize variable to hold subjects
try {
    // Create a new PDO connection
    $pdo = Database::connect(); // Assuming Database::connect() returns a PDO instance

    // Adjust column names to match your actual database structure
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
    $subjectStmt->bindParam(':student_number', $_SESSION['student_number'], PDO::PARAM_STR);

    // Execute the statement
    $subjectStmt->execute();

 // Fetch the subjects
 $subjects = $subjectStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}

// Create the PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Subject Selection Details', 0, 1, 'C');

// Set font for the table
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(30, 10, 'Student Number', 1);
$pdf->Cell(30, 10, 'Section', 1);
$pdf->Cell(40, 10, 'Department', 1);
$pdf->Cell(40, 10, 'Course', 1);
$pdf->Cell(30, 10, 'Subject Code', 1);
$pdf->Cell(50, 10, 'Subject Title', 1);
$pdf->Cell(20, 10, 'Units', 1);
$pdf->Cell(30, 10, 'Semester', 1);
$pdf->Cell(30, 10, 'Schedule Day', 1);
$pdf->Cell(30, 10, 'Start Time', 1);
$pdf->Cell(30, 10, 'End Time', 1);
$pdf->Cell(30, 10, 'Room', 1);
$pdf->Ln();

// Add data to the table
$pdf->SetFont('Arial', '', 12);
foreach ($subjects as $row) {
    $pdf->Cell(30, 10, htmlspecialchars($row['student_number']), 1);
    $pdf->Cell(30, 10, htmlspecialchars($row['section_name']), 1);
    $pdf->Cell(40, 10, htmlspecialchars($row['department_name']), 1);
    $pdf->Cell(40, 10, htmlspecialchars($row['course_name']), 1);
    $pdf->Cell(30, 10, htmlspecialchars($row['subject_code']), 1);
    $pdf->Cell(50, 10, htmlspecialchars($row['subject_title']), 1);
    $pdf->Cell(20, 10, htmlspecialchars($row['subject_units']), 1);
    $pdf->Cell(30, 10, htmlspecialchars($row['semester_name']), 1);
    $pdf->Cell(30, 10, htmlspecialchars($row['schedule_day']), 1);
    $pdf->Cell(30, 10, htmlspecialchars($row['schedule_start_time']), 1);
    $pdf->Cell(30, 10, htmlspecialchars($row['schedule_end_time']), 1);
    $pdf->Cell(30, 10, htmlspecialchars($row['schedule_room']), 1);
    $pdf->Ln();
}

// Output the PDF
$pdf->Output('I', 'subject_selection_details.pdf'); // D for download, you can also use I for inline view

?>
