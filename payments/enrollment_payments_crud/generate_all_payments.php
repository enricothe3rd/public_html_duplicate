<?php
require '../../db/db_connection3.php'; // Ensure you have your DB connection
require_once '../../vendor/fpdf.php'; // Include FPDF library

// Get the PDO instance from your Database class
$pdo = Database::connect();

// Fetch payments along with student names from the enrollment table
try {
    $stmt = $pdo->prepare("
    SELECT p.*, 
           CONCAT(e.firstname, ' ', e.lastname, 
           CASE 
               WHEN e.suffix IS NOT NULL THEN CONCAT(', ', e.suffix)
               ELSE ''
           END) AS student_name
    FROM payments p
    LEFT JOIN enrollments e ON p.student_number = e.student_number
");


    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Could not fetch records: " . $e->getMessage());
}

// Initialize FPDF
$pdf = new FPDF();
$pdf->AddPage();

// Set the title
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Payments Overview', 0, 1, 'C');

// Add a line break
$pdf->Ln(10);




// Set the font for data rows
$pdf->SetFont('Arial', '', 10);

// Populate the table
foreach ($payments as $payment) {
    // Combine Amount/Unit and Misc. Fee into one formatted string
    $amountAndMisc = 'Amount/Unit: P ' . number_format($payment['amount_per_unit'], 2) . '    Misc. Fee: P ' . number_format($payment['miscellaneous_fee'], 2);
    
    // Add this as a single cell in one row
    $pdf->Cell(100, 10, $amountAndMisc, 1); // Adjust width if necessary
    $pdf->Ln(); // Line break after each row

    // You might want to break after the first entry if you only want one set
    break; // This ensures only the first payment is processed
}

// Set header
$pdf->SetFont('Arial', 'B', 9);

$pdf->Cell(25, 10, 'Student No.', 1);
$pdf->Cell(50, 10, 'Student Name', 1);

$pdf->Cell(10, 10, 'Units', 1);
// $pdf->Cell(30, 10, 'Amount/Unit', 1);
// $pdf->Cell(30, 10, 'Misc. Fee', 1);
$pdf->Cell(28, 10, 'Payment Method', 1); // Add payment method to the header

$pdf->Cell(25, 10, 'Total Payment', 1);
$pdf->Cell(32, 10, 'Transaction ID', 1); // Add Transaction ID to the header
$pdf->Cell(25, 10, 'Date Created', 1); // Add Created At to the header

$pdf->Ln();
// Set data font
$pdf->SetFont('Arial', '', 8);

// Populate the table
foreach ($payments as $payment) {

    $pdf->Cell(25, 10, $payment['student_number'], 1);
    $pdf->Cell(50, 10, $payment['student_name'], 1);

    $pdf->Cell(10, 10, $payment['number_of_units'], 1);
    // $pdf->Cell(30, 10, 'P ' . $payment['amount_per_unit'], 1); // Fallback to 'P'

    // $pdf->Cell(30, 10, 'P' . $payment['miscellaneous_fee'], 1);
    $pdf->Cell(28, 10, 'P' . $payment['payment_method'], 1);
    $pdf->Cell(25, 10, 'P' . $payment['total_payment'], 1);
    $pdf->Cell(32, 10, $payment['transaction_id'], 1); // Display the Transaction ID
    $pdf->Cell(25, 10, date('Y-m-d H:i', strtotime($payment['created_at'])), 1); // Display the Created At date
    $pdf->Ln();
}

// Output the PDF
$pdf->Output('D', 'Payments_Report.pdf'); // 'D' for download, 'I' for inline display
