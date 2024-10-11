<?php
require 'Course.php';

// Create an instance of Course
$course = new Course();

// Fetch course data based on the provided ID in the URL
$currentCourse = null;
if (isset($_GET['id'])) {
    $currentCourse = $course->getCourseById($_GET['id']);
    if (!$currentCourse) {
        // If the course does not exist, redirect with an error message
        header('Location: course_list.php?message=course_not_found');
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Call the handleUpdateCourseRequest method with the course ID
    $course->handleUpdateCourseRequest($_GET['id']);
}

// Check for message parameter to display feedback
$message = isset($_GET['message']) ? $_GET['message'] : '';

// Fetch all departments for the dropdown
$departments = $course->getDepartments();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div class="container mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg max-w-lg">
        <button 
            onclick="goBack()" 
            class="mb-4 px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition duration-200 flex items-center"
        >
            <i class="fas fa-arrow-left mr-2"></i>
            Back
        </button>
         
        <h1 class="text-2xl font-semibold text-red-800 mb-4">Edit Course</h1>

        <!-- Display success or error messages -->
        <?php if ($message == 'exists'): ?>
            <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
                <h2 class="text-lg font-semibold">Error</h2>
                <p>The course already exists.</p>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = 'update_course.php?id=<?php echo htmlspecialchars($_GET['id']); ?>'; // Redirect after 3 seconds
                }, 3000);
            </script>
        <?php elseif ($message == 'success'): ?>
            <div class="mt-4 bg-green-200 text-green-700 p-4 rounded">
                <h2 class="text-lg font-semibold">Success</h2>
                <p>The course was updated successfully.</p>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = 'read_courses.php'; // Change to the desired URL
                }, 3000); // Redirect after 3000 milliseconds (3 seconds)
            </script>
        <?php elseif ($message == 'invalid_name'): ?>
            <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
                <h2 class="text-lg font-semibold">Error</h2>
                <p>The course name is invalid. Please enter a valid name.</p>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = 'update_course.php?id=<?php echo htmlspecialchars($_GET['id']); ?>'; // Redirect after 3 seconds
                }, 3000); // Redirect after 3000 milliseconds (3 seconds)
            </script>
        <?php endif; ?>

        <!-- Check if course data is available before rendering the form -->
        <?php if (isset($currentCourse)) { ?>
            <form action="" method="post" class="space-y-4">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($currentCourse['id']); ?>">

                <!-- Course Name Input -->
                <div>
                    <label for="course_name" class="block text-red-700 font-medium">Course Name:</label>
                    <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                        <i class="fas fa-book px-3 text-red-500"></i>
                        <input type="text" id="course_name" name="course_name" value="<?php echo htmlspecialchars($currentCourse['course_name']); ?>" placeholder="Enter course name" required class="block w-full bg-red-50 px-3 py-2 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm placeholder-red-500">
                    </div>
                </div>

                <!-- Department Selection Dropdown -->
                <div>
                    <label for="department_id" class="block text-red-700 font-medium">Department:</label>
                    <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                        <i class="fas fa-building px-3 text-red-500"></i>
                        <select id="department_id" name="department_id" required class="block w-full px-3 py-2 bg-red-50 text-red-800 border-red-300 rounded-md shadow-sm focus:outline-none focus:bg-red-100 focus:border-red-500 sm:text-sm">
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= htmlspecialchars($dept['id']) ?>" <?= $dept['id'] == $currentCourse['department_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i>
                    Update Course
                </button>
            </form>
        <?php } else { ?>
            <p class="text-red-500">Invalid Course ID.</p>
        <?php } ?>
    </div>
</body>
</html>
<script>
    function goBack() {
        window.history.back(); // Navigates to the previous page
    }
</script>
