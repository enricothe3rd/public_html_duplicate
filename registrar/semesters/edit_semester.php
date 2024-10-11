<?php
require 'Semester.php';

$semester = new Semester();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $semester_data = $semester->getSemesterById($id);
}
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $semester->handleUpdateSemesterRequest($id);
    }

    
// Check for message parameter to display feedback
$message = isset($_GET['message']) ? $_GET['message'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Semester</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet"> <!-- Font Awesome -->
</head>
<body class="bg-gray-100">

    <div class="max-w-2xl mx-auto mt-10 bg-white p-8 shadow-lg rounded-lg">
        <button 
            onclick="goBack()" 
            class="mb-4 px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition duration-200 flex items-center"
        >
            <i class="fas fa-arrow-left  mr-2"></i> <!-- Arrow icon -->
            Back
        </button>

        <h1 class="text-2xl font-semibold text-red-800 mb-4">Edit Semester</h1>


      <!-- Display success or error messages -->
<?php if (isset($_GET['message'])): ?>
    <?php if ($_GET['message'] == 'not_found'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>The semester was not found.</p>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = 'create_semester.php'; // Redirect after 3 seconds
            }, 3000);
        </script>
    <?php elseif ($_GET['message'] == 'invalid_name'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>Invalid semester name. Only letters, numbers, hyphens, and single spaces are allowed.</p>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = 'create_semester.php'; // Redirect after 3 seconds
            }, 3000);
        </script>
    <?php elseif ($_GET['message'] == 'exists'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>The semester name already exists. Please choose a different name.</p>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = 'edit_semester.php?id=<?php echo htmlspecialchars($_GET['id']); ?>'; // Redirect after 3 seconds
            }, 3000);
        </script>
    <?php elseif ($_GET['message'] == 'updated_successfully'): ?>
        <div class="mt-4 bg-green-200 text-green-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Success</h2>
            <p>The semester was updated successfully.</p>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = 'read_semesters.php'; // Redirect after 3 seconds
            }, 3000);
        </script>
    <?php elseif ($_GET['message'] == 'update_failed'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>Failed to update the semester. Please try again.</p>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = 'create_semester.php'; // Redirect after 3 seconds
            }, 3000);
        </script>
    <?php endif; ?>
<?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label for="semester_name" class="block text-red-700 font-medium">
                    <i class="fas fa-calendar-alt  text-red-500"></i> <!-- Calendar icon -->
                    Semester Name
                </label>
                <input type="text" name="semester_name" id="semester_name" value="<?= $semester_data['semester_name'] ?>" class="w-full px-4 py-2 border border-red-300 rounded-lg focus:outline-none focus:border-red-500" required>
            </div>

            <div>
                <label for="start_date" class="block text-red-700 font-medium">
                    <i class="fas fa-calendar-day  text-red-500"></i> <!-- Start Date icon -->
                    Start Date
                </label>
                <input type="date" name="start_date" id="start_date" value="<?= $semester_data['start_date'] ?>" class="w-full px-4 py-2 border border-red-300 rounded-lg focus:outline-none focus:border-red-500" required>
            </div>

            <div>
                <label for="end_date" class="block text-red-700 font-medium">
                    <i class="fas fa-calendar-alt  text-red-500"></i> <!-- Calendar icon for End Date -->
                    End Date
                </label>
                <input type="date" name="end_date" id="end_date" value="<?= $semester_data['end_date'] ?>" class="w-full px-4 py-2 border border-red-300 rounded-lg focus:outline-none focus:border-red-500" required>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-red-700 text-white rounded-lg hover:bg-red-800 focus:outline-none flex items-center">
                    <i class="fas fa-save mr-2"></i> <!-- Save icon -->
                    Update Semester
                </button>
            </div>
        </form>
    </div>

</body>
</html>

<script>
        function goBack() {
            window.history.back(); // Navigates to the previous page
        }
    </script>