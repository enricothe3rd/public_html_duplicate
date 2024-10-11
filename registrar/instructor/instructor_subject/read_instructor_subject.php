<?php
require 'Instructor_subject.php';

$instructorSubject = new InstructorSubject();

// Fetch all instructor-subject assignments
$assignments = $instructorSubject->read();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Instructor Subjects</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        async function deleteAssignment(id) {
            if (confirm("Are you sure you want to delete this assignment?")) {
                try {
                    const response = await fetch('delete_instructor_subject.php', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({ id })
                    });

                    const result = await response.json();
                    if (result.success) {
                        alert("Assignment deleted successfully!");
                        window.location.reload();
                    } else {
                        alert(`Failed to delete assignment. ${result.message || ''}`);
                    }
                } catch (error) {
                    console.error('Error deleting assignment:', error);
                }
            }
        }

        function filterTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toLowerCase();
            const table = document.getElementById("assignmentsTable");
            const tr = table.getElementsByTagName("tr");
            let hasMatch = false; // Flag to check if there's a match

            for (let i = 1; i < tr.length; i++) { // Start from 1 to skip the header row
                const td = tr[i].getElementsByTagName("td");
                let rowContainsFilter = false;

                for (let j = 0; j < td.length; j++) {
                    if (td[j] && td[j].innerText.toLowerCase().includes(filter)) {
                        rowContainsFilter = true;
                        hasMatch = true; // Set flag to true if a match is found
                        break;
                    }
                }
                tr[i].style.display = rowContainsFilter ? "" : "none"; // Show or hide row
            }

            // Show or hide the no results message
            const noResultsRow = document.getElementById("noResultsRow");
            noResultsRow.style.display = hasMatch ? "none" : ""; // Show if no match is found
        }
    </script>
</head>
<body class="bg-transparent font-sans leading-normal tracking-normal">

    <!-- Container -->
    <div class="mt-6">
        <h1 class="text-2xl font-semibold text-red-800 mb-4">Instructor Subjects</h1>

        <!-- Add New Assignment Button -->
        <div class="mb-4">
            <a href="create_instructor_subject.php" class="inline-block px-4 py-4 bg-red-700 text-white rounded hover:bg-red-800">
                Add New Assignment
            </a>
                    <!-- Search Input -->
        <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search by instructor, subject, or semester..." class="mb-4 p-2 border border-gray-300 rounded">

        </div>

        <?php if (isset($_GET['message'])): ?>

    <?php if ($_GET['message'] == 'deleted'): ?>
        <div id="deleted-message" class="mb-2 bg-green-200 text-green-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Success</h2>
            <p>The instructor assignment was deleted successfully.</p>
        </div>
        <script>
        setTimeout(function() {
            document.getElementById('deleted-message').style.display = 'none'; // Hide the message
        }, 3000); // Hide after 3000 milliseconds (3 seconds)
    </script>
    
    <?php endif; ?>
    <?php endif; ?>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table id="assignmentsTable" class="w-full border-collapse bg-white shadow-md rounded-lg overflow-hidden">
                <thead class="bg-red-800 text-white">
                    <tr>
                        <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider">Instructor</th>
                        <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider">Subject</th>
                        <th class="px-4 py-4 border-b text-left font-medium uppercase tracking-wider">Semester</th>
                        <th class="px-4 py-4 border-b text-center font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-red-50 divide-y divide-gray-200">
                    <?php foreach ($assignments as $assignment): ?>
                    <tr class="hover:bg-red-200 transition duration-200">
                        <td class="px-4 py-3"><?php echo htmlspecialchars($assignment['instructor_name']); ?></td>
                        <td class="px-4 py-3"><?php echo htmlspecialchars($assignment['subject_name']); ?></td>
                        <td class="px-4 py-3"><?php echo htmlspecialchars($assignment['semester_name']); ?></td>
                        <td class="px-4 py-3 text-center flex space-x-2 justify-center">
                            <!-- Edit Button -->
                            <a href="edit_instructor_subject.php?id=<?php echo htmlspecialchars($assignment['id']); ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-1 px-2 rounded transition duration-150">
    Edit
</a>

                            <!-- Delete Button -->
                            <a href="delete_instructor_subject.php?id=<?php echo htmlspecialchars($assignment['id']); ?>" onclick="return confirm('Are you sure you want to delete this instructor assignment?');" class="bg-red-500 hover:bg-red-700 text-white font-semibold py-1 px-2 rounded transition duration-150">Delete</a>
                 
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <!-- No Results Row -->
                    <tr id="noResultsRow" style="display: none;">
                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">No assignments found matching your search.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
