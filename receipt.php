<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
</head>
<body>
    <h1>Payment Receipt</h1>
    <p>Thank you for your payment!</p>
    <p>Amount: <span id="amount"></span> PHP</p>
    <p>Transaction ID: <span id="transaction-id"></span></p>
    <script>
        // Get URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const amount = urlParams.get('amount');
        const transactionId = urlParams.get('transaction_id');

        // Display amount and transaction ID
        document.getElementById('amount').textContent = amount;
        document.getElementById('transaction-id').textContent = transactionId;
    </script>
</body>
</html>
