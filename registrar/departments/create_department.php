<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require 'Department.php';
// Start the session to use $_SESSION
// Include the success modal


$department = new Department();

// Initialize variables to retain form data
$departmentName = isset($_SESSION['last_department_name']) ? htmlspecialchars($_SESSION['last_department_name'], ENT_QUOTES) : '';
$established = isset($_SESSION['last_established']) ? htmlspecialchars($_SESSION['last_established'], ENT_QUOTES) : '';
$dean = isset($_SESSION['last_dean']) ? htmlspecialchars($_SESSION['last_dean'], ENT_QUOTES) : '';
$email = isset($_SESSION['last_email']) ? htmlspecialchars($_SESSION['last_email'], ENT_QUOTES) : '';
$phone = isset($_SESSION['last_phone']) ? htmlspecialchars($_SESSION['last_phone'], ENT_QUOTES) : '';
$location = isset($_SESSION['last_location']) ? htmlspecialchars($_SESSION['last_location'], ENT_QUOTES) : '';

// Clear session variables after retrieving their values
unset($_SESSION['last_department_name']);
unset($_SESSION['last_established']);
unset($_SESSION['last_dean']);
unset($_SESSION['last_email']);
unset($_SESSION['last_phone']);
unset($_SESSION['last_location']);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize the submitted department data
    $departmentName = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name']), ENT_QUOTES) : '';
    $established = isset($_POST['established']) ? htmlspecialchars(trim($_POST['established']), ENT_QUOTES) : '';
    $dean = isset($_POST['dean']) ? htmlspecialchars(trim($_POST['dean']), ENT_QUOTES) : '';
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email']), ENT_QUOTES) : '';
    $phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone']), ENT_QUOTES) : '';
    $location = isset($_POST['location']) ? htmlspecialchars(trim($_POST['location']), ENT_QUOTES) : '';
   

    // Store inputs in session for retrieval after redirect (for preserving data if needed)
    $_SESSION['last_department_name'] = $departmentName;
    $_SESSION['last_established'] = $established;
    $_SESSION['last_dean'] = $dean;
    $_SESSION['last_email'] = $email;
    $_SESSION['last_phone'] = $phone;
    $_SESSION['last_location'] = $location;


    // Perform basic validation before submitting
    if (empty($departmentName)) {
        $message = "Department name is .";
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Valid email is .";
    } elseif (empty($phone) || !is_numeric($phone)) {
        $message = "A valid phone number is .";
    } else {
        // If validation passes, handle the department creation
        $department->handleCreateDepartmentRequest();
        // Optionally clear session data after successful form submission
        unset($_SESSION['last_department_name']);
        unset($_SESSION['last_established']);
        unset($_SESSION['last_dean']);
        unset($_SESSION['last_email']);
        unset($_SESSION['last_phone']);
        unset($_SESSION['last_location']);
      
    }
}

// Check for the message in the query string
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message'], ENT_QUOTES) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Department</title>
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
            Back
        </button>

        <h1 class="text-3xl font-bold text-red-800 mb-6 text-center">Add New Department</h1>
