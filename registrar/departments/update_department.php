<?php
session_start(); // Start the session to store error messages
require 'Department.php';

$department = new Department();



if (isset($_GET['id'])) {
    $dep = $department->getSectionById($_GET['id']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $department->handleUpdateDepartmentRequest();
}

// Check for message parameter to display feedback
$message = isset($_GET['message']) ? $_GET['message'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Department</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div class="container mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg max-w-md">
        <button 
            onclick="goBack()" 
            class="mb-4 px-4 py-2 bg-red-700 text-white rounded hover:bg-red-700 transition duration-200 flex items-center"
        >
            <i class="fas fa-arrow-left mr-2"></i> <!-- Arrow icon -->
        </button>
        <h1 class="text-3xl font-bold text-red-800 mb-6 text-center">Update Department</h1>
        
   
<!-- Error/Success Messages -->
<div id="messages">
    <?php if ($message == 'exists'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded" id="error-message">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>The department already exists.</p>
        </div>
    <?php elseif ($message == 'empty_name'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded" id="error-message">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>The department name is required.</p>
        </div>
    <?php elseif ($message == 'invalid_name'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded" id="error-message">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>The department name contains invalid characters. Please use only alphanumeric characters and spaces.</p>
        </div>
    <?php elseif ($message == 'empty_dean'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded" id="error-message">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>The dean name is required.</p>
        </div>
    <?php elseif ($message == 'invalid_dean'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded" id="error-message">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>The dean name contains invalid characters. Only alphabetic characters and spaces are allowed.</p>
        </div>
    <?php elseif ($message == 'empty_email'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded" id="error-message">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>An email address is required.</p>
        </div>
    <?php elseif ($message == 'invalid_email'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded" id="error-message">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>Please enter a valid email address (Gmail, Hotmail, Yahoo, Outlook, or iCloud).</p>
        </div>
    <?php elseif ($message == 'empty_phone'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded" id="error-message">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>A phone number is required.</p>
        </div>
    <?php elseif ($message == 'invalid_phone'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded" id="error-message">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>Invalid phone number. It should start with +63 or 09, followed by 9 digits.</p>
        </div>
    <?php elseif ($message == 'empty_location'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded" id="error-message">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>A location is required.</p>
        </div>
    <?php elseif ($message == 'invalid_location'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded" id="error-message">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>The location contains invalid characters. Please use only letters, numbers, commas, periods, or hyphens.</p>
        </div>
    <?php elseif ($message == 'success'): ?>
        <div class="mt-4 bg-green-200 text-green-700 p-4 rounded" id="success-message">
            <h2 class="text-lg font-semibold">Success</h2>
            <p>The department was created successfully.</p>
        </div>
        <script>
            // Redirect to read_departments.php after 3 seconds
            setTimeout(function() {
                window.location.href = 'read_departments.php';
            }, 3000);
        </script>
    <?php elseif ($message == 'failure'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded" id="error-message">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>Failed to create the department. Please try again.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    // Automatically remove error messages after 3 seconds
    setTimeout(function() {
        var errorMessage = document.getElementById('error-message');
        if (errorMessage) {
            errorMessage.style.display = 'none';
        }
    }, 3000);
</script>

<?php if (isset($dep)) { ?>
        <form action="update_department.php" method="post" class="space-y-6">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($dep['id']); ?>">
            <div class="flex items-center border-b border-red-300 py-2">
                <label for="name" class="text-red-700 font-medium mr-2"><i class="fas fa-building"></i></label>
                <input type="text" id="name" name="name"  value="<?php echo htmlspecialchars($dep['name']); ?>" required placeholder="Department Name" class="mt-1 block w-full px-3 py-2 border-none focus:outline-none focus:ring-0 focus:border-b-2 focus:border-red-500">
            </div>
            <div class="flex items-center border-b border-red-300 py-2">
                <label for="established" class="text-red-700 font-medium mr-2"><i class="fas fa-calendar"></i></label>
                <input type="number" id="established" name="established"  value="<?php echo htmlspecialchars($dep['established']); ?>" required placeholder="Established Year" class="mt-1 block w-full px-3 py-2 border-none focus:outline-none focus:ring-0 focus:border-b-2 focus:border-red-500">
            </div>
            <div class="flex items-center border-b border-red-300 py-2">
                <label for="dean" class="text-red-700 font-medium mr-2"><i class="fas fa-user-tie"></i></label>
                <input type="text" id="dean" name="dean"  value="<?php echo htmlspecialchars($dep['dean']); ?>" required placeholder="Dean" class="mt-1 block w-full px-3 py-2 border-none focus:outline-none focus:ring-0 focus:border-b-2 focus:border-red-500">
            </div>
            <div class="flex items-center border-b border-red-300 py-2">
                <label for="email" class="text-red-700 font-medium mr-2"><i class="fas fa-envelope"></i></label>
                <input type="email" id="email" name="email"  value="<?php echo htmlspecialchars($dep['email']); ?>" required placeholder="Contact Email" class="mt-1 block w-full px-3 py-2 border-none focus:outline-none focus:ring-0 focus:border-b-2 focus:border-red-500">
            </div>
            <div class="flex items-center border-b border-red-300 py-2">
                <label for="phone" class="text-red-700 font-medium mr-2"><i class="fas fa-phone"></i></label>
                <input type="text" id="phone" name="phone"  value="<?php echo htmlspecialchars($dep['phone']); ?>" required placeholder="Phone" class="mt-1 block w-full px-3 py-2 border-none focus:outline-none focus:ring-0 focus:border-b-2 focus:border-red-500">
            </div>
            <div class="flex items-center border-b border-red-300 py-2">
                <label for="location" class="text-red-700 font-medium mr-2"><i class="fas fa-map-marker-alt"></i></label>
                <input type="text" id="location" name="location"  value="<?php echo htmlspecialchars($dep['location']); ?>"value="<?php echo htmlspecialchars($location); ?>" required placeholder="Location" class="mt-1 block w-full px-3 py-2 border-none focus:outline-none focus:ring-0 focus:border-b-2 focus:border-red-500">
            </div>
            <div class="flex justify-center">
                <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition duration-200">Update Department</button>
            </div>
        </form>
        <?php } else { ?>
            <p class="text-red-500">Invalid Section ID.</p>
        <?php } ?>
    </div>
    </div>
    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>
