<?php
session_start(); // Start the session

require 'Course.php';

// Initialize Course class
$course = new Course();

// Initialize variables to retain form data
$courseName = isset($_SESSION['last_course_name']) ? htmlspecialchars($_SESSION['last_course_name']) : '';
$departmentId = isset($_SESSION['last_department_id']) ? htmlspecialchars($_SESSION['last_department_id']) : '';

// Clear session variables after retrieving their values
unset($_SESSION['last_course_name']);
unset($_SESSION['last_department_id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve submitted course name and department ID
    $courseName = isset($_POST['course_name']) ? htmlspecialchars($_POST['course_name']) : '';
    $departmentId = isset($_POST['department_id']) ? htmlspecialchars($_POST['department_id']) : '';

    // Store inputs in session for retrieval after redirect
    $_SESSION['last_course_name'] = $courseName;
    $_SESSION['last_department_id'] = $departmentId;

    // Handle course creation
 $course->handleCreateCourseRequest();
    
    // Check the result of the course creation and set a message
    $message = isset($_GET['message']) ? $_GET['message'] : '';

}

// Fetch departments for the dropdown
$departments = $course->getDepartments();


// Check for the message in the query string
$message = isset($_GET['message']) ? $_GET['message'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Create Course</title>
</head>
<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto mt-10 p-8 bg-white rounded-lg shadow-lg">
        <button 
            onclick="goBack()" 
            class="mb-4 px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition duration-200 flex items-center"
        >
            <i class="fas fa-arrow-left mr-2"></i>
            Back
        </button>
        <h1 class="text-3xl font-bold text-red-800 mb-6">Create Course</h1>

        <?php if ($message == 'exists'): ?>
    <div id="error-message" class="mt-4 bg-red-200 text-red-700 p-4 rounded">
        <h2 class="text-lg font-semibold">Error</h2>
        <p>The course already exists for this department.</p>
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

<?php elseif ($message == 'invalid_name'): ?>
    <div id="error-message" class="mt-4 bg-red-200 text-red-700 p-4 rounded">
        <h2 class="text-lg font-semibold">Error</h2>
        <p>The course name is invalid. Please use only alphanumeric characters and hyphens.</p>
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
        <p>The course was created successfully.</p>
    </div>
    <script>
        // Set a timeout to redirect after 3 seconds
        setTimeout(function() {
            window.location.href = 'read_courses.php'; // Make sure this file exists
        }, 3000);
    </script>

<?php elseif ($message == 'failure'): ?>
    <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
        <h2 class="text-lg font-semibold">Error</h2>
        <p>Failed to create the section. Please try again.</p>
    </div>
<?php endif; ?>


        <form method="post" class="space-y-6">
            <div>
                <label for="course_name" class="block text-sm font-medium text-red-700">Course Name</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <i class="fas fa-book px-3 text-red-500"></i>
                    <input type="text" id="course_name" name="course_name" value="<?php echo $courseName; ?>" placeholder="Enter Course Name" required 
                           class="w-full h-10 px-3 py-2 focus:outline-none ">
                </div>
            </div>

            <div>
                <label for="department_id" class="block text-sm font-medium text-red-700">Department</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <i class="fas fa-building px-3 text-red-500"></i>
                    <select id="department_id" name="department_id" required 
                            class="w-full h-12 px-3 bg-red-50 text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 rounded-md transition duration-200">
                        <option value="" disabled selected>Select a department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= htmlspecialchars($dept['id']) ?>" <?= ($departmentId == $dept['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($dept['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white font-bold py-3 px-4 rounded transition duration-200">
                Create Course
            </button>
        </form>
    </div>
</body>
</html>

<script>
    function goBack() {
        window.history.back(); // Navigates to the previous page
    }
</script>
