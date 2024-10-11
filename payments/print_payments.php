<?php
// Start the session
session_start();

// Include your database connection
require '../db/db_connection3.php';
require_once '../vendor/fpdf.php'; // Include FPDF library

// Check if the student number is set in the session
if (!isset($_SESSION['student_number'])) {
    die('Student number is not set in the session.');
}

// Initialize an array to hold the payment data
$payments = [];

try {
    // Create a new PDO connection
    $pdo = Database::connect(); // Assuming Database::connect() returns a PDO instance

    // Set PDO to throw exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

    // Check if there are any payments fetched
    if (!$payments) {
        die('No payment data found for student number: ' . $_SESSION['student_number']);
    }

} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}

// Create the PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'PAYMENTS', 0, 1, 'C');

// Set font for the payment details
$pdf->SetFont('Arial', '', 12);

foreach ($payments as $row) {
    $pdf->Ln(10); // Add some space between each record
    $pdf->SetFont('Arial', 'B', 12); // Set bold for the titles

    // Define the rectangle's position and size
    $x = 120; // Set X coordinate for the right side (adjust as needed)
    $y = $pdf->GetY();
    $width = 70; // Width of the rectangle
    $height = 120; // Height of the rectangle

    // Draw the rectangle
    $pdf->Rect($x, $y, $width, $height);

    // Set the position to start writing inside the rectangle
    $pdf->SetXY($x + 2, $y + 2); // Adjust margins inside the rectangle

    // Start displaying the payment details inside the rectangle
    $pdf->Cell(0, 10, 'Payments: ', 0, 1);
    
    // Displaying the payment details
    $pdf->Cell(0, 10, 'Payment Method: ' . htmlspecialchars($row['payment_method']), 0, 1);
    $pdf->Cell(0, 10, 'Tuition Fee (per unit): ' . htmlspecialchars($row['amount_per_unit']), 0, 1);
    $pdf->Cell(0, 10, 'Miscellaneous Fee: ' . htmlspecialchars($row['miscellaneous_fee']), 0, 1);
    
    // Displaying additional fees only if they have values
    $pdf->Cell(0, 10, 'Research Fee: ' . htmlspecialchars($row['research_fee'] ?? ''), 0, 1);
    $pdf->Cell(0, 10, 'Transfer Fee: ' . htmlspecialchars($row['transfer_fee'] ?? ''), 0, 1);
    $pdf->Cell(0, 10, 'Overload Fee: ' . htmlspecialchars($row['overload_fee'] ?? ''), 0, 1);

    // Payment Summary
    if ($row['payment_method'] === 'Installment') {
        $pdf->Cell(0, 10, 'Installment (DP): ' . htmlspecialchars($row['total_payment']), 0, 1);
    } else {
        $pdf->Cell(0, 10, '', 0, 1); // Empty line for 'Cash' payment method
    }

    $pdf->Cell(0, 10, 'Total: ' . htmlspecialchars($row['total_payment']), 0, 1);

    // Signature Section
    $pdf->Cell(0, 10, 'ASSESSED BY:', 0, 1);

    // Move to the next line after finishing the rectangle
    $pdf->Ln(5); // Add some space before the next payment block
}



// Output the PDF
$pdf->Output('I', 'payment_report.pdf'); // D for download, I for inline view
?>
