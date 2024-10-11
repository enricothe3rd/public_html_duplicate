<?php
// Include database connection
require '../../db/db_connection3.php'; // Ensure you have your DB connection
require_once '../../vendor/fpdf.php'; // Include FPDF library
// Get the PDO instance from your Database class
$pdo = Database::connect();

// Fetch payments along with student names from the enrollment table
try {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               CONCAT(e.firstname, 
                      ' ', e.lastname, 
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments Overview</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        function filterTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toLowerCase();
            const table = document.getElementById("paymentsTable");
            const tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) { // Start from 1 to skip the header row
                const td = tr[i].getElementsByTagName("td");
                let rowContainsFilter = false;

                for (let j = 0; j < td.length; j++) {
                    if (td[j] && td[j].innerText.toLowerCase().includes(filter)) {
                        rowContainsFilter = true;
                        break;
                    }
                }
                tr[i].style.display = rowContainsFilter ? "" : "none"; // Show or hide row
            }
        }
    </script>
</head>
<body class="bg-transparent font-sans leading-normal tracking-normal">

    <div class="max-w-8xl mx-auto mt-10 p-6">
        <h1 class="text-3xl font-bold text-red-800 mb-6">
            <i class="fas fa-money-check-alt"></i> Payments Overview
        </h1>


        <button onclick="window.location.href='generate_all_payments.php'" class="bg-red-700 hover:bg-red-800 text-white font-bold py-2 px-4 rounded">
    <i class="fas fa-print"></i> Print PDF
</button>

        <!-- Search Input -->
        <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search by letter..." class="mb-4 p-2 border border-gray-300 rounded">

        <table id="paymentsTable" class="min-w-full border-collapse shadow-md overflow-hidden">
            <thead class="bg-red-800">
                <tr>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider text-white">ID</th>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider text-white">Student Name</th>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider text-white">Student Number</th>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider text-white">Number of Units</th>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider text-white">Amount per Unit</th>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider text-white">Miscellaneous Fee</th>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider text-white">Total Payment</th>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider text-white">Payment Method</th>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider text-white">Created At</th>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider text-white">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php foreach ($payments as $payment): ?>
                    <tr class="border-b bg-red-50 hover:bg-red-200">
                        <td class="border-t px-6 py-4"><?= htmlspecialchars($payment['id']) ?></td>
                        <td class="border-t px-6 py-4"><?= htmlspecialchars($payment['student_name']) ?></td>
                        <td class="border-t px-6 py-4"><?= htmlspecialchars($payment['student_number']) ?></td>
                        <td class="border-t px-6 py-4"><?= htmlspecialchars($payment['number_of_units']) ?></td>
                        <td class="border-t px-6 py-4">₱<?= htmlspecialchars($payment['amount_per_unit']) ?></td>
                        <td class="border-t px-6 py-4">₱<?= htmlspecialchars($payment['miscellaneous_fee']) ?></td>
                        <td class="border-t px-6 py-4">₱<?= htmlspecialchars($payment['total_payment']) ?></td>
                        <td class="border-t px-6 py-4"><?= htmlspecialchars($payment['payment_method']) ?></td>
                        <td class="border-t px-6 py-4"><?= htmlspecialchars($payment['created_at']) ?></td>
                        <td class="border-t px-6 py-4">
                            <a href="edit_payment.php?id=<?= htmlspecialchars($payment['id']) ?>" class="text-blue-500 hover:underline">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
