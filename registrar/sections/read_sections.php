<?php
require 'Section.php';

// Create an instance of the Section class
$section = new Section();

// Fetch all sections
$sections = $section->getAllSections();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sections</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        function filterTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toLowerCase();
            const table = document.getElementById("sectionsTable");
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
            const noResultsRow = document.getElementById("noResultsRow");
            noResultsRow.style.display = hasMatches ? "none" : ""; // Show message if no matches
        }
    </script>
</head>
<body class="bg-transparent font-sans leading-normal tracking-normal">
    <div class="mt-6">

        <h1 class="text-2xl font-semibold text-red-800 mb-4">Sections</h1>
        <a href="create_section.php" class="inline-block mb-4 px-4 py-4 bg-red-700 text-white rounded hover:bg-red-800">Add New Section</a>
        
        

        <!-- Search Input -->
        <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search by section name or course..." class="mb-4 p-2 border border-gray-300 rounded">

        <?php if (isset($_GET['message'])): ?>
    <?php if ($_GET['message'] == 'exists'): ?>
        <div id="error-message" class="mb-2 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>This section cannot be deleted because it has associated subjects.</p>
        </div>
        <script>
        setTimeout(function() {
            document.getElementById('error-message').style.display = 'none'; // Hide the message
        }, 3000); // Hide after 3000 milliseconds (3 seconds)
    </script>
    <?php elseif ($_GET['message'] == 'deleted'): ?>
        <div id="deleted-message" class="mb-2 bg-green-200 text-green-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Success</h2>
            <p>The section was deleted successfully.</p>
        </div>
        <script>
        setTimeout(function() {
            document.getElementById('deleted-message').style.display = 'none'; // Hide the message
        }, 3000); // Hide after 3000 milliseconds (3 seconds)
    </script>
    
    <?php endif; ?>
<?php endif; ?>
        <table id="sectionsTable" class="w-full border-collapse overflow-hidden">
            <thead class="bg-red-800">
                <tr>
                    <th class="px-4 py-4 border-b text-left font-medium text-white uppercase tracking-wider">Name</th>
                    <th class="px-4 py-4 border-b text-left font-medium text-white uppercase tracking-wider">Course</th>
                    <th class="px-4 py-4 border-b text-left font-medium text-white uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php foreach ($sections as $sec): ?>
                <tr class="border-b bg-red-50 hover:bg-red-200">
                    <td class="px-4 py-2"><?php echo htmlspecialchars($sec['name'] ?? ''); ?></td>
                    <td class="border-t px-6 py-4"><?= htmlspecialchars($section->getCourseName($sec['course_id'])) ?></td>
                    <td class="px-4 py-2 flex space-x-2">
                        <a href="update_section.php?id=<?php echo $sec['id']; ?>" class="bg-yellow-500 hover:bg-yellow-700 text-white font-semibold py-1 px-2 rounded transition duration-150">Edit</a>
                        <a href="delete_section.php?id=<?php echo $sec['id']; ?>" onclick="return confirm('Are you sure you want to delete this section?');" class="bg-red-500 hover:bg-red-700 text-white font-semibold py-1 px-2 rounded transition duration-150">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <!-- No Results Row -->
                <tr id="noResultsRow" class="border-b bg-red-50 hover:bg-red-200" style="display: none;">
                    <td colspan="3" class="text-red-500 text-center py-4">No sections found matching your search criteria.</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
