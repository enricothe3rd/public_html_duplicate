<?php
// Start session
session_start();

require '../../db/db_connection3.php';
$pdo = Database::connect();

try {
    // Prepare the SQL statement to fetch all enrollments with JOINs for courses, sections, and departments
    $stmt = $pdo->prepare("
        SELECT e.student_number, 
               e.firstname, 
               e.middlename, 
               e.lastname, 
               e.suffix, 
               c.course_name, 
               s.name AS section_name, 
               d.name AS department_name
        FROM enrollments e
        LEFT JOIN subject_enrollments se ON e.student_number = se.student_number
        LEFT JOIN courses c ON se.course_id = c.id
        LEFT JOIN sections s ON se.section_id = s.id
        LEFT JOIN departments d ON c.department_id = d.id
        ORDER BY e.lastname ASC  -- Alphabetize by lastname
    ");
    $stmt->execute();

    // Fetch all enrollment data
    $enrollmentData = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle any SQL errors
    echo "Error: " . $e->getMessage();
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Student Enrollment Information</title>
    <script>
        function filterTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toLowerCase();
            const table = document.getElementById("enrollmentTable");
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
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-semibold text-red-800 mb-4">Student Enrollment Information</h1>

        <!-- Search Input -->
        <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search by name, course, or section..." class="mb-4 p-2 border border-gray-300 rounded">

        <!-- Enrollment Table -->
        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table id="enrollmentTable" class="min-w-full border-collapse shadow-md rounded-lg">
                <thead class="bg-red-800">
                    <tr>
                        <th class="px-4 py-4 border-b text-left text-white">Student Number</th>
                        <th class="px-4 py-4 border-b text-left text-white">Full Name</th>
                        <th class="px-4 py-4 border-b text-left text-white">Course</th>
                        <th class="px-4 py-4 border-b text-left text-white">Section</th>
                        <th class="px-4 py-4 border-b text-left text-white">Department</th>
                        <th class="px-4 py-4 border-b text-left text-white">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($enrollmentData) > 0): ?>
                        <?php foreach ($enrollmentData as $enrollment): ?>
                        <tr class="border-b bg-red-50 hover:bg-red-200">
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($enrollment['student_number']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($enrollment['lastname'] . ' ' . $enrollment['middlename'] . ' ' . $enrollment['firstname'] . ' ' . $enrollment['suffix']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($enrollment['course_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($enrollment['section_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($enrollment['department_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="view_details.php?student_number=<?= urlencode($enrollment['student_number']) ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-1 px-2 rounded transition duration-150">View</a>
                                <a href="edit_enrollment.php?student_number=<?= urlencode($enrollment['student_number']) ?>" class="bg-yellow-500 hover:bg-yellow-700 text-white font-semibold py-1 px-2 rounded transition duration-150">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center">No enrollments found.</td>
                        </tr>
                    <?php endif; ?>
                    
                    <!-- No Results Row -->
                    <tr id="noResultsRow" style="display: none;">
                        <td colspan="6" class="px-6 py-4 text-center text-red-500">No enrollments found matching your search criteria.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
