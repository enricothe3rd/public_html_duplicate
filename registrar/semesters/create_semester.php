<?php
require 'Semester.php'; // Include the Semester class

// Start the session to access session variables
session_start();

// Create an instance of Semester
$semester = new Semester();

// Initialize variables to retain the form data
$semesterName = isset($_SESSION['last_semester_name']) ? htmlspecialchars($_SESSION['last_semester_name']) : '';
$startDate = isset($_SESSION['last_start_date']) ? htmlspecialchars($_SESSION['last_start_date']) : '';
$endDate = isset($_SESSION['last_end_date']) ? htmlspecialchars($_SESSION['last_end_date']) : '';

// Check for a successful creation flag from the Semester class
$isCreationSuccessful = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the submitted semester data
    $semesterName = isset($_POST['semester_name']) ? htmlspecialchars($_POST['semester_name']) : '';
    $startDate = isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : '';
    $endDate = isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : '';

    // Store inputs in session for retrieval after redirect
    $_SESSION['last_semester_name'] = $semesterName;
    $_SESSION['last_start_date'] = $startDate;
    $_SESSION['last_end_date'] = $endDate;

    // Handle semester creation
    $semester->handleCreateSemesterRequest();
    
    // Check if the semester creation was successful
    $isCreationSuccessful = $semester->isCreationSuccessful();

    // If the creation was successful, unset the session variables
    if ($isCreationSuccessful) {
        unset($_SESSION['last_semester_name']);
        unset($_SESSION['last_start_date']);
        unset($_SESSION['last_end_date']);
        // Redirect to the same page or another page as needed
        header('Location: create_semester.php?message=success');
        exit();
    } else {
        // Optionally handle other cases (exists, invalid, etc.)
    }
}

// Check for the message in the query string
$message = isset($_GET['message']) ? $_GET['message'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Semester</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet"> <!-- Font Awesome -->
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <div class="max-w-2xl mx-auto mt-10 bg-white p-8 shadow-lg rounded-lg">
       
        <button 
            onclick="goBack()" 
            class="mb-4 px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition duration-200 flex items-center"
        >
            <i class="fas fa-arrow-left mr-2"></i> <!-- Arrow icon -->
            Back
        </button>
        
        <h1 class="text-2xl font-semibold text-red-800 mb-4">Create New Semester</h1>

        <?php if ($message == 'exists'): ?>
            <div id="error-message" class="mt-4 bg-red-200 text-red-700 p-4 rounded">
                <h2 class="text-lg font-semibold">Error</h2>
                <p>The section already exists for this course.</p>
            </div>
            <script>
                // Set a timeout to hide the error message after 3 seconds
                setTimeout(function() {
                    var errorMessage = document.getElementById('error-message');
                    if (errorMessage) {
                        errorMessage.style.display = 'none'; // Hide the message
                    }
                }, 3000); // Hide after 3000 milliseconds (3 seconds)
            </script>
        <?php elseif ($message == 'invalid'): ?>
            <div id="error-message" class="mt-4 bg-red-200 text-red-700 p-4 rounded">
                <h2 class="text-lg font-semibold">Error</h2>
                <p>The section name is invalid. Please use only alphanumeric characters and hyphens.</p>
            </div>
            <script>
                setTimeout(function() {
                    var errorMessage = document.getElementById('error-message');
                    if (errorMessage) {
                        errorMessage.style.display = 'none';
                    }
                }, 3000);
            </script>
        <?php elseif ($message == 'success'): ?>
            <div class="mt-4 bg-green-200 text-green-700 p-4 rounded">
                <h2 class="text-lg font-semibold">Success</h2>
                <p>The section was created successfully.</p>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = 'read_semesters.php'; // Make sure this file exists
                }, 3000);
            </script>
        <?php elseif ($message == 'failure'): ?>
            <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
                <h2 class="text-lg font-semibold">Error</h2>
                <p>Failed to create the section. Please try again.</p>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label for="semester_name" class="block text-red-700 font-medium">Semester Name</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm focus-within:border-red-500">
                    <div class="px-4 py-2">
                        <i class="fas fa-calendar-alt text-red-500"></i>
                    </div>
                    <input type="text" name="semester_name" id="semester_name" placeholder="Enter Semester Name" 
                           value="<?php echo $semesterName; ?>" 
                           class="w-full px-4 py-2 border-0 focus:outline-none" required>
                </div>
            </div>

            <div>
                <label for="start_date" class="block text-red-700 font-medium">Start Date</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm focus-within:border-red-500">
                    <div class="px-4 py-2">
                        <i class="fas fa-calendar-day text-red-500"></i>
                    </div>
                    <input type="date" name="start_date" id="start_date" 
                           value="<?php echo $startDate; ?>" 
                           class="w-full px-4 py-2 border-0 focus:outline-none" required>
                </div>
            </div>

            <div>
                <label for="end_date" class="block text-red-700 font-medium">End Date</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm focus-within:border-red-500">
                    <div class="px-4 py-2">
                        <i class="fas fa-calendar-alt text-red-500"></i>
                    </div>
                    <input type="date" name="end_date" id="end_date" 
                           value="<?php echo $endDate; ?>" 
                           class="w-full px-4 py-2 border-0 focus:outline-none" required>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800 focus:outline-none">Create Semester</button>
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery -->
<script>
    function goBack() {
        // Make an AJAX call to unset the session variables
        $.ajax({
            url: 'unset_session.php', // URL of the PHP script to unset session
            type: 'GET',
            success: function() {
                // Once the session variables are unset, navigate back
                window.history.back();
            },
            error: function() {
                console.error('Failed to unset session variables.');
                // Navigate back even if the AJAX call fails
                window.history.back();
            }
        });
    }
</script>

</body>
</html>
