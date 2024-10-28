<?php
session_start(); // Start the session

// Include the Database class and PHPMailer
require '../db/db_connection3.php'; // Ensure this path is correct
require_once '../vendor/fpdf.php'; // Include FPDF library

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';
require '../phpmailer/src/Exception.php';
// Get the PDO instance
$db = Database::connect(); // Use the Database class to get the PDO instance

// Retrieve student_number from session
$student_number = isset($_SESSION['student_number']) ? $_SESSION['student_number'] : null;

$successMessage = ''; // Variable to hold success message

if ($student_number) {
    try {
        // SQL query to get the sum of installment payments by student_number
        $sql = "SELECT SUM(installment_down_payment) AS total_installment
                FROM payments
                WHERE student_number = :student_number";

        $stmt1 = $db->prepare($sql);
        $stmt1->bindParam(':student_number', $student_number);
        $stmt1->execute();

        // Fetch result
        $result = $stmt1->fetch(PDO::FETCH_ASSOC);
        $total_already_payed = isset($result['total_installment']) ? $result['total_installment'] : 0;

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    // Prepare the SQL statement
    $stmt = $db->prepare("
        SELECT number_of_months_payment, monthly_payment, next_payment_due_date, installment_down_payment, miscellaneous_fee, amount_per_unit, number_of_units
        FROM payments
        WHERE student_number = :student_number
    ");
    
    // Bind the student_number parameter
    $stmt->bindParam(':student_number', $student_number, PDO::PARAM_STR);
    
    // Execute the query
    $stmt->execute();
    
    // Fetch the result as an associative array
    $paymentDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    // Number of months payment
    $number_of_months = $paymentDetails['number_of_months_payment'];

    // Month Payment
    $monthly_payment = $paymentDetails['monthly_payment'];

    // Payment Due Date
    $payment_due_date = $paymentDetails['next_payment_due_date']; 

    // Calculate the Total Installment 
    $total_installment = $number_of_months * $monthly_payment;
    
    // Calculate next payment due date
    $next_payment_due_date = date('Y-m-d', strtotime($paymentDetails['next_payment_due_date'] . ' +1 month'));// +1 month

    // Calculate that will deduct
    $remaining_balance = $total_installment - $total_already_payed;

} else {
    echo "Student number is not set in the session.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $student_email = null;  // Initialize the variable

    if ($student_number) {
        // Prepare the SQL statement to fetch email, firstname, lastname, middlename, and suffix
        $stmt = $db->prepare("SELECT email, firstname, lastname, middlename, suffix FROM enrollments WHERE student_number = :student_number");
        
        // Bind the parameter
        $stmt->bindParam(':student_number', $student_number);
        
        // Execute the statement
        $stmt->execute();
    
        // Check if any rows were returned
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $student_email = $row['email'];
    
            // Now you can use these variables in your application
        } else {
            echo "No student found with this student number.";
        }
    } else {
        echo "Student number not found in session.";
    }
    
    $fullname = ucfirst(strtolower($row['lastname'])) . ', ' . ucfirst(strtolower($row['firstname']));

if (!empty($row['suffix'])) {
    $fullname .= ', ' . ucfirst(strtolower($row['suffix']));
}
if (!empty($row['middlename'])) {
    $fullname .= '., ' . ucfirst(strtolower($row['middlename']));
}


    $student_monthly_payment = $_POST['total_amount1']; // Corrected from 'monthly_payment'
    $transaction_id = $_POST['transaction_id']; // Get transaction ID from hidden input
    $amount_per_unit = $paymentDetails['amount_per_unit']; // Number of amount_per_unit
    $miscellaneous_fee = $paymentDetails['miscellaneous_fee']; // Number of miscellaneous_fee
    $number_of_units = $paymentDetails['number_of_units']; // Number of number_of_units
    // $student_email = 'student@example.com'; // Fetch the email from your database if it's stored

    try {
        // Assuming you have variables for $installment_down_payment and $next_payment_due_date
        $installment_down_payment = $student_monthly_payment; // Adjust this as per your logic
        // Add one month to the current due date
        $current_due_date = new DateTime($payment_due_date);
        $next_payment_due_date = $current_due_date->modify('+1 month')->format('Y-m-d');

        // Insert new payment record
        $insertStmt = $db->prepare("INSERT INTO payments (student_number, transaction_id, installment_down_payment, next_payment_due_date, payment_status, amount_per_unit, miscellaneous_fee, payment_method, number_of_units) 
                                      VALUES (:student_number, :transaction_id, :installment_down_payment, :next_payment_due_date, 'completed', :amount_per_unit, :miscellaneous_fee, 'installment', :number_of_units)");
        $insertStmt->execute([
            ':student_number' => $_SESSION['student_number'],
            ':transaction_id' => $transaction_id,
            ':installment_down_payment' => $installment_down_payment,
            ':next_payment_due_date' => $next_payment_due_date,
            ':amount_per_unit' => $amount_per_unit,
            ':miscellaneous_fee' => $miscellaneous_fee,
            ':number_of_units' => $number_of_units,
        ]);

        // Set the success message
        $successMessage = "Payment processed successfully! Transaction ID: <strong>$transaction_id</strong>. Next payment due date is <strong>" . $next_payment_due_date . "</strong>.";

        // Store necessary data for PDF generation in the session or as JSON for JavaScript
        $_SESSION['pdf_data'] = [
            'student_number' => $student_number,
            'transaction_id' => $transaction_id,
            'installment_down_payment' => $installment_down_payment,
            'next_payment_due_date' => $next_payment_due_date,
            'total_already_paid' => $total_already_payed,
        ];

        // Send confirmation email
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'pedrajetajr22@gmail.com'; // SMTP username
            $mail->Password = 'fstvwntidussfhvc'; // SMTP password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            //Recipients
            $mail->setFrom('bcc@gmail.com', 'Binangonan Catholic College Payment');
            $mail->addAddress($student_email); // Add the recipient's email
            $mail->addReplyTo('no-reply@example.com', 'No Reply');

            //Content
            $mail->isHTML(true); 
            $mail->Subject = 'Payment Confirmation';
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                <h1 style='color: #007BFF; font-size: 24px; margin-bottom: 20px;'>Payment Confirmation</h1>
                <p style='margin-bottom: 15px;'>Hi,</p>
                <p style='margin-bottom: 15px;'>We’ve successfully received your payment. Thank you for choosing us!</p>
                
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                    <tr style='border-bottom: 1px solid #ddd;'>
                        <td style='padding: 10px 0;'><strong>Name:</strong></td>
                        <td style='padding: 10px 0;'>{$fullname}</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #ddd;'>
                        <td style='padding: 10px 0;'><strong>Transaction ID:</strong></td>
                        <td style='padding: 10px 0;'>{$transaction_id}</td>
                    </tr>
                    <tr style='border-bottom: 1px solid #ddd;'>
                        <td style='padding: 10px 0;'><strong>Amount Paid:</strong></td>
                        <td style='padding: 10px 0;'>{$student_monthly_payment}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px 0;'><strong>Next Payment Due Date:</strong></td>
                        <td style='padding: 10px 0;'>{$next_payment_due_date}</td>
                    </tr>
                </table>
            
                <p style='color: #555; margin-bottom: 15px;'>If you have any questions, feel free to reach out. Keep this email for your records.</p>
                <p style='margin-bottom: 0;'>Best regards,<br>BCC Team</p>
            </div>
            ";
            
        

            $mail->send();
            // echo 'Payment confirmation email sent.';

        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    } catch (Exception $e) {
        echo "Error processing payment: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://www.paypal.com/sdk/js?client-id=AeTnJCEfQ0MJolcWHGQSC8kwaMioTs_jWRC1mOJ05nqsy2zJe7ou1LvYQ88-EMm1vIIjImwRKvULNCT-&currency=PHP"></script>
    <style>
    body {
        font-family: Arial, sans-serif;
        display: flex; /* Use Flexbox to center the content */
        justify-content: center; /* Center horizontally */
        align-items: center; /* Center vertically */
        height: 120vh; /* Use full viewport height */
        margin: 0; /* Remove default margin */
        width: 100%;
    }
    /* Other styles remain unchanged */
    label {
        margin-top: 10px;
        display: block;
    }
    input {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        margin-bottom: 15px;
    }
    button {
        padding: 10px;
        background-color: #0070ba;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
    }
</style>
<script>
        document.addEventListener("DOMContentLoaded", function() {
            const successMessage = document.getElementById("success-message");
            if (successMessage) {
                setTimeout(() => {
                    successMessage.classList.add("opacity-0"); // Start fade-out effect
                    setTimeout(() => {
                        successMessage.style.display = "none"; // Hide the message after fade-out
                        // Generate PDF by navigating to the PDF generation endpoint
                        window.location.href = 'repayment_generate_pdf.php'; // Update the path as needed
                    }, 500); // Delay to match the fade-out transition
                }, 3000); // Time in milliseconds before the message starts fading out (3 seconds)
            }
        });
    </script>


</head>
<body class="">
    <form id="payment-form" method="post" action="">
        <input type="hidden" id="hidden_total" name="total_amount1" value="0.00">
        <div class=" p-6 rounded-lg shadow-md max-w-lg mx-auto">
            <!-- Payment Details --><br><br><br>
            <div class="bg-white p-6 rounded-lg shadow-lg mb-6 max-w-md mx-auto">

            <?php if ($successMessage): ?>
    <div id="success-message" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline"><?php echo $successMessage; ?></span>
    </div>
<?php endif; ?>

    <p class="text-lg font-semibold text-gray-700">
        Number of Installments: 
        <span class="font-bold text-blue-600" id="installment-count"></span>
    </p>
    
    <p class="text-lg font-semibold text-gray-700 mt-4">
        Total Tuition: 
        <span class="font-bold text-green-500">₱<span id="total-amount"><?php echo htmlspecialchars($total_installment); ?></span></span>
    </p>

    <p class="text-lg font-semibold text-gray-700 mt-4">
        Amount Already Paid: 
        <span class="font-bold text-green-500">₱<span id="total_already_payed"><?php echo htmlspecialchars($total_already_payed); ?></span></span>
    </p>

    <p class="text-lg font-semibold text-gray-700 mt-4">
        Remaining Balance: 
        <span class="font-bold text-red-500">₱<span id="remaining-balance"><?php echo htmlspecialchars($remaining_balance); ?></span></span>
    </p>
</div>

            <?php
// Check if remaining balance and number of months are set
if (isset($remaining_balance) && isset($number_of_months) && $number_of_months > 0) {
    // Calculate the dynamic monthly payment based on remaining balance and number of months
    if ($remaining_balance > 0) {
        $monthly_payment = $remaining_balance / $number_of_months;

        // Calculate the maximum months based on the remaining balance
        $max_months = floor($remaining_balance / $monthly_payment);
        
        // Loop to render checkboxes only for the available months
        for ($i = 1; $i <= $number_of_months; $i++) {
            if ($i <= $max_months) {
?>
         <div class="flex items-center mb-4 p-4 bg-white border border-gray-200 rounded-lg shadow-md hover:bg-gray-50 transition duration-200 ease-in-out">
    <input type="checkbox" name="month_payment[]" value="<?= $monthly_payment ?>" 
           class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
           id="month-<?= $i ?>" onchange="updateTotal(this); toggleCustomAmount()">
    
    <label class="ml-4 text-gray-800 text-base" for="month-<?= $i ?>">
        Month <?= $i ?> - Payment: 
        <span class="font-semibold text-green-600">₱<?= number_format($monthly_payment, 2) ?></span>
    </label>
</div>

<?php
            }
        }
    } else {
        // User-friendly message for no remaining balance
        echo "<p class='text-red-600'>You have already paid the full tuition. No further payments are required.</p>";
    }
} else {
    // User-friendly message for invalid input
    echo "<p class='text-red-600'>Invalid input. Please check your payment details and try again.</p>";
}
?>


<!-- Custom Amount Input -->
<div class="mb-6 <?php echo ($remaining_balance <= 0) ? 'hidden' : ''; ?>">
    <label for="custom_amount" class="block text-gray-700 text-sm font-medium mb-2">
        Custom Amount:
    </label>
    <input type="number" id="custom_amount" name="custom_amount" placeholder="Enter custom amount" min="0" 
           class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
           oninput="updateCustomAmount(this)">
    
    <p class="text-lg font-semibold text-gray-800 mt-4">
        Amount Selected: 
        <span class="font-bold text-green-600">₱<span id="total-amount1">0.00</span></span>
    </p>
</div>




<script>
function toggleCustomAmount() {
    // Get all checkboxes
    const checkboxes = document.querySelectorAll('input[name="month_payment[]"]');
    const customAmountInput = document.getElementById('custom_amount');

    // Check if any checkbox is checked
    const isAnyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);

    // Enable or disable the custom amount input based on checkbox state
    customAmountInput.disabled = isAnyChecked;
    
    // Optionally clear the custom amount if it's disabled
    if (isAnyChecked) {
        customAmountInput.value = '';
    }
}
</script>


<script>
    // Display the number of installments allowed based on remaining balance
    document.getElementById('installment-count').innerText = Math.floor(parseFloat(document.getElementById('remaining-balance').innerText) / parseFloat(<?php echo json_encode($monthly_payment); ?>));

    function updateTotal(checkbox) {
        const totalAmountElement = document.getElementById('total-amount1');
        const hiddenTotalInput = document.getElementById('hidden_total');
        const remainingBalanceElement = document.getElementById('remaining-balance');
        const paypalButtonContainer = document.getElementById('paypal-button-container'); // Get the PayPal button container

        let currentTotal = parseFloat(totalAmountElement.innerText) || 0;
        const remainingBalance = parseFloat(remainingBalanceElement.innerText) || 0;
        const monthlyPayment = parseFloat(checkbox.value) || 0;

        if (checkbox.checked) {
            if (currentTotal + monthlyPayment > remainingBalance) {
                alert("You cannot select more than the remaining balance.");
                checkbox.checked = false;
                return;
            }
            currentTotal += monthlyPayment;
        } else {
            currentTotal -= monthlyPayment;
        }

        // Update the total display and hidden input
        totalAmountElement.innerText = currentTotal.toFixed(2);
        hiddenTotalInput.value = currentTotal.toFixed(2);

        // Debugging
        console.log(`Current Total: ${currentTotal.toFixed(2)}`);
        console.log(`Paypal Button should be ${currentTotal === 0 ? 'hidden' : 'visible'}`);

        // Hide or show PayPal button based on total amount
        paypalButtonContainer.style.display = currentTotal === 0 ? 'none' : 'block';
    }

    function updateCustomAmount(input) {
        const totalAmountElement = document.getElementById('total-amount1');
        const hiddenTotalInput = document.getElementById('hidden_total');
        const remainingBalanceElement = document.getElementById('remaining-balance');
        const paypalButtonContainer = document.getElementById('paypal-button-container'); // Get the PayPal button container

        const customAmount = parseFloat(input.value) || 0;
        const remainingBalance = parseFloat(remainingBalanceElement.innerText) || 0;

        if (customAmount > remainingBalance) {
            alert("Custom amount cannot exceed the remaining balance.");
            input.value = ''; // Clear the input if overpaid
            totalAmountElement.innerText = '0.00';
            hiddenTotalInput.value = '0.00';
            // Hide PayPal button when custom amount is cleared
            paypalButtonContainer.style.display = 'none';
            return;
        }

        // Reset checkboxes when a custom amount is entered
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });

        // Update total with custom amount
        totalAmountElement.innerText = customAmount.toFixed(2);
        hiddenTotalInput.value = customAmount.toFixed(2);

        // Debugging
        console.log(`Custom Amount: ${customAmount.toFixed(2)}`);
        console.log(`Paypal Button should be ${customAmount === 0 ? 'hidden' : 'visible'}`);

        // Show or hide PayPal button based on custom amount
        paypalButtonContainer.style.display = customAmount === 0 ? 'none' : 'block';
    }
</script>


        <input type="hidden" id="transaction_id" name="transaction_id">
        
        <div id="paypal-button-container" class="hidden<?php echo ($remaining_balance <= 0) ? 'hidden' : ''; ?>"></div>
    </form>

    <script>
        paypal.Buttons({
            createOrder: function(data, actions) {
                const amount = document.getElementById('total-amount1').innerText; // Use updated amount
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: amount // Use the calculated amount
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    document.getElementById('transaction_id').value = details.id; // Set transaction ID in hidden input
                    document.getElementById('payment-form').submit(); // Submit form after capturing the order
                });
            }
        }).render('#paypal-button-container'); // Display PayPal button
    </script>
</body>
</html>
