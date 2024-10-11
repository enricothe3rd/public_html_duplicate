<?php
session_start(); // Start the session

require 'Classroom.php'; // Include the Classroom class

// Create an instance of Classroom
$classroom = new Classroom();

// Initialize variables to retain the form data
$roomNumber = isset($_SESSION['last_room_number']) ? htmlspecialchars($_SESSION['last_room_number']) : '';
$capacity = isset($_SESSION['last_capacity']) ? htmlspecialchars($_SESSION['last_capacity']) : '';
$building = isset($_SESSION['last_building']) ? htmlspecialchars($_SESSION['last_building']) : '';

// Clear session variables after retrieving their values
unset($_SESSION['last_room_number']);
unset($_SESSION['last_capacity']);
unset($_SESSION['last_building']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the submitted classroom details
    $roomNumber = isset($_POST['room_number']) ? htmlspecialchars($_POST['room_number']) : '';
    $capacity = isset($_POST['capacity']) ? htmlspecialchars($_POST['capacity']) : '';
    $building = isset($_POST['building']) ? htmlspecialchars($_POST['building']) : '';

    // Store inputs in session for retrieval after redirect
    $_SESSION['last_room_number'] = $roomNumber;
    $_SESSION['last_capacity'] = $capacity;
    $_SESSION['last_building'] = $building;

    // Handle classroom creation
    $classroom->handleCreateClassroomRequest();
}

// Check for the message in the query string
$message = isset($_GET['message']) ? $_GET['message'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Classroom</title>
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
        <h1 class="text-2xl font-semibold text-red-800 mb-4">Create New Classroom</h1>

        <?php if ($message == 'exists'): ?>
            <div id="error-message" class="mt-4 bg-red-200 text-red-700 p-4 rounded">
                <h2 class="text-lg font-semibold">Error</h2>
                <p>The classroom already exists.</p>
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

        <?php elseif ($message == 'invalid_input'): ?>
            <div id="error-message" class="mt-4 bg-red-200 text-red-700 p-4 rounded">
                <h2 class="text-lg font-semibold">Error</h2>
                <p>Invalid input. Please ensure all fields are filled correctly.</p>
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

        <?php elseif ($message == 'success'): ?>
            <div class="mt-4 bg-green-200 text-green-700 p-4 rounded">
                <h2 class="text-lg font-semibold">Success</h2>
                <p>The classroom was created successfully.</p>
            </div>
            <script>
                // Set a timeout to redirect after 3 seconds
                setTimeout(function() {
                    window.location.href = 'read_classrooms.php'; // Ensure this file exists
                }, 3000);
            </script>
 <?php elseif ($message == 'empty_fields'): ?>
            <div id="warning-message" class="mt-4 bg-yellow-200 text-yellow-700 p-4 rounded">
                <h2 class="text-lg font-semibold">Warning</h2>
                <p>Please fill all the fields</p>
            </div>
            <script>
                // Set a timeout to hide the error message after 3 seconds
                setTimeout(function() {
                    var errorMessage = document.getElementById('warning-message');
                    if (errorMessage) {
                        errorMessage.style.display = 'none'; // Hide the message
                    }
                }, 3000); // Hide after 3000 milliseconds (3 seconds)
            </script>
<?php elseif ($message == 'invalid_building'): ?>
    <div id="warning-message" class="mt-4 bg-yellow-200 text-yellow-700 p-4 rounded">
        <h2 class="text-lg font-semibold">Warning</h2>
        <p>The building name must contain at least one letter. Please provide a valid building name.</p>
    </div>
    <script>
        // Set a timeout to hide the warning message after 3 seconds
        setTimeout(function() {
            var warningMessage = document.getElementById('warning-message');
            if (warningMessage) {
                warningMessage.style.display = 'none'; // Hide the message
            }
        }, 3000); // Hide after 3000 milliseconds (3 seconds)
    </script>


        <?php elseif ($message == 'failure'): ?>
            <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
                <h2 class="text-lg font-semibold">Error</h2>
                <p>Failed to create the classroom. Please try again.</p>
            </div>
        <?php endif; ?>

        <form action="create_classroom.php" method="POST" class="space-y-4">
            <div>
                <label for="room_number" class="block text-red-700 font-medium">Room Number</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm focus-within:border-red-500">
                    <div class="px-4 py-2">
                        <i class="fas fa-door-open text-red-500"></i>
                    </div>
                    <input type="text" id="room_number" name="room_number" value="<?php echo $roomNumber; ?>" placeholder="Enter Room Number" class="w-full px-4 py-2 border-0 focus:outline-none">
                </div>
            </div>
            <div class="mb-4">
                <label for="capacity" class="block text-red-700 font-medium">Capacity</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm focus-within:border-red-500">
                    <span class="px-3 text-gray-500">
                        <i class="fas fa-users text-red-500"></i>
                    </span>
                    <input type="number" id="capacity" name="capacity" value="<?php echo $capacity; ?>" placeholder="Enter Capacity" class="w-full px-4 py-2 border-0 focus:outline-none">
                </div>
            </div>
            <div class="mb-4">
                <label for="building" class="block text-red-700 font-medium">Building</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm focus-within:border-red-500">
                    <span class="px-3 text-gray-500">
                        <i class="fas fa-building text-red-500"></i>
                    </span>
                    <input type="text" id="building" name="building" value="<?php echo $building; ?>" placeholder="Enter Building Number" class="w-full px-4 py-2 border-0 focus:outline-none">
                </div>
            </div>
            <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800">Create Classroom</button>
        </form>
    </div>
</body>
</html>
<script>
    function goBack() {
        window.history.back(); // Navigates to the previous page
    }
</script>
