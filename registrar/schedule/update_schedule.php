<?php
require 'Schedule.php';

// Create an instance of Schedule
$schedule = new Schedule();

// Fetch schedule data based on the provided ID in the URL
$sched = null;
if (isset($_GET['id'])) {
    $sched = $schedule->getScheduleById($_GET['id']);
}

// Handle form submission for updating the schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schedule->handleUpdateScheduleRequest();
}

// Fetch all subjects, sections, and rooms for the dropdowns
$subjects = $schedule->getAllSubjects();
$sections = $schedule->getAllSections(); // Fetch sections
$rooms = $schedule->getAllClassrooms(); // Fetch rooms

// Define days of the week for the dropdown
$daysOfWeek = [
    'Monday'    => 'Monday',
    'Tuesday'   => 'Tuesday',
    'Wednesday' => 'Wednesday',
    'Thursday'  => 'Thursday',
    'Friday'    => 'Friday',
    'Saturday'  => 'Saturday',
    'Sunday'    => 'Sunday',
];


// Check for message parameter to display feedback
$message = isset($_GET['message']) ? $_GET['message'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@latest/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div class="max-w-3xl mx-auto mt-10 p-8 bg-white rounded-lg shadow-lg">
        <button onclick="goBack()" class="mb-4 px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition duration-200 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </button>
        <h1 class="text-3xl font-bold text-red-800 mb-6">Update Schedule</h1>

        <!-- Check if schedule data is available before rendering the form -->
        <?php if ($sched): ?>
            <form action="" method="post" class="space-y-6">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($sched['id']); ?>">


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
                <!-- Section Dropdown -->
                <div>
                    <label for="section_id" class="block text-sm font-medium text-red-700">
                        <i class="fas fa-users"></i> Section:
                    </label>
                    <select id="section_id" name="section_id" required class="mt-1 block w-full px-3 py-2 border border-red-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm bg-red-50 text-red-800">
                        <?php if (is_array($sections) && !empty($sections)): ?>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?php echo htmlspecialchars($section['id']); ?>" <?php echo $section['id'] == $sched['section_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($section['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">No sections available</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Subject Dropdown -->
                <div>
                    <label for="subject_id" class="block text-sm font-medium text-red-700">
                        <i class="fas fa-book"></i> Subject:
                    </label>
                    <select id="subject_id" name="subject_id" required class="mt-1 block w-full px-3 py-2 border border-red-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm bg-red-50 text-red-800">
                        <?php if (is_array($subjects) && !empty($subjects)): ?>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo htmlspecialchars($subject['id']); ?>" <?php echo $subject['id'] == $sched['subject_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($subject['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">No subjects available</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Day of Week Dropdown -->
                <div>
                    <label for="day_of_week" class="block text-sm font-medium text-red-700">
                        <i class="fas fa-calendar-day"></i> Day of Week:
                    </label>
                    <select id="day_of_week" name="day_of_week" required class="mt-1 block w-full px-3 py-2 border border-red-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm bg-red-50 text-red-800">
                        <?php foreach ($daysOfWeek as $key => $value): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $key == $sched['day_of_week'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($value); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Start Time -->
                <div>
                    <label for="start_time" class="block text-sm font-medium text-red-700">
                        <i class="fas fa-clock"></i> Start Time:
                    </label>
                    <input type="time" id="start_time" name="start_time" value="<?php echo htmlspecialchars($sched['start_time']); ?>" required class="mt-1 block w-full px-3 py-2 border border-red-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm">
                </div>

                <!-- End Time -->
                <div>
                    <label for="end_time" class="block text-sm font-medium text-red-700">
                        <i class="fas fa-clock"></i> End Time:
                    </label>
                    <input type="time" id="end_time" name="end_time" value="<?php echo htmlspecialchars($sched['end_time']); ?>" required class="mt-1 block w-full px-3 py-2 border border-red-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm">
                </div>

                <!-- Room Dropdown -->
                <div>
                    <label for="room" class="block text-sm font-medium text-red-700">
                        <i class="fas fa-door-open"></i> Room:
                    </label>
                    <select id="room" name="room" required class="mt-1 block w-full px-3 py-2 border border-red-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 sm:text-sm bg-red-50 text-red-800">
                        <?php if (is_array($rooms) && !empty($rooms)): ?>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?php echo htmlspecialchars($room['id']); ?>" <?php echo $room['id'] == $sched['room'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($room['room_number']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">No rooms available</option>
                        <?php endif; ?>
                    </select>
                </div>

                <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white font-bold py-3 rounded transition duration-200">
                    <i class="fas fa-plus"></i> Update Schedule
                </button>
            </form>
        <?php else: ?>
            <p class="text-red-500">Invalid Schedule ID.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<script>
    function goBack() {
        window.history.back(); // Navigates to the previous page
    }
</script>
