<?php
require 'Student.php';
$student = new Student();
$students = $student->getStudents();

if ($students === false) {
    echo "An error occurred while fetching students.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@^3.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mx-auto mt-8 p-4">
        <h1 class="text-2xl font-bold mb-4">Students</h1>
        <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
            <thead>
                <tr class="bg-gray-200">
                    <th class="py-2 px-4 border-b">Student Number</th>
                    <th class="py-2 px-4 border-b">First Name</th>
                    <th class="py-2 px-4 border-b">Middle Name</th>
                    <th class="py-2 px-4 border-b">Last Name</th>
                    <th class="py-2 px-4 border-b">Suffix</th>
                    <th class="py-2 px-4 border-b">Type of Student</th>
                    <th class="py-2 px-4 border-b">Sex</th>
                    <th class="py-2 px-4 border-b">Date of Birth</th>
                    <th class="py-2 px-4 border-b">Email</th>
                    <th class="py-2 px-4 border-b">Contact No</th>
                    <th class="py-2 px-4 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                <tr>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($student['student_number']) ?></td>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($student['first_name']) ?></td>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($student['middle_name']) ?></td>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($student['last_name']) ?></td>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($student['suffix']) ?></td>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($student['student_type']) ?></td>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($student['sex']) ?></td>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($student['dob']) ?></td>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($student['email']) ?></td>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($student['contact_no']) ?></td>
                    <td class="py-2 px-4 border-b">
                        <a href="edit_student.php?id=<?= htmlspecialchars($student['id']) ?>" class="text-blue-500">Edit</a> |
                        <a href="delete_student.php?id=<?= htmlspecialchars($student['id']) ?>" class="text-red-500" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
