<?php
require 'Schedule.php';

// Create an instance of the Schedule class
$schedule = new Schedule();

// Fetch all schedules
$schedules = $schedule->getAllSchedules();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedules</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        function filterTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toLowerCase();
            const table = document.getElementById("schedulesTable");
            const tr = table.getElementsByTagName("tr");
            let hasMatches = false; // Flag to track if there are matches

            for (let i = 1; i < tr.length; i++) { // Start from 1 to skip the header row
                const td = tr[i].getElementsByTagName("td");
                let rowContainsFilter = false;

                for (let j = 0; j < td.length; j++) {
                    if (td[j] && td[j].innerText.toLowerCase().includes(filter)) {
                        rowContainsFilter = true;
                        break;
                    }
                }
                tr[i].style.display = rowContainsFilter ? "" : "none"; // Show or hide row
                if (rowContainsFilter) {
                    hasMatches = true; // Set the flag if there's a match
                }
            }

            // Show or hide the no results message
            const noResultsMessageRow = document.getElementById("noResultsMessage");
            noResultsMessageRow.style.display = hasMatches ? "none" : ""; // Show message if no matches
        }
    </script>
</head>
<body class="bg-transparent font-sans leading-normal tracking-normal">
    <div class="mt-6">
        <h1 class="text-2xl font-semibold text-red-800 mb-4">Schedules</h1>
        <a href="create_schedule.php" class="inline-block mb-4 px-4 py-4 bg-red-700 text-white rounded hover:bg-red-800">Add New Schedule</a>

        <!-- Search Input -->
        <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search by section, subject, or room..." class="mb-4 p-2 border border-gray-300 rounded">

        <?php if (isset($_GET['message']) && ($_GET['message'] == 'deleted')): ?>
    <div id="deleted-message" class="mb-2 bg-green-200 text-green-700 p-4 rounded">
        <h2 class="text-lg font-semibold">Success</h2>
        <p>The schedule was deleted successfully.</p>
    </div>
    <script>
        setTimeout(function() {
            document.getElementById('deleted-message').style.display = 'none'; // Hide the message
        }, 3000); // Hide after 3000 milliseconds (3 seconds)
    </script>
<?php endif; ?>

        <table id="schedulesTable" class="w-full border-collapse bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-red-800 text-white">
                <tr>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider">ID</th>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider">Section</th>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider">Subject</th>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider">Day of Week</th>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider">Start Time</th>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider">End Time</th>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider">Room</th>
                    <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($schedules)): ?>
                    <?php foreach ($schedules as $sched): ?>
                        <tr class="border bg-red-50 hover:bg-red-200 transition duration-200">
                            <td class="px-4 py-3"><?php echo htmlspecialchars($sched['id'] ?? ''); ?></td>
                            <td class="px-4 py-3"><?php echo htmlspecialchars($sched['section_name'] ?? ''); ?></td>
                            <td class="px-4 py-3"><?php echo htmlspecialchars($sched['subject_name'] ?? ''); ?></td>
                            <td class="px-4 py-3"><?php echo htmlspecialchars($sched['day_of_week'] ?? ''); ?></td>
                            <td class="px-4 py-3"><?php echo htmlspecialchars(date('h:i A', strtotime($sched['start_time']))); ?></td>
                            <td class="px-4 py-3"><?php echo htmlspecialchars(date('h:i A', strtotime($sched['end_time']))); ?></td>
                            <td class="px-4 py-3"><?php echo htmlspecialchars($sched['room_number'] ?? ''); ?></td>
                            <td class="px-4 py-3 flex space-x-2">
                                <a href="update_schedule.php?id=<?php echo urlencode($sched['id']); ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-1 px-2 rounded transition duration-150">Edit</a>
                                <a href="delete_schedule.php?id=<?php echo urlencode($sched['id']); ?>" onclick="return confirm('Are you sure you want to delete this schedule?');" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-2 rounded transition duration-150">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <!-- No Results Message Row -->
                <tr id="noResultsMessage" class="bg-red-50 text-red-500 text-center" style="display: none;">
                    <td colspan="8" class="border-t px-6 py-3">No schedules found matching your search criteria.</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
