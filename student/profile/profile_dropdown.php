<?php
// session_start();
// Check if the profile photo exists in the session
if (isset($_SESSION['profile_photo']) && !empty($_SESSION['profile_photo'])) {
    $profilePhoto = $_SESSION['profile_photo'];
} else {
    $profilePhoto = 'default_photo.png'; // Path to a default photo or handle accordingly
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Dropdown Icon</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <style>
        .dropdown {
            display: none;
        }
        .dropdown.active {
            display: block;
        }
    </style>
</head>
<body class="flex items-center justify-end h-screen bg-gray-100 pr-10">

    <div class="relative inline-block">
        <!-- Dropdown Component -->
        <div class="dropdown-icon">
            <button id="dropdownToggle" class="flex items-center">
                <img src="../uploads/<?= htmlspecialchars($profilePhoto) ?>" alt="Profile Photo" class="w-20 h-20 rounded-full" /> <!-- Profile photo -->
            </button>
            <div id="dropdownMenu" class="dropdown absolute right-0 mt-2 w-48 bg-white border border-gray-200 shadow-lg rounded-lg overflow-hidden z-10">
                <div class="flex items-center p-4 border-b border-gray-200">
                    <img src="../uploads/<?= htmlspecialchars($profilePhoto) ?>" alt="Profile Photo" style="width: 50px; height: auto;" class="rounded-full"> <!-- Display profile photo -->
                    <span class="font-semibold text-gray-800 ml-2">User Name</span> <!-- Replace with dynamic username -->
                </div>
                <ul class="py-1">
                    <li class="py-2 px-4 text-gray-800 hover:bg-gray-100 cursor-pointer transition duration-150">Profile</li>
                    <li class="py-2 px-4 text-gray-800 hover:bg-gray-100 cursor-pointer transition duration-150">Logout</li>
                </ul>
            </div>
        </div>
        <!-- End Dropdown Component -->
    </div>

    <script>
        const dropdownToggle = document.getElementById('dropdownToggle');
        const dropdownMenu = document.getElementById('dropdownMenu');

        dropdownToggle.addEventListener('click', () => {
            dropdownMenu.classList.toggle('active');
        });

        window.addEventListener('click', (event) => {
            if (!dropdownToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.remove('active');
            }
        });
    </script>
</body>
</html>
