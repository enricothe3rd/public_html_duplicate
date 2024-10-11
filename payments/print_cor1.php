<?php
session_start(); // Start the session

// Include your database connection
require '../db/db_connection3.php'; // Adjust the path as necessary
require_once '../vendor/fpdf.php'; // Include FPDF library

// Create a new PDO instance
$pdo = Database::connect();

$enrollmentData = null; // Initialize variable to hold enrollment data
$subjects = []; // Initialize variable to hold subjects
$payments = [];

try {
    // Check if student_number is set in the session
    if (isset($_SESSION['student_number'])) {
        $student_number = $_SESSION['student_number'];
        // echo "Student number found in session: $student_number\n"; // Debugging statement

        // Prepare the SQL statement with JOINs to fetch course, section, and department info
        $stmt = $pdo->prepare("
            SELECT e.*, 
                   se.course_id,
                   c.course_name,
                   s.name AS section_name,
                   d.name AS department_name,
                   e.firstname,
                   e.middlename,
                   e.lastname,
                   e.suffix,
                   e.sex  
            FROM enrollments e
            LEFT JOIN subject_enrollments se ON e.student_number = se.student_number
            LEFT JOIN courses c ON se.course_id = c.id
            LEFT JOIN sections s ON se.section_id = s.id
            LEFT JOIN departments d ON c.department_id = d.id
            WHERE e.student_number = :student_number
        ");
    
        $stmt->bindParam(':student_number', $student_number, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch the results
        $enrollmentData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debugging statement to check the fetched enrollment data
        if ($enrollmentData) {
            // echo "Enrollment data found:\n";
            // echo "<pre>";
            // print_r($enrollmentData);
            // echo "</pre>";
        } else {
            // echo "No enrollment data found for student number: $student_number\n";
        }
    } else {
        // echo "Student number not found in session.\n";
    }

    // Adjust column names to match your actual database structure
    $SubjectStmt = $pdo->prepare("
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
            se.school_year -- Ensure this column is selected
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
    $SubjectStmt->bindParam(':student_number', $_SESSION['student_number'], PDO::PARAM_STR);
    // Execute the statement
    $SubjectStmt->execute();

    // Fetch the subjects
    $subjects = $SubjectStmt->fetchAll(PDO::FETCH_ASSOC);

    // Debugging statement to check fetched subjects
    if (empty($subjects)) {
        // echo "No subjects found for student number: " . htmlspecialchars($_SESSION['student_number']) . "\n";
    } else {
        // echo "Subjects found:\n";
        // echo "<pre>";
        // print_r($subjects);
        // echo "</pre>";
    }

    // SQL query to fetch payment details based on student_number from the session
    $paymentStmt = $pdo->prepare("
        SELECT 
            p.id,
            p.student_number,
            p.number_of_units,
            p.amount_per_unit,
            p.miscellaneous_fee,
            p.total_payment,
            p.payment_method,
            IFNULL(p.research_fee, '') AS research_fee,
            IFNULL(p.transfer_fee, '') AS transfer_fee,
            IFNULL(p.overload_fee, '') AS overload_fee,
            p.created_at,
            p.updated_at,
            p.transaction_id
        FROM payments p
        WHERE p.student_number = :student_number
    ");

    // Bind the session student number to the SQL statement
    $paymentStmt->bindParam(':student_number', $_SESSION['student_number'], PDO::PARAM_STR);

    // Execute the statement
    $paymentStmt->execute();

    // Fetch the payment details
    $payments = $paymentStmt->fetchAll(PDO::FETCH_ASSOC);

    // Debugging statement to check fetched payment details
    if (empty($payments)) {
        // echo "No payment details found for student number: " . htmlspecialchars($_SESSION['student_number']) . "\n";
    } else {
        // echo "Payment details found:\n";
        // echo "<pre>";
        // print_r($payments);
        // echo "</pre>";
    }

} catch (PDOException $e) {
    // Handle any errors
    $error_message = "Error: " . $e->getMessage();
    // echo $error_message; // Debugging statement to show error
}

// Check if enrollment data was retrieved successfully
if ($enrollmentData) {
    // Combine first name, middle name, last name, and suffix
    $fullname = htmlspecialchars($enrollmentData['firstname']);
    if (!empty($enrollmentData['middlename'])) {
        $fullname .= ' ' . htmlspecialchars($enrollmentData['middlename']);
    }
    $fullname .= ' ' . htmlspecialchars($enrollmentData['lastname']);
    if (!empty($enrollmentData['suffix'])) {
        $fullname .= ' ' . htmlspecialchars($enrollmentData['suffix']);
    }

    // Display the full name
    // echo "Full Name: $fullname\n";

    // Create the PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Add the logo image at the top left (adjust the path and size as needed)
    $pdf->Image('../assets/images/school-logo/bcc-icon.png', 33, 12, 16); // X, Y, Width

    // Set title
    $pdf->SetFont('Courier', 'B', 17);
    $pdf->Cell(0, 6, 'BINANGONAN CATHOLIC COLLEGE', 0, 1, 'C');

    // Add school details
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 4, 'Binangonan Rizal', 0, 1, 'C');
    $pdf->Ln(2); // Add a line break

    // Add school details
    $pdf->SetFont('Courier', 'I', 16);
    $pdf->Cell(0, 4, 'COLLEGE DEPARTMENT', 0, 1, 'C');
    $pdf->Ln(6); // Add a line break

    // Add school details
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetX(6); // Change 6 to the desired left margin
    $pdf->Cell(0, 4, 'Registration Form', 0, 1, 'L');

    // Add the font files for Times New Roman
    $pdf->AddFont('TimesNewRoman', '', 'times.php'); // Regular Times New Roman
    $pdf->AddFont('TimesNewRoman', 'B', 'timesb.php'); // Bold Times New Roman

    // Set font to bold for the labels and increase the size
    $pdf->SetFont('TimesNewRoman', 'B', 9); // Use the newly added bold font

    // Get the school year from subjects, if available
    $schoolYear = !empty($subjects) ? htmlspecialchars($subjects[0]['school_year']) : 'No School Year Available'; // Use first subject's school_year or default message

    // Define fields to display (with combined full name)
    $fields = [
        'Student' => $fullname, // Combined full name
        'Year' => $schoolYear, // Display school year
        'Course' => htmlspecialchars($enrollmentData['course_name']),
        'Date of Birth' => htmlspecialchars($enrollmentData['dob']),
        'Address' => htmlspecialchars($enrollmentData['address']),
        'Email' => htmlspecialchars($enrollmentData['email']),
        'Contact No' => htmlspecialchars($enrollmentData['contact_no']),
        'Status' => htmlspecialchars($enrollmentData['status']),
        'Section' => htmlspecialchars($enrollmentData['section_name']),
    ];


// Define line height
$lineHeight = 6; // Height of each line (increased for better readability)
$pdf->SetY($pdf->GetY() -6 ); // Start Y position with some space

// Calculate position for the rectangle
$x = 146; // X position for the rectangle
$y = $pdf->GetY() - 6; // Y position for the rectangle (adjust as necessary)
$width = 40; // Width of the rectangle
$height = 12; // Height of the rectangle (adjusted for better fit)

// Draw bold rectangle by overlapping multiple rectangles
$boldOffset = 0.6; // Adjust this value for desired boldness

// Draw the outer rectangle
$pdf->SetLineWidth(0.6); // Set line width for outer rectangle
$pdf->Rect($x - $boldOffset, $y - $boldOffset, $width + 2 * $boldOffset, $height + 2 * $boldOffset); // Outer rectangle

// Draw the inner rectangle
$pdf->SetLineWidth(0.3); // Set line width for inner rectangle
$pdf->Rect($x, $y, $width, $height); // Inner rectangle

// Add "STUDENT'S COPY" label on the top right corner
$pdf->SetXY($x, $y); // Adjust Y position for the first label
$pdf->SetFont('TimesNewRoman', 'B', 9);
$pdf->Cell($width, $lineHeight, "COLLEGE DEP. COPY", 0, 1, 'C'); // Center aligned within the rectangle

// Add "FIRST SEMESTER, A.Y. 2624-2626" label below "STUDENT'S COPY"
$pdf->SetXY($x, $y + 6); // Adjust Y position for the second label
$pdf->SetFont('TimesNewRoman', 'B', 7);
$pdf->Cell($width, $lineHeight, "FIRST SEMESTER, A.Y. 2624-2626", 0, 1, 'C'); // Center aligned within the rectangle

// Move to the next line with some extra space
$pdf->Ln(6);


// Define line height
$lineHeight = 3; // Height of each line (increased for better readability)
$pdf->SetY($pdf->GetY() ); // Start Y position with some space

// Output the Student Number on its own row
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->SetX(6); // Change 6 to the desired left margin
$pdf->Cell(26, $lineHeight, "Student No:", 0);
$pdf->SetFont('TimesNewRoman', '', 9);
$pdf->SetX(24); // Change 6 to the desired left margin
$pdf->Cell(26, $lineHeight, htmlspecialchars($student_number),);


    


// Get the current X and Y positions
$currentX = $pdf->GetX();
$currentY = $pdf->GetY();

// Set the Y position for the underline
$pdf->SetY($currentY + 3); // Slightly below the text
$pdf->SetX(26); // Move to the right to align with the student number text

// Draw the underline
$pdf->Cell(26, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

// Move to the next line with some extra space
$pdf->Ln($lineHeight );




    // Define width for labels and values
    $labelWidth = 26; // Width for labels (increased for better readability)
    $valueWidth = 26; // Width for values
    $xStart = 6; // Starting X position

    // Output the first three fields ('Student', 'Year', 'Course') in the first row
    $pdf->SetX($xStart);

    // Set font to bold for label
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Student:", 0);
    $pdf->SetX(26);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell($valueWidth, $lineHeight, $fullname, 0);

    $pdf->SetX(72); // Move to next position
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Year:", 0);
    $pdf->SetX(81 );
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($schoolYear), 0);

    $pdf->SetX(100 ); // Move to next position
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Course:", 0);
    $pdf->SetX(114);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['course_name']), 0);


    // $pdf->SetX(146 ); // Move to next position
    // $pdf->SetFont('Helvetica', 'B', 9);
    // $pdf->Cell($labelWidth, $lineHeight, "Sex:", 0);
    // $pdf->SetX(162);
    // $pdf->SetFont('Helvetica', '', 9);
    // $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['sex']), 0);


    $underlineX = $pdf->GetX(); // Left to Right
    $underlineY = $pdf->GetY(); //Top to Buttom
    
    //Student Underline
    $pdf->SetY($underlineY + 3.6);
    $pdf->SetX(20); 
    $pdf->Cell(60, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

    // Year Underline
    $pdf->SetY($currentY + 9.6); 
    $pdf->SetX(82 ); 
    $pdf->Cell(14, 0, '', 'T'); 
    
    // Course Underline
    $pdf->SetY($underlineY + 3.6);
    $pdf->SetX(114  ); 
    // Draw the underline
    $pdf->Cell(87, 0, '', 'T'); // The 'T' parameter draws a top border (underline)    
   

    // Sex Underline
    // $pdf->SetY($underlineY + 3.6);
    // $pdf->SetX(162  ); 
    // // Draw the underline
    // $pdf->Cell(11, 0, '', 'T'); // The 'T' parameter draws a top border (underline)   


   // Set the font style and size
    $pdf->SetFont('Courier', 'I', 6); // Font family: Arial, style: Bold, size: 12
    // Set the x position
    $pdf->SetX(28); // Set this to your desired left margin
    // Create the first cell of the new row with the set font
    $pdf->Cell(26, $lineHeight, htmlspecialchars("LAST NAME, GIVEN NAME, MIDDLE NAME")); // First cell of the new row



    
    $pdf->Ln(4); // Move to the next line with some extra space


    // Output the next row for 'Data of Birth', 'Address', and 'Email' in the same row
    $pdf->SetX(6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Data of Birth:", 0);
    $pdf->SetX(27);
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['dob']), 0);
    
    $pdf->SetX(46); // Move to next position
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Present Address:", 0);
    $pdf->SetX(73);
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['address']), 0);
    
    $pdf->SetX(138); // Move to next position
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Email Address:", 0);
    $pdf->SetX($xStart + $labelWidth + $valueWidth + 6 + $labelWidth + $valueWidth + $labelWidth + $labelWidth + 2);
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['email']), 0);

    $underlineX = $pdf->GetX(); // Left to Right
    $underlineY = $pdf->GetY(); //Top to Buttom
    
    //Date of Birth Underline

    $pdf->SetY($underlineY + 3.6);
    $pdf->SetX(27); // Set this to your desired left margin
    $pdf->Cell(16, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

    // Address Underline
    $pdf->SetY($underlineY + 3.6);
    $pdf->SetX( 73  ); 
    // Draw the underline
    $pdf->Cell(62, 0, '', 'T'); // The 'T' parameter draws a top border (underline)    


    // Email Underline
    $pdf->SetY($underlineY + 3.6);
    $pdf->SetX($underlineX - 26  ); 
    // Draw the underline
    $pdf->Cell(36, 0, '', 'T'); // The 'T' parameter draws a top border (underline)   



    $pdf->Ln($lineHeight ); // Move to the next line with some extra space

    // Output the remaining fields in a similar manner

    $pdf->SetX(6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Status:", 0);
    $pdf->SetX(17 );
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['status']), 0);

    $pdf->SetX(124 ); // Move to next position
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Sex:", 0);
    $pdf->SetX(133);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['sex']), 0);

    $pdf->SetX(145);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Contact No:", 0);
    $pdf->SetX(165);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['contact_no']), 0);

    $pdf->SetX($xStart + $labelWidth + $valueWidth + 6); // Move to next position


    $pdf->SetX(38); // Move to next position
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Section:", 0);
    $pdf->SetX(52);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['section_name']), 0);

    
    $pdf->SetX(70); // Move to next position
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Department:", 0);
    $pdf->SetX(90);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['department_name']), 0);

    //Status underline
    $pdf->SetY($underlineY + 9.6);
    $pdf->SetX(17); // Set this to your desired left margin
    $pdf->Cell(16, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

    //Section underline
    $pdf->SetY($underlineY + 9.6);
    $pdf->SetX(51); // Set this to your desired left margin
    $pdf->Cell(11, 0, '', 'T'); // The 'T' parameter draws a top border (underline)


    //Department underline
    $pdf->SetY($underlineY + 9.6);
    $pdf->SetX(90); // Set this to your desired left margin
    $pdf->Cell(30, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

    //sext underline
    $pdf->SetY($underlineY + 9.6);
    $pdf->SetX(132); // Set this to your desired left margin
    $pdf->Cell(10, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

    //contact underline
    $pdf->SetY($underlineY + 9.6);
    $pdf->SetX(165); // Set this to your desired left margin
    $pdf->Cell(22, 0, '', 'T'); // The 'T' parameter draws a top border (underline)



    $pdf->Ln(10 ); // Move to the next line with some extra space
    $pdf->SetX(10);
    $pdf->SetFont('Arial', 'B', 10);

    // Convert start_time to AM/PM if it exists
$formatted_start_time = isset($subject['start_time']) ? (new DateTime($subjects['start_time']))->format('h:i A') : 'N/A';

// Convert end_time to AM/PM if it exists
$formatted_end_time = isset($subject['end_time']) ? (new DateTime($subjects['end_time']))->format('h:i A') : 'N/A';
    // $pdf->Cell(0, 6, 'Subject Selection Details', 0, 1, 'C');
    
    // Set font for the table
    $pdf->SetFont('Arial', 'B', 6);
    
    // $pdf->Cell(26, 6, 'Student Number', 1);
    // $pdf->Cell(26, 6, 'Section', 1);
    // $pdf->Cell(26, 6, 'Department', 1);
    // $pdf->Cell(26, 6, 'Course', 1);
    $pdf->Cell(10, 7, 'Code', 1);
    $pdf->Cell(50, 7, 'Title', 1);
    $pdf->Cell(10, 7, 'Units', 1);
    // $pdf->Cell(26, 6, 'Semester', 1);

    // $pdf->Cell(26, 6, 'Start Time', 1);
    // $pdf->Cell(26, 6, 'End Time', 1);
    $pdf->Cell(15, 7, 'Room', 1);
    $pdf->Cell(35, 7, 'Day & Time', 1);
    $pdf->Ln();
    
    // Add data to the table
    $pdf->SetFont('Arial', '', 7);
    foreach ($subjects as $row) {
        // $pdf->Cell(26, 6, htmlspecialchars($row['student_number']), 1);
        // $pdf->Cell(26, 6, htmlspecialchars($row['section_name']), 1);
        // $pdf->Cell(26, 6, htmlspecialchars($row['department_name']), 1);
        // $pdf->Cell(26, 6, htmlspecialchars($row['course_name']), 1);
        $pdf->Cell(10, 8, htmlspecialchars($row['subject_code']), 1);
        $pdf->Cell(50, 8, htmlspecialchars($row['subject_title']), 1);
        $pdf->Cell(10, 8, htmlspecialchars($row['subject_units']), 1);
        // $pdf->Cell(26, 6, htmlspecialchars($row['semester_name']), 1);

        


        $pdf->Cell(15, 8, htmlspecialchars($row['room']), 1);
        $pdf->Cell(35, 8, htmlspecialchars($row['day_of_week'] . ' ' . date("g:i A", strtotime($row['start_time'])) . ' - ' . date("g:i A", strtotime($row['end_time']))), 1);


        $pdf->Ln(8);
    }
    
    
  




    foreach ($payments as $row) {
        $pdf->Ln(2); // Add some space between each record
        $pdf->SetFont('Arial', 'B', 12); // Set bold for the titles
    
        // Define the rectangle's position and size
        $x = 150; // Set X coordinate for the right side (adjust as needed)
        $y = 70;
        $width = 50; // Width of the rectangle
        $height = 68; // Height of the rectangle
    
        // Draw the rectangle
        $pdf->Rect($x, $y, $width, $height);
    
        // Set the position to start writing inside the rectangle
        $pdf->SetXY($x + 2, $y + 2); // Adjust margins inside the rectangle
    
        // Start displaying the payment details inside the rectangle
        $pdf->SetY(68);
        $pdf->SetX(165);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 10, 'PAYMENTS ', 0, 1);
        
        // Displaying the payment details
        $pdf->SetY(75);
        $pdf->SetX(151);
        $pdf->Cell(0, 10, 'Payment Method: ' . htmlspecialchars($row['payment_method']), 0, 1);
        
        //Payment underline
        $pdf->SetY(81.5);
        $pdf->SetX(175); // Set this to your desired left margin
        $pdf->Cell(11, 0, '', 'T'); // The 'T' parameter draws a top border (underline)


        $pdf->SetY(80);
        $pdf->SetX(151);
        $pdf->Cell(0, 10, 'Tuition Fee (per unit): ' . htmlspecialchars($row['amount_per_unit']), 0, 1);

        //Tuition  underline
        $pdf->SetY(86.5);
        $pdf->SetX(181); // Set this to your desired left margin
        $pdf->Cell(11, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

        $pdf->SetY(87);
        $pdf->SetX(163);
        $pdf->Cell(0, 10, 'Tuition Fee: ', 0, 1);

        $pdf->SetY(93);
        $pdf->SetX(152);
        $pdf->Cell(0, 10, 'Miscellaneous Fee: ' . htmlspecialchars($row['miscellaneous_fee']), 0, 1);
        
        //Tuition  underline
        $pdf->SetY(99.5);
        $pdf->SetX(179); // Set this to your desired left margin
        $pdf->Cell(12, 0, '', 'T'); // The 'T' parameter draws a top border (underline)


        $pdf->SetY(98);
        $pdf->SetX(152); // Set this to your desired left margin
        $pdf->Cell(0, 10, 'Research Fee: ' . htmlspecialchars($row['research_fee'] ?? ''), 0, 1);
        
        //Research  underline
        $pdf->SetY(104.5);
        $pdf->SetX(173); // Set this to your desired left margin
        $pdf->Cell(20, 0, '', 'T'); // The 'T' parameter draws a top border (underline)
       
       $pdf->SetY(105);
       $pdf->SetX(152); 
        $pdf->Cell(0, 10, 'Transfer Fee: ' . htmlspecialchars($row['transfer_fee'] ?? ''), 0, 1);

        //Transfer  underline
        $pdf->SetY(111.5);
        $pdf->SetX(173); // Set this to your desired left margin
        $pdf->Cell(20, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

        $pdf->SetY(111);
        $pdf->SetX(152); 
        $pdf->Cell(0, 10, 'Overload: ' . htmlspecialchars($row['overload_fee'] ?? ''), 0, 1);
    
        //Transfer  underline
        $pdf->SetY(117);
        $pdf->SetX(169); // Set this to your desired left margin
        $pdf->Cell(24, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

        
        $pdf->SetY(116);
        $pdf->SetX(152); 
        $pdf->Cell(0, 10, 'Installment (DP): ' . htmlspecialchars($row['total_payment']), 0, 1);

        //Installment  underline
        $pdf->SetY(122.5);
        $pdf->SetX(174); // Set this to your desired left margin
        $pdf->Cell(19, 0, '', 'T'); // The 'T' parameter draws a top border (underline)
        // Payment Summary
        if ($row['payment_method'] === 'Installment') {
            $pdf->Cell(0, 10, 'Installment (DP): ' . htmlspecialchars($row['total_payment']), 0, 1);
        } else {
            $pdf->Cell(0, 10, '', 0, 1); // Empty line for 'Cash' payment method
        }
        $pdf->SetY(122.5);
        $pdf->SetX(152); // Set this to your desired left margin
        $pdf->Cell(0, 10, 'Total: ' . htmlspecialchars($row['total_payment']), 0, 1);
    
        //Total  underline
        $pdf->SetY(129);
        $pdf->SetX(161); // Set this to your desired left margin
        $pdf->Cell(32, 0, '', 'T'); // The 'T' parameter draws a top border (underline)


        //Total  underline
        $pdf->SetY(129);
        $pdf->SetX(152); // Set this to your desired left margin
        $pdf->Cell(0, 10, 'ASSESSED BY:', 0, 1);

        //ASSESSED  underline
        $pdf->SetY(135);
        $pdf->SetX(173); // Set this to your desired left margin
        $pdf->Cell(20, 0, '', 'T'); // The 'T' parameter draws a top border (underline)
    




        // Move to the next line after finishing the rectangle
        $pdf->Ln(5); // Add some space before the next payment block
    }


            //Total  underline
        $pdf->SetY(120);
        $pdf->SetX(5); // Set this to your desired left margin
        $pdf->Cell(0, 10, 'Students Signature', 0, 1);

        $pdf->SetY(141 -15);
        $pdf->SetX(34); // Set this to your desired left margin
        $pdf->Cell(44, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

        //Total  underline
       $pdf->SetY(135 -15);
       $pdf->SetX(80); // Set this to your desired left margin
       $pdf->Cell(0, 10, 'Evaluated by', 0, 1);

       $pdf->SetY(141 -15);
       $pdf->SetX(100); // Set this to your desired left margin
       $pdf->Cell(44, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

       //Total  underline
       $pdf->SetY(140 -15);
       $pdf->SetX(5); // Set this to your desired left margin
       $pdf->Cell(0, 10, 'Date Accomplished', 0, 1);

       $pdf->SetY(146.5 -15);
       $pdf->SetX(34); // Set this to your desired left margin
       $pdf->Cell(44, 0, '', 'T'); // The 'T' parameter draws a top border (underline)
        //Total  underline
        $pdf->SetY(140 -15);
        $pdf->SetX(80); // Set this to your desired left margin
        $pdf->Cell(0, 10, 'Enrolling Officer/ Registrar', 0, 1);


        $pdf->SetY(146.5 -15);
        $pdf->SetX(118); // Set this to your desired left margin
        $pdf->Cell(30, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

        $pdf->SetY(147 -15);
        $pdf->SetX(5); // Set this to your desired left margin
        $pdf->Cell(0, 6, 'i certify that this student is allowed to enroll in the subject listed with corrsponding number of units', 0, 1);

        $pdf->SetY(154.5 -15);
        $pdf->SetX(5); // Set this to your desired left margin
        $pdf->Cell(201, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

        $pdf->SetY(155 -15) ;
        $pdf->SetX(5); // Set this to your desired left margin
        $pdf->Cell(201, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

        $pdf->Ln(1); // Add a line break
    // Add the logo image at the top left (adjust the path and size as needed)
    $pdf->Image('../assets/images/school-logo/bcc-icon.png', 33, 144, 16); // X, Y, Width

    // Set title
    $pdf->SetFont('Courier', 'B', 17);
    $pdf->Cell(0, 6, 'BINANGONAN CATHOLIC COLLEGE', 0, 1, 'C');
    
    // Add school details
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 4, 'Binangonan Rizal', 0, 1, 'C');
    $pdf->Ln(2); // Add a line break

     // Add school details
     $pdf->SetFont('Courier', 'I', 16);
     $pdf->Cell(0, 4, 'COLLEGE DEPARTMENT', 0, 1, 'C');
     $pdf->Ln(6); // Add a line break

   
    // Add school details
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->SetX(6); // Change 6 to the desired left margin
    $pdf->Cell(0, 4, 'Registration Form', 0, 1, 'L');

    



    // Add the font files for Times New Roman
    $pdf->AddFont('TimesNewRoman', '', 'times.php'); // Regular Times New Roman
    $pdf->AddFont('TimesNewRoman', 'B', 'timesb.php'); // Bold Times New Roman

    // Set font to bold for the labels and increase the size
    $pdf->SetFont('TimesNewRoman', 'B', 9); // Use the newly added bold font

    // Define fields to display (with combined full name)
    $fields = [
        'Student' => $fullname, // Combined full name
        'Year' => htmlspecialchars($schoolYear),
        'Course' => htmlspecialchars($enrollmentData['course_name']),
        'Data of Birth' => htmlspecialchars($enrollmentData['dob']),
        'Address' => htmlspecialchars($enrollmentData['address']),
        'Email' => htmlspecialchars($enrollmentData['email']),
        'Contact No' => htmlspecialchars($enrollmentData['contact_no']),
        'Status' => htmlspecialchars($enrollmentData['status']),
        'Section' => htmlspecialchars($enrollmentData['section_name']),
    ];



// Define line height
$lineHeight = 6; // Height of each line (increased for better readability)
$pdf->SetY($pdf->GetY() -6 ); // Start Y position with some space

// Calculate position for the rectangle
$x = 146; // X position for the rectangle
$y = $pdf->GetY() - 6; // Y position for the rectangle (adjust as necessary)
$width = 40; // Width of the rectangle
$height = 12; // Height of the rectangle (adjusted for better fit)

// Draw bold rectangle by overlapping multiple rectangles
$boldOffset = 0.6; // Adjust this value for desired boldness

// Draw the outer rectangle
$pdf->SetLineWidth(0.6); // Set line width for outer rectangle
$pdf->Rect($x - $boldOffset, $y - $boldOffset, $width + 2 * $boldOffset, $height + 2 * $boldOffset); // Outer rectangle

// Draw the inner rectangle
$pdf->SetLineWidth(0.3); // Set line width for inner rectangle
$pdf->Rect($x, $y, $width, $height); // Inner rectangle

// Add "STUDENT'S COPY" label on the top right corner
$pdf->SetXY($x, $y); // Adjust Y position for the first label
$pdf->SetFont('TimesNewRoman', 'B', 9);
$pdf->Cell($width, $lineHeight, "REGISTRAR'S COPY", 0, 1, 'C'); // Center aligned within the rectangle

// Add "FIRST SEMESTER, A.Y. 2624-2626" label below "STUDENT'S COPY"
$pdf->SetXY($x, $y + 6); // Adjust Y position for the second label
$pdf->SetFont('TimesNewRoman', 'B', 7);
$pdf->Cell($width, $lineHeight, "FIRST SEMESTER, A.Y. 2624-2626", 0, 1, 'C'); // Center aligned within the rectangle

// Move to the next line with some extra space
$pdf->Ln(6);


// Define line height
$lineHeight = 3; // Height of each line (increased for better readability)
$pdf->SetY($pdf->GetY() ); // Start Y position with some space

// Output the Student Number on its own row
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->SetX(6); // Change 6 to the desired left margin
$pdf->Cell(26, $lineHeight, "Student No:", 0);
$pdf->SetFont('TimesNewRoman', '', 9);
$pdf->SetX(24); // Change 6 to the desired left margin
$pdf->Cell(26, $lineHeight, htmlspecialchars($student_number),);


    


// Get the current X and Y positions
$currentX = $pdf->GetX();
$currentY = $pdf->GetY();

// Set the Y position for the underline
$pdf->SetY($currentY + 3); // Slightly below the text
$pdf->SetX(26); // Move to the right to align with the student number text

// Draw the underline
$pdf->Cell(26, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

// Move to the next line with some extra space
$pdf->Ln($lineHeight );




    // Define width for labels and values
    $labelWidth = 26; // Width for labels (increased for better readability)
    $valueWidth = 26; // Width for values
    $xStart = 6; // Starting X position

    // Output the first three fields ('Student', 'Year', 'Course') in the first row
    $pdf->SetX($xStart);

    // Set font to bold for label
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Student:", 0);
    $pdf->SetX(26);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell($valueWidth, $lineHeight, $fullname, 0);

    $pdf->SetX(72); // Move to next position
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Year:", 0);
    $pdf->SetX(81 );
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($schoolYear), 0);

    $pdf->SetX(100 ); // Move to next position
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Course:", 0);
    $pdf->SetX(114);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['course_name']), 0);




    $underlineX = $pdf->GetX(); // Left to Right
    $underlineY = $pdf->GetY(); //Top to Buttom
    
    //Student Underline
    $pdf->SetY($underlineY + 3.6);
    $pdf->SetX(20); 
    $pdf->Cell(60, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

    // Year Underline
    $pdf->SetY($currentY + 9.6); 
    $pdf->SetX(82 ); 
    $pdf->Cell(14, 0, '', 'T'); 
    
    // Course Underline
    $pdf->SetY($underlineY + 3.6);
    $pdf->SetX(114  ); 
    // Draw the underline
    $pdf->Cell(87, 0, '', 'T'); // The 'T' parameter draws a top border (underline)    
   

    // Sex Underline
    $pdf->SetY($underlineY + 3.6);
    $pdf->SetX(162  ); 
    // Draw the underline
    $pdf->Cell(11, 0, '', 'T'); // The 'T' parameter draws a top border (underline)   


   // Set the font style and size
    $pdf->SetFont('Courier', 'I', 6); // Font family: Arial, style: Bold, size: 12
    // Set the x position
    $pdf->SetX(28); // Set this to your desired left margin
    // Create the first cell of the new row with the set font
    $pdf->Cell(26, $lineHeight, htmlspecialchars("LAST NAME, GIVEN NAME, MIDDLE NAME")); // First cell of the new row



    
    $pdf->Ln(4); // Move to the next line with some extra space


    // Output the next row for 'Data of Birth', 'Address', and 'Email' in the same row
    $pdf->SetX(6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Data of Birth:", 0);
    $pdf->SetX(27);
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['dob']), 0);
    
    $pdf->SetX(46); // Move to next position
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Present Address:", 0);
    $pdf->SetX(73);
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['address']), 0);
    
    $pdf->SetX(138); // Move to next position
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Email Address:", 0);
    $pdf->SetX($xStart + $labelWidth + $valueWidth + 6 + $labelWidth + $valueWidth + $labelWidth + $labelWidth + 2);
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['email']), 0);

    $underlineX = $pdf->GetX(); // Left to Right
    $underlineY = $pdf->GetY(); //Top to Buttom
    
    //Date of Birth Underline

    $pdf->SetY($underlineY + 3.6);
    $pdf->SetX(27); // Set this to your desired left margin
    $pdf->Cell(16, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

    // Address Underline
    $pdf->SetY($underlineY + 3.6);
    $pdf->SetX( 73  ); 
    // Draw the underline
    $pdf->Cell(62, 0, '', 'T'); // The 'T' parameter draws a top border (underline)    


    // Email Underline
    $pdf->SetY($underlineY + 3.6);
    $pdf->SetX($underlineX - 26  ); 
    // Draw the underline
    $pdf->Cell(36, 0, '', 'T'); // The 'T' parameter draws a top border (underline)   



    $pdf->Ln($lineHeight ); // Move to the next line with some extra space

    // Output the remaining fields in a similar manner

    $pdf->SetX(6);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Status:", 0);
    $pdf->SetX(17 );
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['status']), 0);

    
    $pdf->SetX(124 ); // Move to next position
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Sex:", 0);
    $pdf->SetX(133);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['sex']), 0);

    $pdf->SetX(145);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Contact No:", 0);
    $pdf->SetX(165);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['contact_no']), 0);

    $pdf->SetX($xStart + $labelWidth + $valueWidth + 6); // Move to next position


    $pdf->SetX(38); // Move to next position
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Section:", 0);
    $pdf->SetX(52);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['section_name']), 0);

    
    $pdf->SetX(70); // Move to next position
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($labelWidth, $lineHeight, "Department:", 0);
    $pdf->SetX(90);
    $pdf->SetFont('Helvetica', '', 9);
    $pdf->Cell($valueWidth, $lineHeight, htmlspecialchars($enrollmentData['department_name']), 0);

    //Status underline
    $pdf->SetY($underlineY + 9.6);
    $pdf->SetX(17); // Set this to your desired left margin
    $pdf->Cell(16, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

    //Section underline
    $pdf->SetY($underlineY + 9.6);
    $pdf->SetX(51); // Set this to your desired left margin
    $pdf->Cell(11, 0, '', 'T'); // The 'T' parameter draws a top border (underline)


    //Department underline
    $pdf->SetY($underlineY + 9.6);
    $pdf->SetX(90); // Set this to your desired left margin
    $pdf->Cell(30, 0, '', 'T'); // The 'T' parameter draws a top border (underline)


 //sext underline
 $pdf->SetY($underlineY + 9.6);
 $pdf->SetX(132); // Set this to your desired left margin
 $pdf->Cell(10, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

 //contact underline
 $pdf->SetY($underlineY + 9.6);
 $pdf->SetX(165); // Set this to your desired left margin
 $pdf->Cell(22, 0, '', 'T'); // The 'T' parameter draws a top border (underline)


    $pdf->Ln(10 ); // Move to the next line with some extra space
    $pdf->SetX(10);
    $pdf->SetFont('Arial', 'B', 10);

    // Convert start_time to AM/PM if it exists
$formatted_start_time = isset($subject['start_time']) ? (new DateTime($subjects['start_time']))->format('h:i A') : 'N/A';

// Convert end_time to AM/PM if it exists
$formatted_end_time = isset($subject['end_time']) ? (new DateTime($subjects['end_time']))->format('h:i A') : 'N/A';
    // $pdf->Cell(0, 6, 'Subject Selection Details', 0, 1, 'C');
    
    // Set font for the table
    $pdf->SetFont('Arial', 'B', 6);
    
    // $pdf->Cell(26, 6, 'Student Number', 1);
    // $pdf->Cell(26, 6, 'Section', 1);
    // $pdf->Cell(26, 6, 'Department', 1);
    // $pdf->Cell(26, 6, 'Course', 1);
    $pdf->Cell(10, 7, 'Code', 1);
    $pdf->Cell(50, 7, 'Title', 1);
    $pdf->Cell(10, 7, 'Units', 1);
    // $pdf->Cell(26, 6, 'Semester', 1);

    // $pdf->Cell(26, 6, 'Start Time', 1);
    // $pdf->Cell(26, 6, 'End Time', 1);
    $pdf->Cell(15, 7, 'Room', 1);
    $pdf->Cell(35, 7, 'Day & Time', 1);
    $pdf->Ln();
    
    // Add data to the table
    $pdf->SetFont('Arial', '', 7);
    foreach ($subjects as $row) {
        // $pdf->Cell(26, 6, htmlspecialchars($row['student_number']), 1);
        // $pdf->Cell(26, 6, htmlspecialchars($row['section_name']), 1);
        // $pdf->Cell(26, 6, htmlspecialchars($row['department_name']), 1);
        // $pdf->Cell(26, 6, htmlspecialchars($row['course_name']), 1);
        $pdf->Cell(10, 8, htmlspecialchars($row['subject_code']), 1);
        $pdf->Cell(50, 8, htmlspecialchars($row['subject_title']), 1);
        $pdf->Cell(10, 8, htmlspecialchars($row['subject_units']), 1);
        // $pdf->Cell(26, 6, htmlspecialchars($row['semester_name']), 1);

        


        $pdf->Cell(15, 8, htmlspecialchars($row['room']), 1);
        $pdf->Cell(35, 8, htmlspecialchars($row['day_of_week'] . ' ' . date("g:i A", strtotime($row['start_time'])) . ' - ' . date("g:i A", strtotime($row['end_time']))), 1);


        $pdf->Ln(8);
    }
    
    
  




    foreach ($payments as $row) {
        $pdf->Ln(2); // Add some space between each record
        $pdf->SetFont('Arial', 'B', 12); // Set bold for the titles
    
        // Define the rectangle's position and size
        $x = 150; // Set X coordinate for the right side (adjust as needed)
        $y = 200;
        $width = 50; // Width of the rectangle
        $height = 75; // Height of the rectangle
    
        // Draw the rectangle
        $pdf->Rect($x, $y, $width, $height);
    
        // Set the position to start writing inside the rectangle
        $pdf->SetXY($x + 2, $y + 2); // Adjust margins inside the rectangle
    
        // Start displaying the payment details inside the rectangle
        $pdf->SetY(212 - 15);
        $pdf->SetX(165);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 10, 'PAYMENTS ', 0, 1);
        
        // Displaying the payment details
        $pdf->SetY(220 - 15);
        $pdf->SetX(151);
        $pdf->Cell(0, 10, 'Payment Method: ' . htmlspecialchars($row['payment_method']), 0, 1);
        
        //Payment underline
        $pdf->SetY(226.5 - 15);
        $pdf->SetX(175); // Set this to your desired left margin
        $pdf->Cell(11, 0, '', 'T'); // The 'T' parameter draws a top border (underline)


        $pdf->SetY(226 - 15);
        $pdf->SetX(151);
        $pdf->Cell(0, 10, 'Tuition Fee (per unit): ' . htmlspecialchars($row['amount_per_unit']), 0, 1);

        //Tuition  underline
        $pdf->SetY(232.5 - 15);
        $pdf->SetX(181); // Set this to your desired left margin
        $pdf->Cell(11, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

        $pdf->SetY(236 - 15);
        $pdf->SetX(163);
        $pdf->Cell(0, 10, 'Tuition Fee: ', 0, 1);

        $pdf->SetY(241 - 15);
        $pdf->SetX(152);
        $pdf->Cell(0, 10, 'Miscellaneous Fee: ' . htmlspecialchars($row['miscellaneous_fee']), 0, 1);
        
        //Tuition  underline
        $pdf->SetY(247.5 - 15);
        $pdf->SetX(179); // Set this to your desired left margin
        $pdf->Cell(12, 0, '', 'T'); // The 'T' parameter draws a top border (underline)


        $pdf->SetY(246 - 15);
        $pdf->SetX(152); // Set this to your desired left margin
        $pdf->Cell(0, 10, 'Research Fee: ' . htmlspecialchars($row['research_fee'] ?? ''), 0, 1);
        
        //Research  underline
        $pdf->SetY(252  - 15);
        $pdf->SetX(173); // Set this to your desired left margin
        $pdf->Cell(20, 0, '', 'T'); // The 'T' parameter draws a top border (underline)
       
       $pdf->SetY(252 - 15);
       $pdf->SetX(152); 
        $pdf->Cell(0, 10, 'Transfer Fee: ' . htmlspecialchars($row['transfer_fee'] ?? ''), 0, 1);

        //Transfer  underline
        $pdf->SetY(258.5 - 15);
        $pdf->SetX(173); // Set this to your desired left margin
        $pdf->Cell(20, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

        $pdf->SetY(258.5 - 15);
        $pdf->SetX(152); 
        $pdf->Cell(0, 10, 'Overload: ' . htmlspecialchars($row['overload_fee'] ?? ''), 0, 1);
    
        //Transfer  underline
        $pdf->SetY(265 - 15);
        $pdf->SetX(169); // Set this to your desired left margin
        $pdf->Cell(24, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

        
        $pdf->SetY(265 - 15);
        $pdf->SetX(152); 
        $pdf->Cell(0, 10, 'Installment (DP): ' . htmlspecialchars($row['total_payment']), 0, 1);

        //Installment  underline
        $pdf->SetY(271.5 - 15);
        $pdf->SetX(174); // Set this to your desired left margin
        $pdf->Cell(19, 0, '', 'T'); // The 'T' parameter draws a top border (underline)
        // Payment Summary
        if ($row['payment_method'] === 'Installment') {
            $pdf->Cell(0, 10, 'Installment (DP): ' . htmlspecialchars($row['total_payment']), 0, 1);
        } else {
            $pdf->Cell(0, 10, '', 0, 1); // Empty line for 'Cash' payment method
        }
        $pdf->SetY(257);
        $pdf->SetX(152); // Set this to your desired left margin
        $pdf->Cell(0, 10, 'Total: ' . htmlspecialchars($row['total_payment']), 0, 1);
    
        //Total  underline
        $pdf->SetY(264);
        $pdf->SetX(161); // Set this to your desired left margin
        $pdf->Cell(32, 0, '', 'T'); // The 'T' parameter draws a top border (underline)


        //Total  underline
        $pdf->SetY(263);
        $pdf->SetX(152); // Set this to your desired left margin
        $pdf->Cell(0, 10, 'ASSESSED BY:', 0, 1);

        //ASSESSED  underline
        $pdf->SetY(269);
        $pdf->SetX(173); // Set this to your desired left margin
        $pdf->Cell(20, 0, '', 'T'); // The 'T' parameter draws a top border (underline)
    




    }


            //Total  underline
        $pdf->SetY(255);
        $pdf->SetX(5); // Set this to your desired left margin
        $pdf->Cell(0, 10, 'Students Signature', 0, 1);

        $pdf->SetY(261.5);
        $pdf->SetX(34); // Set this to your desired left margin
        $pdf->Cell(44, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

        //Total  underline
       $pdf->SetY(255);
       $pdf->SetX(80); // Set this to your desired left margin
       $pdf->Cell(0, 10, 'Evaluated by', 0, 1);

       $pdf->SetY(261.5);
       $pdf->SetX(100); // Set this to your desired left margin
       $pdf->Cell(44, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

       //Total  underline
       $pdf->SetY(262);
       $pdf->SetX(5); // Set this to your desired left margin
       $pdf->Cell(0, 10, 'Date Accomplished', 0, 1);

       $pdf->SetY(268.5);
       $pdf->SetX(34); // Set this to your desired left margin
       $pdf->Cell(44, 0, '', 'T'); // The 'T' parameter draws a top border (underline)
        //Total  underline
        $pdf->SetY(262);
        $pdf->SetX(80); // Set this to your desired left margin
        $pdf->Cell(0, 10, 'Enrolling Officer/ Registrar', 0, 1);


        $pdf->SetY(268);
        $pdf->SetX(118); // Set this to your desired left margin
        $pdf->Cell(30, 0, '', 'T'); // The 'T' parameter draws a top border (underline)

        $pdf->SetY(270);
        $pdf->SetX(5); // Set this to your desired left margin
        $pdf->Cell(0, 6, 'i certify that this student is allowed to enroll in the subject listed with corrsponding number of units', 0, 1);




































    
    
    // Output the PDF
    $pdf->Output('D', 'COR_College_def_and_Registrar_Copy.pdf');
} else {
    echo "No enrollment data found.";
}


?>

