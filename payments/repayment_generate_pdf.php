<?php
session_start(); // Start the session
require_once '../vendor/fpdf.php'; // Include FPDF library

// Retrieve data from session
$pdfData = $_SESSION['pdf_data'] ?? null;

if ($pdfData) {
    $pdf = new FPDF();
    $pdf->AddPage();

    // Add a logo or icon at the top
    $pdf->Image('../assets/images/school-logo/bcc-icon.png', 10, 8, 33); // Adjust path, x, y, and width as needed
    $pdf->Ln(20); // Add space below the logo

    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Payment Receipt', 0, 1, 'C'); // Centered title
    $pdf->Ln(10);

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, "Student Number: " . $pdfData['student_number']);
    $pdf->Ln(10);
    $pdf->Cell(40, 10, "Transaction ID: " . $pdfData['transaction_id']);
    $pdf->Ln(10);
    $pdf->Cell(40, 10, "Amount Paid: " . $pdfData['installment_down_payment']);
    $pdf->Ln(10);
    $pdf->Cell(40, 10, "Next Payment Due Date: " . $pdfData['next_payment_due_date']);
    $pdf->Ln(10);
    $pdf->Cell(40, 10, "Total Already Paid: " . $pdfData['total_already_paid']);
    $pdf->Ln(10);

    $pdf->Output('D', 'receipt_' . $pdfData['transaction_id'] . '.pdf'); // Download the PDF
}
?>
