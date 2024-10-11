<?php
require 'Student.php';
$student = new Student();
$student->handleCreateStudentRequest();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Student</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@^3.0/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mx-auto mt-8 p-4">
        <h1 class="text-2xl font-bold mb-4">Create Student</h1>
        <form action="create_student.php" method="post" class="bg-white p-6 rounded-lg shadow-md">
            <div class="mb-4">
                <label for="student_number" class="block text-gray-700">Student Number:</label>
                <input type="text" id="student_number" name="student_number" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="first_name" class="block text-gray-700">First Name:</label>
                <input type="text" id="first_name" name="first_name" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="middle_name" class="block text-gray-700">Middle Name:</label>
                <input type="text" id="middle_name" name="middle_name" class="w-full p-2 border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="last_name" class="block text-gray-700">Last Name:</label>
                <input type="text" id="last_name" name="last_name" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="suffix" class="block text-gray-700">Suffix:</label>
                <input type="text" id="suffix" name="suffix" class="w-full p-2 border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="student_type" class="block text-gray-700">Type of Student:</label>
                <select id="student_type" name="student_type" class="w-full p-2 border border-gray-300 rounded" required>
                    <option value="Regular">Regular</option>
                    <option value="New">New</option>
                    <option value="Irregular">Irregular</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="sex" class="block text-gray-700">Sex:</label>
                <select id="sex" name="sex" class="w-full p-2 border border-gray-300 rounded" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="dob" class="block text-gray-700">Date of Birth:</label>
                <input type="date" id="dob" name="dob" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email:</label>
                <input type="email" id="email" name="email" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="contact_no" class="block text-gray-700">Contact No:</label>
                <input type="text" id="contact_no" name="contact_no" class="w-full p-2 border border-gray-300 rounded">
            </div>
            <button type="submit" class="bg-blue-500 text-white p-2 rounded">Create Student</button>
        </form>
    </div>
</body>
</html>
