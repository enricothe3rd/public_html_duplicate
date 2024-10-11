<?php
require 'Student.php';
$student = new Student();
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $studentData = $student->getStudentById($id);
    if (!$studentData) {
        echo 'Student not found.';
        exit();
    }
    $student->handleUpdateStudentRequest($id);
} else {
    echo 'Invalid request.';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mx-auto mt-8 p-4">
        <h1 class="text-2xl font-bold mb-4">Edit Student</h1>
        <form action="edit_student.php?id=<?php echo htmlspecialchars($id); ?>" method="post" class="bg-white p-6 rounded-lg shadow-md">
            <div class="mb-4">
                <label for="student_number" class="block text-gray-700">Student Number:</label>
                <input type="text" id="student_number" name="student_number" value="<?php echo htmlspecialchars($studentData['student_number']); ?>" class="w-full p-2 border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="name" class="block text-gray-700">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($studentData['name']); ?>" class="w-full p-2 border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($studentData['email']); ?>" class="w-full p-2 border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="phone" class="block text-gray-700">Phone:</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($studentData['phone']); ?>" class="w-full p-2 border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="date_of_birth" class="block text-gray-700">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($studentData['date_of_birth']); ?>" class="w-full p-2 border border-gray-300 rounded">
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update Student</button>
        </form>
    </div>
</body>
</html>
