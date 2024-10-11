<?php
require 'Payment.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        ':student_id' => $_POST['student_id'],
        ':number_of_subjects' => $_POST['number_of_subjects'],
        ':number_of_units' => $_POST['number_of_units'],
        ':amount_per_unit' => $_POST['amount_per_unit'],
        ':enrollment_fee' => $_POST['enrollment_fee'],
        ':miscellaneous_fee' => $_POST['miscellaneous_fee'],
        ':research_fee' => $_POST['research_fee'],
        ':overload_fee' => $_POST['overload_fee'],
        ':payment_method' => $_POST['payment_method']
    ];

    $payment = new Payment();

    if ($payment->create($data)) {
        echo "Payment recorded successfully!";
    } else {
        echo "Error recording payment.";
    }
}
?>
