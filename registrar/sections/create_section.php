<?php
session_start(); // Start the session

require 'Section.php';

// Create an instance of Section
$section = new Section();

// Initialize variables to retain the form data
$sectionName = isset($_SESSION['last_section_name']) ? htmlspecialchars($_SESSION['last_section_name']) : '';
$courseId = isset($_SESSION['last_course_id']) ? htmlspecialchars($_SESSION['last_course_id']) : '';

// Clear session variables after retrieving their values
unset($_SESSION['last_section_name']);
unset($_SESSION['last_course_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the submitted section name and course ID
    $sectionName = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
    $courseId = isset($_POST['course_id']) ? htmlspecialchars($_POST['course_id']) : '';
    
    // Store inputs in session for retrieval after redirect
    $_SESSION['last_section_name'] = $sectionName;
    $_SESSION['last_course_id'] = $courseId;

    // Handle section creation
    $section->handleCreateSectionRequest();
}

// Fetch all courses for the dropdown
$courses = $section->getAllCourses(); 

// Check for the message in the query string
$message = isset($_GET['message']) ? $_GET['message'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Section</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div class="container mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg max-w-lg">
        <button onclick="goBack()" class="mb-4 px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition duration-200 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </button>
        
        <h1 class="text-2xl font-semibold text-red-800 mb-4">Add New Section</h1>
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

<?php elseif ($message == 'invalid_name'): ?>
    <div id="error-message" class="mt-4 bg-red-200 text-red-700 p-4 rounded">
        <h2 class="text-lg font-semibold">Error</h2>
        <p>The section name is invalid. Please use only alphanumeric characters and hyphens.</p>
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
        <p>The section was created successfully.</p>
    </div>
    <script>
        // Set a timeout to redirect after 3 seconds
        setTimeout(function() {
            window.location.href = 'read_sections.php'; // Make sure this file exists
        }, 3000);
    </script>

<?php elseif ($message == 'failure'): ?>
    <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
        <h2 class="text-lg font-semibold">Error</h2>
        <p>Failed to create the section. Please try again.</p>
    </div>
<?php endif; ?>

        <form action="create_section.php" method="post" class="space-y-4">
            <div>
                <label for="name" class="block text-red-700 font-medium">Section Name:</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <i class="fas fa-chalkboard-teacher px-3 text-red-500"></i>
                    <input type="text" id="name" name="name" value="<?php echo $sectionName; ?>" placeholder="Enter section name" required class="bg-red-50 block w-full px-3 py-2 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                </div>
            </div>
            <div>
                <label for="course_id" class="block text-red-700 font-medium">Course:</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <i class="fas fa-book px-3 text-red-500"></i>
                    <select id="course_id" name="course_id" required class="block w-full px-3 py-2 bg-red-50 text-red-800 border-red-300 rounded-md shadow-sm focus:outline-none focus:bg-red-100 focus:border-red-500 sm:text-sm">
                        <option value="" disabled>Select a course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo htmlspecialchars($course['id']); ?>" <?php echo ($courseId == $course['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="w-full px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 flex items-center justify-center">
                <i class="fas fa-plus-circle mr-2"></i> Create Section
            </button>
        </form>
    </div>

    <script>
        function closeModal() {
            window.location.href = 'create_section.php';
        }

        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>
