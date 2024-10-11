<?php
require 'Payment.php';

header('Content-Type: application/json');

$payment = new Payment();
$data = json_decode(file_get_contents('php://input'), true);

// Check if the required data is present
if (!isset($data['student_number'], $data['amount_per_unit'], $data['miscellaneous_fee'], $data['payment_method'], $data['number_of_units'], $data['number_of_months_payment'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

// Calculate total payment
$totalPayment = ($data['amount_per_unit'] * $data['number_of_units']) + $data['miscellaneous_fee'];

// Calculate monthly payment
$monthlyPayment = $totalPayment / $data['number_of_months_payment'];

// Determine the next payment due date
$nextPaymentDate = date('Y-m-d', strtotime('+1 month')); // Set to one month from now

// Prepare data for insertion
$paymentData = [
    'student_number' => $data['student_number'],
    'number_of_units' => $data['number_of_units'],
    'amount_per_unit' => $data['amount_per_unit'],
    'miscellaneous_fee' => $data['miscellaneous_fee'],
    'total_payment' => $totalPayment,
    'payment_method' => $data['payment_method'],
    'transaction_id' => $data['transaction_id'] ?? null, // Optional field
    'number_of_months_payment' => $data['number_of_months_payment'],
    'monthly_payment' => $monthlyPayment, // New column
    'next_payment_due_date' => $nextPaymentDate, // New column
    'installment_down_payment' => $data['installment_down_payment'],
];

// Insert payment into the database
if ($payment->create($paymentData)) {
    echo json_encode(['success' => true, 'message' => 'Payment recorded successfully.']);
} else {
    error_log("Failed to insert payment data: " . print_r($paymentData, true));
    echo json_encode(['success' => false, 'message' => 'Failed to record payment.']);
}
?>
