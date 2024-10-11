<?php
session_start(); // Start the session

require '../../db/db_connection3.php'; // Ensure to include your database connection file

// Get the PDO instance from your Database class
$pdo = Database::connect();

// Initialize variables to hold input values
$units_price = '';
$miscellaneous_fee = '';
$months_of_payments = '';

// Handle form submission for insert/update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $units_price = $_POST['units_price'];
    $miscellaneous_fee = $_POST['miscellaneous_fee'];
    $months_of_payments = $_POST['months_of_payments'] ?? null; // Optional

    // Check if a row already exists
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollment_payments");
        $stmt->execute();
        $rowCount = $stmt->fetchColumn();

        if ($rowCount > 0) {
            // Update the existing row
            $sql = "UPDATE enrollment_payments SET units_price = :units_price, miscellaneous_fee = :miscellaneous_fee, months_of_payments = :months_of_payments";
            $stmt = $pdo->prepare($sql);
            $_SESSION['message'] = "Successfully updated the entry."; // Set success message
        } else {
            // Insert new row
            $sql = "INSERT INTO enrollment_payments (units_price, miscellaneous_fee, months_of_payments) VALUES (:units_price, :miscellaneous_fee, :months_of_payments)";
            $stmt = $pdo->prepare($sql);
            $_SESSION['message'] = "Successfully added the entry."; // Set success message
        }

        // Bind parameters and execute
        $stmt->bindParam(':units_price', $units_price);
        $stmt->bindParam(':miscellaneous_fee', $miscellaneous_fee);
        $stmt->bindParam(':months_of_payments', $months_of_payments);
        $stmt->execute();

        // Redirect to the same page (or another page) to prevent re-submission
        header('Location: enrollment_payments.php');
        exit(); // Ensure no further code is executed
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM enrollment_payments");
        $stmt->execute();
        $_SESSION['message'] = "Successfully deleted the entry."; // Set success message
        header('Location: enrollment_payments.php');
        exit();
    } catch (PDOException $e) {
        echo "Error deleting data: " . $e->getMessage();
    }
}

// Fetch existing data to prepopulate the form (optional)
try {
    $stmt = $pdo->prepare("SELECT * FROM enrollment_payments LIMIT 1"); // Only fetch one row
    $stmt->execute();
    $existing_data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existing_data) {
        $units_price = $existing_data['units_price'];
        $miscellaneous_fee = $existing_data['miscellaneous_fee'];
        $months_of_payments = $existing_data['months_of_payments'];
    }
} catch (PDOException $e) {
    echo "Error fetching existing data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Units Price Form</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">

    <div class="max-w-lg mx-auto mt-10 p-8 bg-white rounded-lg shadow-lg">
        <!-- Display success message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div id="success-message" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline"><?php echo $_SESSION['message']; ?></span>
                <span onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">
                    <i class="fas fa-times"></i>
                </span>
            </div>
            <?php unset($_SESSION['message']); // Clear message after displaying ?>
        <?php endif; ?>

        <!-- Units Price Form -->
        <h1 class="text-3xl font-bold text-red-800 mb-6"><i class="fas fa-money-bill-wave"></i> Units Price Form</h1>
        
        <form action="enrollment_payments.php" method="POST" class="space-y-6">
            <!-- Units Price -->
            <div>
                <label for="units_price" class="block text-sm font-medium text-red-700">
                    <i class="fas fa-dollar-sign"></i> Units Price
                </label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <i class="fas fa-coins px-3 text-red-500"></i>
                    <input type="number" step="0.01" name="units_price" id="units_price" required
                           value="<?php echo htmlspecialchars($units_price); ?>"
                           class="w-full h-10 px-3 py-2 focus:outline-none">
                </div>
            </div>

            <!-- Miscellaneous Fee -->
            <div>
                <label for="miscellaneous_fee" class="block text-sm font-medium text-red-700">
                    <i class="fas fa-receipt"></i> Miscellaneous Fee
                </label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <i class="fas fa-file-invoice-dollar px-3 text-red-500"></i>
                    <input type="number" step="0.01" name="miscellaneous_fee" id="miscellaneous_fee" required
                           value="<?php echo htmlspecialchars($miscellaneous_fee); ?>"
                           class="w-full h-10 px-3 py-2 focus:outline-none">
                </div>
            </div>

            <!-- Months of Payments -->
            <div>
                <label for="months_of_payments" class="block text-sm font-medium text-red-700">
                    <i class="fas fa-calendar-alt"></i> Months of Payments
                </label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <i class="fas fa-calendar px-3 text-red-500"></i>
                    <select name="months_of_payments" id="months_of_payments" required
                            class="w-full h-12 px-3 bg-red-50 text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 rounded-md transition duration-200">
                        <option value="">-- Select Months --</option>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($i == $months_of_payments) ? 'selected' : ''; ?>>
                                <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full bg-red-700 hover:bg-red-800 text-white font-bold py-3 px-4 rounded transition duration-200">
                <i class="fas fa-check-circle"></i> Submit
            </button>
        </form>

        <!-- Table for existing enrollment payments -->
        <div class="mt-8">
            <h2 class="text-lg font-bold text-red-700"><i class="fas fa-list"></i> Existing Enrollment Payments</h2>
            <table class="min-w-full mt-4 border border-red-300 rounded-md shadow-sm">
                <thead class="bg-red-800">
                    <tr>
                        <th class="border px-4 py-2 text-white">Units Price</th>
                        <th class="border px-4 py-2 text-white">Miscellaneous Fee</th>
                        <th class="border px-4 py-2 text-white">Months of Payments</th>
                        <th class="border px-4 py-2 text-white">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($existing_data): ?>
                        <tr class="bg-red-50">
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($existing_data['units_price']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($existing_data['miscellaneous_fee']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($existing_data['months_of_payments']); ?></td>
                            <td class="border px-4 py-2">
                                <a href="?delete=1"  onclick="return confirmDelete();" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="border text-center px-4 py-2 text-red-600">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Hide the success message after 3 seconds
        setTimeout(function() {
            var message = document.getElementById('success-message');
            if (message) {
                message.style.display = 'none';
            }
        }, 3000);

        function confirmDelete() {
        return confirm("Are you sure you want to delete this payments?");
    }
    </script>


</body>
</html>
