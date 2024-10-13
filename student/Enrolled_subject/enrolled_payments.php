<?php
session_start();
require '../../db/db_connection3.php';

// Establish database connection
$pdo = Database::connect();

// Initialize variables
$payments = [];
$student_number = '';

// Check if the session variable for student_number is set
if (isset($_SESSION['student_number'])) {
    $student_number = $_SESSION['student_number'];

    $stmt = $pdo->prepare( "
        SELECT id,                         
            number_of_units,
            amount_per_unit,
            miscellaneous_fee,
            installment_down_payment,
            total_payment,       
            payment_method,
            transaction_id,
            created_at,
            student_number
        FROM payments
        WHERE student_number = :student_number;
    ");

    // Bind the student_number parameter
    $stmt->bindParam(':student_number', $student_number, PDO::PARAM_STR);

    // Execute the query
    if (!$stmt->execute()) {
        die("Error executing query: " . implode(", ", $stmt->errorInfo()));
    }

    // Fetch all payment records as an associative array
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($payments) > 0) {
        $payment_method = isset($payments[0]['payment_method']) ? $payments[0]['payment_method'] : 'Not set';
   
    } else {
        echo "<p>No payment record found for student.</p>";
    }
} else {
    echo "<p class='text-red-500'>Session variable for student number is not set. Please log in.</p>";
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Records</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <h1 class="text-2xl font-bold mb-4 text-center  text-red-800">Payment Records</h1>

    <?php if (!empty($payments)): ?>
        <div class="overflow-x-auto">
            <table class="hidden min-w-full bg-white shadow-md rounded-lg sm:table"> <!-- Hidden on small devices -->
                <thead class="bg-gray-200">
                    <tr class="bg-red-800">
                        <th class="px-4 py-4 border-b text-left text-white">Student Number</th>
                        <th class="px-4 py-4 border-b text-left text-white">Number of Units</th>
                        <th class="px-4 py-4 border-b text-left text-white">Amount per Unit</th>
                        <th class="px-4 py-4 border-b text-left text-white">Miscellaneous Fee</th>
                        <?php if (trim($payment_method) === 'installment'): ?>
                            <th class="px-4 py-4 border-b text-left text-white">Installment Payment</th>
                        <?php endif; ?>
                        <?php if (trim($payment_method) === 'cash'): ?>
                            <th class="px-4 py-4 border-b text-left text-white">Total Payment</th>
                        <?php endif; ?>
                        <th class="px-4 py-4 border-b text-left text-white">Payment Method</th>
                        <th class="px-4 py-4 border-b text-left text-white">Transaction ID</th>
                        <th class="px-4 py-4 border-b text-left text-white">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr class="border-b bg-red-50 hover:bg-red-200">
                            <td class="border-t px-6 py-3"><?php echo htmlspecialchars($payment['student_number']); ?></td>
                            <td class="border-t px-6 py-3"><?php echo htmlspecialchars($payment['number_of_units']); ?></td>
                            <td class="border-t px-6 py-3"><?php echo htmlspecialchars($payment['amount_per_unit']); ?></td>
                            <td class="border-t px-6 py-3"><?php echo htmlspecialchars($payment['miscellaneous_fee']); ?></td>
                            <?php if (trim($payment_method) === 'installment'): ?>
                                <td class="border-t px-6 py-3"><?php echo htmlspecialchars($payment['installment_down_payment']); ?></td>
                            <?php endif; ?>
                            <?php if (trim($payment_method) === 'cash'): ?>
                                <td class="border-t px-6 py-3"><?php echo htmlspecialchars($payment['total_payment']); ?></td>
                            <?php endif; ?>
                            <td class="border-t px-6 py-3"><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                            <td class="border-t px-6 py-3"><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                            <td class="border-t px-6 py-3"><?php echo htmlspecialchars($payment['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="block sm:hidden"> <!-- Visible only on small devices -->
                <?php foreach ($payments as $payment): ?>
                    <div class="bg-white shadow-md rounded-lg mb-4 p-4">
                        <div class="flex flex-col">
                            <div class="flex justify-between">
                                <span class="font-bold  text-red-800">Student Number:</span>
                                <span><?php echo htmlspecialchars($payment['student_number']); ?></span>
                            </div>
                            <div class="flex justify-between  ">
                                <span class="font-bold text-red-800">Number of Units:</span>
                                <span><?php echo htmlspecialchars($payment['number_of_units']); ?></span>
                            </div>
                            <div class="flex justify-between  ">
                                <span class="font-bold text-red-800">Amount per Unit:</span>
                                <span><?php echo htmlspecialchars($payment['amount_per_unit']); ?></span>
                            </div>
                            <div class="flex justify-between  ">
                                <span class="font-bold text-red-800">Miscellaneous Fee:</span>
                                <span><?php echo htmlspecialchars($payment['miscellaneous_fee']); ?></span>
                            </div>
                            <?php if (trim($payment_method) === 'installment'): ?>
                                <div class="flex justify-between  ">
                                    <span class="font-bold text-red-800">Installment Payment:</span>
                                    <span><?php echo htmlspecialchars($payment['installment_down_payment']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (trim($payment_method) === 'cash'): ?>
                                <div class="flex justify-between  ">
                                    <span class="font-bold text-red-800">Total Payment:</span>
                                    <span><?php echo htmlspecialchars($payment['total_payment']); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="flex justify-between  ">
                                <span class="font-bold text-red-800">Payment Method:</span>
                                <span><?php echo htmlspecialchars($payment['payment_method']); ?></span>
                            </div>
                            <div class="flex justify-between  ">
                                <span class="font-bold text-red-800">Transaction ID:</span>
                                <span><?php echo htmlspecialchars($payment['transaction_id']); ?></span>
                            </div>
                            <div class="flex justify-between  ">
                                <span class="font-bold text-red-800 ">Created At:</span>
                                <span><?php echo htmlspecialchars($payment['created_at']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <p class="text-center text-red-500">No payment records found for the provided student number.</p>
    <?php endif; ?>
</body>
</html>
