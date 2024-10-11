<?php
require 'Semester.php';

$semester = new Semester();
$semesters = $semester->getSemesters();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semesters List</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-transparent font-sans leading-normal tracking-normal">

    <div class="mt-6 ">
        <h2 class="text-2xl font-semibold text-red-800 mb-6">Semesters</h2>
<!-- Display success or error messages -->
<?php if (isset($_GET['message'])): ?>
    <?php if ($_GET['message'] == 'not_found'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>The semester was not found.</p>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = 'read_semesters.php'; // Redirect after 3 seconds
            }, 3000);
        </script>
    <?php elseif ($_GET['message'] == 'deleted_successfully'): ?>
        <div class="mt-4 bg-green-200 text-green-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Success</h2>
            <p>The semester was deleted successfully.</p>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = 'read_semesters.php'; // Redirect after 3 seconds
            }, 3000);
        </script>
    <?php elseif ($_GET['message'] == 'delete_failed'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>Failed to delete the semester. Please try again.</p>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = 'read_semesters.php'; // Redirect after 3 seconds
            }, 3000);
        </script>
    <?php endif; ?>
<?php endif; ?>

        <div class="mb-4 mt-2">
            <a href="create_semester.php" class="inline-block px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800">Add Semester</a>
        </div>

        <table class="min-w-full border-collapset shadow-md rounded-lg">
            <thead class="bg-red-800">
                <tr>
                    <th class="px-4 py-4 border-b text-left text-white">ID</th>
                    <th class="px-4 py-4 border-b text-left text-white">Semester Name</th>
                    <th class="px-4 py-4 border-b text-left text-white">Start Date</th>
                    <th class="px-4 py-4 border-b text-left text-white">End Date</th>
                    <th class="px-4 py-4 border-b text-left text-white">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($semesters as $semester) : ?>
                    <tr class="border-b bg-red-50 hover:bg-red-200">
                        <td class="border-t px-6 py-3"><?php echo htmlspecialchars($semester['id']); ?></td>
                        <td class="border-t px-6 py-3"><?php echo htmlspecialchars($semester['semester_name']); ?></td>
                        <td class="border-t px-6 py-3"><?php echo htmlspecialchars($semester['start_date']); ?></td>
                        <td class="border-t px-6 py-3"><?php echo htmlspecialchars($semester['end_date']); ?></td>
                        <td class="border-t px-6 py-3 text-center">
                            <a href="edit_semester.php?id=<?php echo htmlspecialchars($semester['id']); ?>" class="bg-yellow-500 hover:bg-yellow-700 text-white font-semibold py-1 px-2 rounded transition duration-150">Edit</a>
                            <a href="delete_semester.php?id=<?php echo htmlspecialchars($semester['id']); ?>" onclick="return confirm('Are you sure?');" class="bg-red-500 hover:bg-red-700 text-white font-semibold py-1 px-2 rounded transition duration-150">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