<!-- Error/Success Messages -->
<div id="messages">
    <?php if ($message == 'exists'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>The department already exists.</p>
        </div>
    <?php elseif ($message == 'empty_name'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>The department name is required.</p>
        </div>
    <?php elseif ($message == 'invalid_name'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>The department name contains invalid characters. Please use only alphanumeric characters and spaces.</p>
        </div>
    <?php elseif ($message == 'empty_dean'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>The dean name is required.</p>
        </div>
    <?php elseif ($message == 'invalid_dean'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>The dean name contains invalid characters. Only alphabetic characters and spaces are allowed.</p>
        </div>
    <?php elseif ($message == 'empty_email'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>An email address is required.</p>
        </div>
    <?php elseif ($message == 'invalid_email'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>Please enter a valid email address (Gmail, Hotmail, Yahoo, Outlook, or iCloud).</p>
        </div>
    <?php elseif ($message == 'empty_phone'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>A phone number is required.</p>
        </div>
    <?php elseif ($message == 'invalid_phone'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>Invalid phone number. It should start with +63 or 09, followed by 9 digits.</p>
        </div>
    <?php elseif ($message == 'empty_location'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>A location is required.</p>
        </div>
    <?php elseif ($message == 'invalid_location'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>The location contains invalid characters. Please use only letters, numbers, commas, periods, or hyphens.</p>
        </div>
    <?php elseif ($message == 'success'): ?>
        <div class="mt-4 bg-green-200 text-green-700 p-4 rounded">
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
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>Failed to create the department. Please try again.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    // Automatically hide error messages after 3 seconds
    setTimeout(function() {
        var errorMessages = document.querySelectorAll('#messages > div:not(.bg-green-200)');
        errorMessages.forEach(function(errorMessage) {
            errorMessage.style.display = 'none';
        });
    }, 3000);
</script>


        <!-- Department Creation Form -->
        <form action="create_department.php" method="post" class="space-y-6">
            <div class="flex flex-col">
                <label for="name" class="text-red-700 font-medium">Department Name</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <label for="name" class="px-3 text-red-700 font-medium"><i class="fas fa-building"></i></label>
                    <input type="text" id="name" name="name" value="<?php echo $departmentName; ?>" placeholder="Department Name" class="block w-full px-3 py-2 bg-red-50 text-red-800 border-none focus:outline-none focus:bg-red-100 focus:border-red-500 rounded-r-md" >
                </div>
            </div>

            <div class="flex flex-col">
                <label for="established" class="text-red-700 font-medium">Established Year</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <label for="established" class="px-3 text-red-700 font-medium"><i class="fas fa-calendar"></i></label>
                    <input type="number" id="established" name="established" value="<?php echo $established; ?>" placeholder="Enter Established Year (YYYY)" class="block w-full px-3 py-2 bg-red-50 text-red-800 border-none focus:outline-none focus:bg-red-100 focus:border-red-500 rounded-r-md" min="1900" max="2099" >
                </div>
            </div>

            <div class="flex flex-col">
                <label for="dean" class="text-red-700 font-medium">Dean</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <label for="dean" class="px-3 text-red-700 font-medium"><i class="fas fa-user-tie"></i></label>
                    <input type="text" id="dean" name="dean" value="<?php echo $dean; ?>" placeholder="Dean" class="block w-full px-3 py-2 bg-red-50 text-red-800 border-none focus:outline-none focus:bg-red-100 focus:border-red-500 rounded-r-md" >
                </div>
            </div>

            <div class="flex flex-col">
                <label for="email" class="text-red-700 font-medium">Contact Email</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <label for="email" class="px-3 text-red-700 font-medium"><i class="fas fa-envelope"></i></label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" placeholder="Contact Email" class="block w-full px-3 py-2 bg-red-50 text-red-800 border-none focus:outline-none focus:bg-red-100 focus:border-red-500 rounded-r-md" >
                </div>
            </div>

            <div class="flex flex-col">
                <label for="phone" class="text-red-700 font-medium">Phone</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <label for="phone" class="px-3 text-red-700 font-medium"><i class="fas fa-phone"></i></label>
                    <input type="text" id="phone" name="phone" value="<?php echo $phone; ?>" placeholder="e.g. +639123456789 or 021234567" class="block w-full px-3 py-2 bg-red-50 text-red-800 border-none focus:outline-none focus:bg-red-100 focus:border-red-500 rounded-r-md" >
                </div>
            </div>

            <div class="flex flex-col">
                <label for="location" class="text-red-700 font-medium">Location</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <label for="location" class="px-3 text-red-700 font-medium"><i class="fas fa-map-marker-alt"></i></label>
                    <input type="text" id="location" name="location" value="<?php echo $location; ?>" placeholder="Location" class="block w-full px-3 py-2 bg-red-50 text-red-800 border-none focus:outline-none focus:bg-red-100 focus:border-red-500 rounded-r-md" >
                </div>
            </div>

            <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition duration-200 flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i> Create Department
            </button>
        </form>
    </div>

    <script>
        function goBack() {
            // Redirect to 'read_departments.php'
            window.location.href = 'read_departments.php';

          
        }
    </script>
</body>
</html>
