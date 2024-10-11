<?php
require 'Subject.php';

$subject = new Subject();

// Fetch subject data based on the provided ID in the URL
$sub = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $sub = $subject->find($_GET['id']);
}

// Fetch all sections, semesters, and school years for the dropdowns
$sections = $subject->getAllSections();
$semesters = $subject->getAllSemesters();
$schoolYears = $subject->getAllSchoolYears(); // New method to fetch school years

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject->handleUpdateSubjectRequest();
}

$message = isset($_GET['message']) ? $_GET['message'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Subject</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet"> <!-- Font Awesome -->
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div class="container mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg max-w-md">
        <button 
            onclick="goBack()" 
            class="mb-4 px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition duration-200 flex items-center"
        >
            <i class="fas fa-arrow-left mr-2"></i> <!-- Arrow icon -->
            Back
        </button>

        <h1 class="text-2xl font-semibold text-red-800 mb-4">Update Subject</h1>

        <?php if (isset($message) && $message == 'success'): ?>
    <div class="mt-4 bg-green-200 text-green-700 p-4 rounded" id="message-box">
        <h2 class="text-lg font-semibold">Success</h2>
        <p>The subject was updated successfully.</p>
    </div>
    <script>
        // Set a timeout to redirect after 3 seconds
        setTimeout(function() {
            window.location.href = 'read_subjects.php'; // Make sure this file exists
        }, 3000);
        
        // Hide message after a delay
        setTimeout(function() {
            document.getElementById('message-box').style.display = 'none';
        }, 3000); // Hides after 5 seconds
    </script>
<?php elseif (isset($message) && $message == 'failure'): ?>
    <div class="mt-4 bg-red-200 text-red-700 p-4 rounded" id="message-box">
        <h2 class="text-lg font-semibold">Failure</h2>
        <p>Failed to update the subject. Please try again.</p>
    </div>
    <script>
        // Hide message after a delay
        setTimeout(function() {
            document.getElementById('message-box').style.display = 'none';
        }, 3000); // Hides after 5 seconds
    </script>
<?php elseif (isset($message) && $message == 'invalid_input'): ?>
    <div class="mt-4 bg-yellow-200 text-yellow-700 p-4 rounded" id="message-box">
        <h2 class="text-lg font-semibold">Invalid Input</h2>
        <p>Please fill in all required fields correctly.</p>
    </div>
    <script>
        // Hide message after a delay
        setTimeout(function() {
            document.getElementById('message-box').style.display = 'none';
        }, 3000); // Hides after 5 seconds
    </script>
<?php elseif (isset($message) && $message == 'invalid_request'): ?>
    <div class="mt-4 bg-orange-200 text-orange-700 p-4 rounded" id="message-box">
        <h2 class="text-lg font-semibold">Invalid Request</h2>
        <p>The request method is not valid. Please try again.</p>
    </div>
    <script>
        // Hide message after a delay
        setTimeout(function() {
            document.getElementById('message-box').style.display = 'none';
        }, 3000); // Hides after 5 seconds
    </script>
<?php endif; ?>

        <!-- Display the form if subject data is found -->
        <?php if ($sub) { ?>
            <form action="update_subject.php" method="post" class="space-y-4">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($sub['id']); ?>">
                
                <div>
                    <label for="code" class="block text-red-700 font-medium">Subject Code:</label>
                    <input type="text" id="code" name="code" value="<?php echo htmlspecialchars($sub['code']); ?>"  class="mt-1 block w-full px-3 py-2 border border-red-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="title" class="block text-red-700 font-medium">Subject Title:</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($sub['title']); ?>"  class="mt-1 block w-full px-3 py-2 border border-red-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="section_id" class="block text-red-700 font-medium">Section:</label>
                    <select id="section_id" name="section_id" class="mt-1 block w-full px-3 py-2 bg-red-50 text-red-800 border-red-300 rounded-md shadow-sm focus:outline-none focus:bg-red-100 focus:border-red-500 sm:text-sm" >
                        <?php foreach ($sections as $section) { ?>
                            <option value="<?php echo htmlspecialchars($section['id']); ?>" <?php echo ($section['id'] == $sub['section_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($section['name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div>
                    <label for="semester_id" class="block text-red-700 font-medium">Semester:</label>
                    <select id="semester_id" name="semester_id" class="mt-1 block w-full px-3 py-2 bg-red-50 text-red-800 border-red-300 rounded-md shadow-sm focus:outline-none focus:bg-red-100 focus:border-red-500 sm:text-sm" >
                        <?php foreach ($semesters as $semester) { ?>
                            <option value="<?php echo htmlspecialchars($semester['id']); ?>" <?php echo ($semester['id'] == $sub['semester_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($semester['semester_name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                
                <div>
    <label for="school_year" class="block text-red-700 font-medium">School Year:</label>
    <select id="school_year" name="school_year_id" class="mt-1 block w-full px-3 py-2 bg-red-50 text-red-800 border-red-300 rounded-md shadow-sm focus:outline-none focus:bg-red-100 focus:border-red-500 sm:text-sm" >
        <?php foreach ($schoolYears as $year) { ?>
            <option value="<?php echo htmlspecialchars($year['id']); ?>" <?php echo ($year['id'] == $sub['school_year_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($year['year']); ?>
            </option>
        <?php } ?>
    </select>
</div>


                <div>
                    <label for="units" class="block text-red-700 font-medium">Units:</label>
                    <input type="number" id="units" name="units" value="<?php echo htmlspecialchars($sub['units']); ?>"  class="mt-1 block w-full px-3 py-2 border border-red-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm">
                </div>
                
                <button type="submit" class="w-full px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i> <!-- Save icon -->
                    Update Subject
                </button>
            </form>
        <?php } else { ?>
            <p class="text-red-500">Invalid Subject ID or no data found for the provided ID.</p>
        <?php } ?>
    </div>

    <script>
        function goBack() {
            window.history.back(); // Navigates to the previous page
        }
    </script>
</body>
</html>
