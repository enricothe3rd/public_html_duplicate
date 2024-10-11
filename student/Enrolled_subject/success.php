<?php
// success.php
session_start();
// You can set a success message in the session if needed
// $_SESSION['message'] = "Your action was successful!";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <script>
        // Redirect to grades.php after 3 seconds
        setTimeout(function() {
            window.location.href = 'grades.php';
        }, 1000); // 3000 milliseconds = 3 seconds
    </script>
</head>
<body class="flex items-center justify-center h-screen">
<div class="bg-white p-6 rounded shadow-md max-w-sm">
    <h2 class="text-lg font-semibold mb-4">
        <span class="text-green-500">&#10003;</span> <!-- Check icon -->
        Success!
    </h2>
    <p>Your action was completed successfully.</p>
    <p class="text-sm text-gray-600">You will be redirected to the grades page shortly.</p>
</div>

</body>
</html>
