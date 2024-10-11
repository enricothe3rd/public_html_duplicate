<?php
// Include database connection
require '../../db/db_connection3.php'; // Ensure you have your DB connection

// Get the PDO instance from your Database class
$pdo = Database::connect();

// Check if the ID is set in the URL and fetch the payment record
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the payment record based on the ID
    try {
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the payment exists
        if (!$payment) {
            die("Payment not found.");
        }
    } catch (PDOException $e) {
        die("Could not fetch payment: " . $e->getMessage());
    }
} else {
    die("Invalid request.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $total_payment = $_POST['total_payment'];
    $payment_method = $_POST['payment_method'];

    try {
        // Update the payment record
        $stmt = $pdo->prepare("UPDATE payments SET total_payment = :total_payment, payment_method = :payment_method WHERE id = :id");
        $stmt->bindParam(':total_payment', $total_payment);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Redirect after successful update
        header("Location: view_all_payments.php");
        exit();
    } catch (PDOException $e) {
        die("Could not update payment: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 p-6">

    <div class="max-w-md mx-auto p-8 bg-white rounded-lg shadow-lg">
        <button 
            onclick="goBack()" 
            class="mb-4 px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition duration-200 flex items-center"
        >
            <i class="fas fa-arrow-left mr-2"></i> <!-- Arrow icon -->
            Back
        </button>
        <h1 class="text-2xl font-bold text-red-800 mb-6">Edit Payment</h1>

        <form method="POST" action="">

            <div class="mb-4">
                <label for="total_payment" class="block text-sm font-medium text-red-700">Total Payment</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                        <i class="fas fa-dollar-sign text-red-500 px-3"></i>
                    <input type="text" name="total_payment" id="total_payment" class="w-full h-10 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" value="<?= htmlspecialchars($payment['total_payment']) ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="payment_method" class="block text-sm font-medium text-red-700">Payment Method</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                        <i class="fas fa-credit-card text-red-500 px-3"></i>
                    <select name="payment_method" id="payment_method" class="w-full h-10 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" required>
                        <option value="cash" <?= $payment['payment_method'] === 'cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="installment" <?= $payment['payment_method'] === 'installment' ? 'selected' : '' ?>>Installment</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-between">
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 flex items-center">
                    <i class="fas fa-save mr-2"></i> Update Payment
                </button>
                <!-- <a href="view_payments.php" class="text-blue-500 hover:underline flex items-center">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a> -->
            </div>
        </form>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>

