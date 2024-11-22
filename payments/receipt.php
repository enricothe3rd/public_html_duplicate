<?php
session_start();
require '../db/db_connection3.php'; // Your database connection

// Check if student number is set
if (!isset($_SESSION['student_number'])) {
    echo "<div class='text-red-500'>Student number not found.</div>";
    exit;
}

$pdo = Database::connect();
$student_number = $_SESSION['student_number'];
$query = $pdo->prepare("SELECT * FROM payments WHERE student_number = :student_number ORDER BY created_at DESC LIMIT 1");
$query->execute(['student_number' => $student_number]);
$paymentDetails = $query->fetch(PDO::FETCH_ASSOC);

if (!$paymentDetails) {
    echo "<div class='text-red-500'>No payment details found for this student.</div>";
    exit;
}

// Get payment method
$payment_method = $paymentDetails['payment_method'];

$getFullName = $pdo->prepare("SELECT * FROM enrollments WHERE student_number = :student_number");
$getFullName->execute(['student_number' => $student_number]);
$student_details = $getFullName->fetch(PDO::FETCH_ASSOC);

$fullname = ucfirst(strtoupper($student_details['lastname'])) . ", " . ucfirst(strtoupper($student_details['firstname'])) . ", " . ucfirst(strtoupper($student_details['suffix']));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .receipt-header {
            border-bottom: 2px solid #0070ba; /* PayPal blue */
        }
        .receipt-footer {
            border-top: 2px solid #0070ba; /* PayPal blue */
        }
        .button {
            transition: background-color 0.3s, transform 0.2s;
        }
        .button:hover {
            background-color: #005999; /* Darker shade for hover */
            transform: scale(1.05); /* Slight zoom effect */
        }
        .rounded-button {
            border-radius: 12px; /* Rounded corners */
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg">
        <div class="text-center receipt-header pb-4">
            <img src="../assets/images/school-logo/bcc-icon.png" alt="School Logo" class="w-16 h-16 mx-auto">
            <h1 class="text-3xl font-bold mt-2 text-gray-800">Payment Receipt</h1>
        </div>

        <div class="my-4">
        <p class="text-lg font-semibold text-gray-700">Student Name: <span class="font-normal text-gray-600"><?php echo htmlspecialchars($fullname); ?></span></p>
            <p class="text-lg font-semibold text-gray-700">Student Number: <span class="font-normal text-gray-600"><?php echo htmlspecialchars($paymentDetails['student_number']); ?></span></p>
            <p class="text-lg font-semibold text-gray-700">Transaction ID: <span class="font-normal text-gray-600"><?php echo htmlspecialchars($paymentDetails['transaction_id']); ?></span></p>
            <p class="text-lg font-semibold text-gray-700">Payment Method <span class="font-normal text-gray-600"><?php echo htmlspecialchars(ucfirst(strtolower($paymentDetails['payment_method']))); ?></span></p>

            <?php if (trim($payment_method) === 'cash'): ?>
            <p class="text-lg font-semibold text-gray-700">Amount Paid: <span class="font-normal text-gray-600">₱<?php echo htmlspecialchars(number_format($paymentDetails['total_payment'], 2)); ?> PHP</span></p>
            <?php endif; ?>
            <?php if (trim($payment_method) === 'installment'): ?>
            <p class="text-lg font-semibold text-gray-700">Installment Downpayment: <span class="font-normal text-gray-600">₱<?php echo htmlspecialchars(number_format($paymentDetails['installment_down_payment'], 2)); ?> PHP</span></p>
            <?php endif; ?>
            <p class="text-lg font-semibold text-gray-700">Date: <span class="font-normal text-gray-600"><?php echo htmlspecialchars(date("F j, Y", strtotime($paymentDetails['created_at']))); ?></span></p>
        </div>

        <div class="text-center receipt-footer py-4">
            <p class="italic text-gray-600">Thank you for your payment!</p>
        </div>

        <div class="flex justify-center space-x-4 mt-4">
            <a href="receipt_print.php" class="bg-blue-500 text-white font-semibold py-2 px-6 rounded-button button">
                Print Receipt
            </a>

<a id="printCorBtn" class="bg-blue-500 text-white font-semibold py-2 px-6 rounded-button button">
    Print COR
</a>

<!-- Modal Structure -->
<div id="myModal2" class="fixed inset-0 bg-gray-800 bg-opacity-70 hidden flex justify-center items-center">
    <div class="bg-white rounded-lg shadow-xl p-8 w-11/12 md:w-96">
        <h2 class="text-2xl font-semibold mb-4 text-gray-800">Select COR to Print</h2>
        <p class="mb-6 text-gray-600">Please choose one of the following options:</p>
        <div class="flex flex-col space-y-4">
            <button id="cor1Btn" class="bg-green-500 text-white font-semibold py-3 px-4 rounded-lg shadow hover:bg-green-600 transition duration-200">COllege Dep.t & Registrar Copy</button>
            <button id="cor2Btn" class="bg-blue-500 text-white font-semibold py-3 px-4 rounded-lg shadow hover:bg-blue-600 transition duration-200">Student & Cashier Copy</button>
        </div>
        <div class="flex justify-between mt-6">
            <button id="cancelBtn1" class="bg-red-500 text-white font-semibold py-2 px-4 rounded-lg shadow hover:bg-red-600 transition duration-200">Cancel</button>
            <button id="cancelBtn2" class="bg-red-500 text-white font-semibold py-2 px-4 rounded-lg shadow hover:bg-red-600 transition duration-200">Close</button>
        </div>
    </div>
</div>

<script>
    const printCorBtn = document.getElementById('printCorBtn');
    const modal1 = document.getElementById('myModal2');
    const cancelBtn1 = document.getElementById('cancelBtn1');
    const cancelBtn2 = document.getElementById('cancelBtn2');
    const cor1Btn = document.getElementById('cor1Btn');
    const cor2Btn = document.getElementById('cor2Btn');

    // Open modal when the button is clicked
    printCorBtn.onclick = function() {
        modal1.classList.remove('hidden');
    };

    // Close modal when the cancel button is clicked
    cancelBtn1.onclick = function() {
        modal1.classList.add('hidden');
    };

// Close modal when the cancel button is clicked
    cancelBtn2.onclick = function() {
        modal1.classList.add('hidden');
    };
    // Handle COR Option 1 action
    cor1Btn.onclick = function() {
        window.location.href = 'print_cor.php'; // Redirect to print_cor.php
    };

    // Handle COR Option 2 action
    cor2Btn.onclick = function() {
        window.location.href = 'print_cor1.php'; // Redirect to print_cor1.php
    };

    // Close modal if user clicks outside of the modal
    window.onclick = function(event) {
        if (event.target === modal1) {
            modal1.classList.add('hidden');
        }
    };
</script>
            <!-- Button to navigate to the dashboard -->
<button onclick="navigateToDashboard()" class="bg-green-500 text-white font-semibold py-2 px-6 rounded-button button">
    Go to Dashboard
</button>

<script>
    function navigateToDashboard() {
        // Fetch and include the spinner when the button is clicked
        fetch('../loading_spinner.php')
            .then(response => response.text())
            .then(data => {
                // Create a temporary div to hold the spinner HTML
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data;

                // Append the spinner HTML to the body
                document.body.appendChild(tempDiv);

                // Show the spinner
                document.getElementById('loading-spinner').style.display = 'flex';

                // Optional: Add a delay before navigating
                setTimeout(() => {
                    window.top.location.href = '../student_dashboard.php';
                }, 500); // Adjust the delay as needed
            })
            .catch(error => console.error('Error loading spinner:', error));
    }
</script>




        </div>
    </div>
</body>
</html>

