<?php
require '../../db/db_connection3.php';

try {
    $db = Database::connect();

    // Fetch all dropped students from the archive table
    $query = $db->query("SELECT * FROM archived_students ORDER BY created_at DESC");
    $droppedStudents = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching dropped students: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <title>View Dropped Students</title>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-10 p-5">
        <h1 class="text-2xl font-semibold text-red-700 mb-5">Dropped Students</h1>

        <?php if (!empty($droppedStudents)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300 text-left bg-white shadow-lg rounded-lg">
                    <thead class="bg-red-700 text-white">
                        <tr>
                            <th class="px-4 py-2">Student Number</th>
                            <th class="px-4 py-2">Name</th>
      
                            <th class="px-4 py-2">Date Dropped</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($droppedStudents as $student): ?>
                            <tr class="hover:bg-red-50">
                                <td class="border px-4 py-2"><?= htmlspecialchars($student['student_number']); ?></td>
                                <td class="border px-4 py-2"><?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></td>
     
                                <td class="border px-4 py-2"><?= htmlspecialchars(date('F j, Y, g:i a', strtotime($student['created_at']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-600">No dropped students found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
