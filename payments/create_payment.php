<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Payment</title>
    <link rel="stylesheet" href="path/to/tailwind.css"> <!-- Adjust the path as needed -->
    <script>
        function toggleFields() {
            var paymentType = document.querySelector('select[name="payment_type"]').value;
            if (paymentType === 'installment') {
                document.getElementById('installment_fields').style.display = 'block';
                document.getElementById('bank_number').style.display = 'block';
            } else {
                document.getElementById('installment_fields').style.display = 'none';
                document.getElementById('bank_number').style.display = 'none';
            }
        }

        function toggleInstallmentType() {
            var installmentType = document.querySelector('select[name="installment_type"]').value;
            var monthlyFields = document.getElementById('monthly_fields');
            var quarterlyFields = document.getElementById('quarterly_fields');
            var flexibleFields = document.getElementById('flexible_fields');
            monthlyFields.style.display = 'none';
            quarterlyFields.style.display = 'none';
            flexibleFields.style.display = 'none';

            if (installmentType === 'monthly') {
                monthlyFields.style.display = 'block';
            } else if (installmentType === 'quarterly') {
                quarterlyFields.style.display = 'block';
            } else if (installmentType === 'flexible') {
                flexibleFields.style.display = 'block';
            }
        }
    </script>
</head>
<body>
    <div class="container mx-auto p-4">
        <h2 class="text-xl font-bold mb-4">Create Payment</h2>
        <form action="process_payment.php" method="POST">
            <label class="block mb-2">Student ID:</label>
            <input type="text" name="student_id" required class="border p-2 mb-4 w-full"><br>

            <label class="block mb-2">Subject ID:</label>
            <input type="text" name="subject_id" required class="border p-2 mb-4 w-full"><br>

            <label class="block mb-2">Subject Price:</label>
            <input type="text" name="subject_price" class="border p-2 mb-4 w-full" readonly><br>

            <label class="block mb-2">Payment Type:</label>
            <select name="payment_type" onchange="toggleFields()" class="border p-2 mb-4 w-full">
                <option value="cash">Cash</option>
                <option value="installment">Installment</option>
            </select><br>

            <label class="block mb-2">Amount:</label>
            <input type="text" name="amount_paid" required class="border p-2 mb-4 w-full"><br>

            <label class="block mb-2">Miscellaneous Fee:</label>
            <input type="text" name="miscellaneous_fee" class="border p-2 mb-4 w-full"><br>

            <label class="block mb-2">Research Fee:</label>
            <input type="text" name="research_fee" class="border p-2 mb-4 w-full"><br>

            <label class="block mb-2">Transfer Fee:</label>
            <input type="text" name="transfer_fee" class="border p-2 mb-4 w-full"><br>

            <label class="block mb-2">Overload Subjects Fee:</label>
            <input type="text" name="overload_fee" class="border p-2 mb-4 w-full"><br>

            <div id="installment_fields" style="display: none;">
                <label class="block mb-2">Installment Type:</label>
                <select name="installment_type" onchange="toggleInstallmentType()" class="border p-2 mb-4 w-full">
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="flexible">Flexible</option>
                </select><br>

                <div id="monthly_fields" style="display: none;">
                    <label class="block mb-2">Monthly Installment Amount:</label>
                    <input type="text" name="monthly_amount" class="border p-2 mb-4 w-full"><br>
                </div>

                <div id="quarterly_fields" style="display: none;">
                    <label class="block mb-2">Quarterly Installment Amount:</label>
                    <input type="text" name="quarterly_amount" class="border p-2 mb-4 w-full"><br>
                </div>

                <div id="flexible_fields" style="display: none;">
                    <label class="block mb-2">Flexible Payment Details:</label>
                    <input type="text" name="flexible_details" class="border p-2 mb-4 w-full"><br>
                </div>
            </div>

            <label id="bank_number" style="display: none;" class="block mb-2">Bank Number:</label>
            <input type="text" name="bank_number" class="border p-2 mb-4 w-full"><br>

            <label class="block mb-2">Payment Status:</label>
            <select name="payment_status" class="border p-2 mb-4 w-full">
                <option value="paid">Paid</option>
                <option value="pending">Pending</option>
            </select><br>

            <input type="submit" value="Submit" class="bg-blue-500 text-white py-2 px-4 rounded">
        </form>
    </div>
</body>
</html>
