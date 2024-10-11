<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'Schedule.php';

// Create an instance of Schedule
$schedule = new Schedule();

// Handle schedule creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schedule->handleCreateScheduleRequest();
}

// Fetch all sections for the dropdown
$sections = $schedule->getAllSections();

// Fetch all sections for the dropdown
$classrooms = $schedule->getAllClassrooms();

// Initialize variables
$selectedSectionId = isset($_POST['section_id']) ? intval($_POST['section_id']) : null;
$subjects = [];

// Fetch subjects based on selected section
if ($selectedSectionId) {
    $subjects = $schedule->getSubjectsBySection($selectedSectionId);
} else {
    // Fetch all subjects initially (if no section selected)
    $subjects = $schedule->getAllSubjects();

    // Check for the message in the query string
    
$message = isset($_GET['message']) ? $_GET['message'] : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@latest/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto mt-10 p-8 bg-white rounded-lg shadow-lg">
        <button 
            onclick="goBack()" 
            class="mb-4 px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition duration-200 flex items-center"
        >
            <i class="fas fa-arrow-left mr-2"></i> <!-- Arrow icon -->
            Back
        </button>
        <h1 class="text-3xl font-bold text-red-800 mb-6">Add New Schedule</h1>

        <?php if (isset($message) && $message == 'success'): ?>
    <div class="mt-4 bg-green-200 text-green-700 p-4 rounded">
        <h2 class="text-lg font-semibold">Success</h2>
        <p>The schedule was created successfully.</p>
    </div>
    <script>
        // Set a timeout to redirect after 3 seconds
        setTimeout(function() {
            window.location.href = 'read_schedules.php'; // Make sure this file exists
        }, 3000);
    </script>
<?php endif; ?>

        <form action="create_schedule.php" method="post" class="space-y-6">
            <div>
                <label for="section_id" class="block text-sm font-medium text-red-700">
                    <i class="fas fa-users"></i> Section:
                </label>
                <select id="section_id" name="section_id" onchange="this.form.submit()" required class="mt-1 block w-full px-3 py-2 border border-red-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm bg-red-50 text-red-800">
                    <option value="" disabled selected>Select a section</option>
                    <?php foreach ($sections as $section): ?>
                        <option value="<?php echo htmlspecialchars($section['id']); ?>" <?php echo $selectedSectionId == $section['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($section['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="subject_id" class="block text-sm font-medium text-red-700">
                    <i class="fas fa-book"></i> Subject:
                </label>
                <select id="subject_id" name="subject_id" required class="mt-1 block w-full px-3 py-2 border border-red-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm bg-red-50 text-red-800">
                    <option value="" disabled selected>Select a subject</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo htmlspecialchars($subject['id']); ?>">
                            <?php echo htmlspecialchars($subject['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="room_number_id" class="block text-sm font-medium text-red-700">
                    <i class="fas fa-door-open"></i> Room Number:
                </label>
                <select id="room_number_id" name="room_number_id" required class="mt-1 block w-full px-3 py-2 border border-red-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm bg-red-50 text-red-800">
                    <option value="" disabled selected>Select a room</option>
                    <?php foreach ($classrooms as $classroom): ?>
                        <option value="<?php echo htmlspecialchars($classroom['id']); ?>">
                            <?php echo htmlspecialchars($classroom['room_number']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>











            <div>
                <label for="day_of_week" class="block text-sm font-medium text-red-700">
                    <i class="fas fa-calendar-day"></i> Day of Week:
                </label>
                <select id="day_of_week" name="day_of_week" required class="mt-1 block w-full px-3 py-2 border border-red-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm bg-red-50 text-red-800">
                    <option value="" disabled selected>Select a day</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>
            </div>
            <div>
                <label for="start_time" class="block text-sm font-medium text-red-700">
                    <i class="fas fa-clock"></i> Start Time:
                </label>
                <input type="time" id="start_time" name="start_time" required class="mt-1 block w-full px-3 py-2 border border-red-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm">
            </div>
            <div>
                <label for="end_time" class="block text-sm font-medium text-red-700">
                    <i class="fas fa-clock"></i> End Time:
                </label>
                <input type="time" id="end_time" name="end_time" required class="mt-1 block w-full px-3 py-2 border border-red-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm">
            </div>
    
            <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white font-bold py-3 rounded transition duration-200">
                <i class="fas fa-plus"></i> Create Schedule
            </button>
        </form>
    </div>

    <script>
        function goBack() {
            window.history.back(); // Navigates to the previous page
        }
    </script>
</body>
</html>
