<?php
// Start the session
session_start();

// Include PHPMailer library from your specified location
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';
require '../phpmailer/src/Exception.php';

// Use PHPMailer\PHPMailer namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include your database connection file
require '../db/db_connection3.php';

// Check if the student is logged in
if (!isset($_SESSION['student_number'])) {
    die("Please log in to view payment details.");
}

// Get the student number from the session
$student_number = $_SESSION['student_number'];

// Fetch payment details and email from the database based on student_number
try {
    $db = Database::connect(); // Ensure you have the proper database connection method in your Database class

    // Fetch latest payment details
    $stmt = $db->prepare("SELECT 
        p.number_of_units, 
        p.amount_per_unit, 
        p.miscellaneous_fee, 
        p.total_payment, 
        p.payment_method, 
        p.transaction_id, 
        p.number_of_months_payment, 
        p.monthly_payment, 
        p.next_payment_due_date, 
        p.installment_down_payment,
        e.email,
        e.firstname,
        e.lastname,
        e.suffix,
        e.middlename
    FROM payments p
    JOIN enrollments e ON p.student_number = e.student_number
    WHERE p.student_number = :student_number
    ORDER BY p.created_at DESC
    LIMIT 1");  // Limit to 1 to get only the latest payment

    $stmt->execute(['student_number' => $student_number]);
    $paymentDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paymentDetails) {
        die("No payment details found for the given student number.");
    }

} catch (Exception $e) {
    die("Error fetching payment details: " . $e->getMessage());
}

// Construct full name
$fullname = ucfirst(strtolower($paymentDetails['lastname'])) . ', ' . ucfirst(strtolower($paymentDetails['firstname']));

if (!empty($paymentDetails['suffix'])) {
    $fullname .= ', ' . ucfirst(strtolower($paymentDetails['suffix']));
}
if (!empty($paymentDetails['middlename'])) {
    $fullname .= ' ' . ucfirst(strtolower($paymentDetails['middlename'])); // Removed the period
}

// Prepare the email content
$emailBody = "
<div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
    <h3 style='color: #007BFF; font-size: 24px; margin-bottom: 20px;'>Payment Details for Student Number: {$student_number}</h3>
    
    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
        <tr style='border-bottom: 1px solid #ddd;'>
            <td style='padding: 10px 0;'><strong>Name:</strong></td>
            <td style='padding: 10px 0;'>{$fullname}</td>
        </tr>
        <tr style='border-bottom: 1px solid #ddd;'>
            <td style='padding: 10px 0;'><strong>Number of Units:</strong></td>
            <td style='padding: 10px 0;'>{$paymentDetails['number_of_units']}</td>
        </tr>
        <tr style='border-bottom: 1px solid #ddd;'>
            <td style='padding: 10px 0;'><strong>Amount per Unit:</strong></td>
            <td style='padding: 10px 0;'>{$paymentDetails['amount_per_unit']}</td>
        </tr>
        <tr style='border-bottom: 1px solid #ddd;'>
            <td style='padding: 10px 0;'><strong>Miscellaneous Fee:</strong></td>
            <td style='padding: 10px 0;'>{$paymentDetails['miscellaneous_fee']}</td>
        </tr>";

        // Conditionally show "Total Payment" based on payment method
        if ($paymentDetails['payment_method'] !== 'installment') {
            $emailBody .= "
            <tr style='border-bottom: 1px solid #ddd;'>
                <td style='padding: 10px 0;'><strong>Total Payment:</strong></td>
                <td style='padding: 10px 0;'>{$paymentDetails['total_payment']}</td>
            </tr>";
        }

$emailBody .= "
        <tr style='border-bottom: 1px solid #ddd;'>
            <td style='padding: 10px 0;'><strong>Payment Method:</strong></td>
            <td style='padding: 10px 0;'>{$paymentDetails['payment_method']}</td>
        </tr>
        <tr style='border-bottom: 1px solid #ddd;'>
            <td style='padding: 10px 0;'><strong>Transaction ID:</strong></td>
            <td style='padding: 10px 0;'>{$paymentDetails['transaction_id']}</td>
        </tr>";

        // Conditionally show additional payment details based on payment method
        if ($paymentDetails['payment_method'] !== 'cash') {
            $emailBody .= "
            <tr style='border-bottom: 1px solid #ddd;'>
                <td style='padding: 10px 0;'><strong>Number of Months for Payment:</strong></td>
                <td style='padding: 10px 0;'>{$paymentDetails['number_of_months_payment']}</td>
            </tr>
            <tr style='border-bottom: 1px solid #ddd;'>
                <td style='padding: 10px 0;'><strong>Monthly Payment:</strong></td>
                <td style='padding: 10px 0;'>{$paymentDetails['monthly_payment']}</td>
            </tr>
            <tr style='border-bottom: 1px solid #ddd;'>
                <td style='padding: 10px 0;'><strong>Next Payment Due Date:</strong></td>
                <td style='padding: 10px 0;'>{$paymentDetails['next_payment_due_date']}</td>
            </tr>
            <tr style='border-bottom: 1px solid #ddd;'>
                <td style='padding: 10px 0;'><strong>Installment Down Payment:</strong></td>
                <td style='padding: 10px 0;'>{$paymentDetails['installment_down_payment']}</td>
            </tr>";
        }

$emailBody .= "
    </table>
    
    <p style='color: #555;'>Thank you for your payment. If you have any questions, please contact our support team.</p>
</div>
";



// Send the email using PHPMailer
$mail = new PHPMailer(true);


    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'pedrajetajr22@gmail.com'; // SMTP username
    $mail->Password = 'fstvwntidussfhvc'; // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('bcc@gmail.com', 'Binangonan Catholic College Payment');
    $mail->addAddress($paymentDetails['email']); // Use the email from the payment details

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Your Payment Details';
    $mail->Body = $emailBody;

    // Send the email
$mail->send()

?>
