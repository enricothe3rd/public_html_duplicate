<?php

require 'session_instructor.php';

// Include the Database class file (adjust the path if necessary)
require_once 'db/db_connection3.php'; // Adjust the path to where your Database class is defined

// Fetch the student_number and email from the session

$email = $_SESSION['user_email'] ?? null; // Adjusted to use session variable directly

// Call the connect method to get PDO instance
$pdo = Database::connect();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Department Dashboard</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/png" href="assets/images/school-logo/bcc-icon.png">
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        .content-section {
            display: none;
            height: calc(100% - 64px); /* Adjust height considering sidebar header */
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .tab-active {
            background-color: #f87171; /* Active tab background color */
            color: white; /* Active tab text color */
        }
        aside{
            width:15vw;
        }
        #toggle-sidebar{
            display: none;
        }
        /* Hide sidebar by default on mobile */
        @media (max-width: 768px) {
            aside{
                width: 12vw;
                padding-top:5%;
            }
            aside, .logo {
                display: none; /* Hide sidebar */
            }
            .sidebar-visible {
                display: block; /* Show sidebar when toggled */
            }
            /* Hide text on small devices */
            aside span {
                display: none; /* Hide text */
            }
            /* Show icons on small devices */
            aside a{
                padding:0;
            }
            aside i {
                display: block; /* Ensure icons are displayed */
            }
            nav ul li a {
    margin: 0; /* Removes any margin */
    padding: 0; /* Removes any padding */

}
#toggle-sidebar{
            display: block;
        }

        }
    </style>

</head>
<body class="bg-gray-100">

    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class=" bg-red-800 h-[120vh] text-white flex-shrink-0">
            <!-- Logo -->
            <div class="p-6 text-center logo">
                <img src="assets/images/school-logo/bcc-icon1.jpg" alt="Logo" class="custom-logo-size rounded-full">
            </div>
            <!-- Navigation -->
   <!-- Navigation -->
<nav class="mt-4 sm:mt-1">
    <ul>
        <!-- Home -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" 
               onclick="showContent('home', this)">
               <i class="fas fa-home mr-3"></i> 
               <span class="hidden md:inline">Home</span>
            </a>
        </li>

        <!-- Profile -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" 
               onclick="showContent('profile', this)">
               <i class="fas fa-user mr-3"></i> 
               <span class="hidden md:inline">Profile</span>
            </a>
        </li>

        <!-- Enrolled Subjects -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" 
               onclick="showContent('department', this)">
               <i class="fas fa-book-open mr-3"></i> 
               <span class="hidden md:inline">Enrolled Subjects</span>
            </a>
        </li>

        <!-- Student by Subjects -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" 
               onclick="showContent('courses', this)">
               <i class="fas fa-users mr-3"></i> 
               <span class="hidden md:inline">Student by Subjects</span>
               <i class="fas fa-angle-right ml-auto"></i> <!-- Greater than icon -->
            </a>
        </li>

<<<<<<< HEAD
                <!-- Student by Subjects -->
                <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" 
               onclick="showContent('dropped', this)">
               <i class="fas fa-users mr-3"></i> 
               <span class="hidden md:inline">View Dropped Students</span>
               <i class="fas fa-angle-right ml-auto"></i> <!-- Greater than icon -->
            </a>
        </li>
        <!-- Logout -->
                        <li>
  <a href="javascript:void(0);" onclick="toggleModal(true)" class="flex items-center py-3 px-4 hover:bg-red-500 text-white rounded-lg transition ease-in-out duration-300">
    <i class="fas fa-sign-out-alt mr-3"></i> 
    <span class="hidden md:inline">Logout</span>
  </a>
</li>
<?php include 'logout_modal.php'; ?>
=======
        <!-- Logout -->
        <li>
            <a href="logout.php" class="flex items-center py-3 px-4 hover:bg-red-500 text-white" 
               onclick="return confirm('Are you sure you want to log out?');">
               <i class="fas fa-sign-out-alt mr-3"></i> 
               <span class="hidden md:inline">Logout</span>
            </a>
        </li>
>>>>>>> c9683a1ae74a68f06ae55803e5bcd1740a1a2b49
    </ul>
</nav>

        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-hidden">
        <button id="toggle-sidebar" class="bg-red-800 text-white p-2 rounded mb-4" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i> <!-- Toggle Icon -->
            </button>
            <div id="home" class="content-section">
                <iframe src="front_page.php" title="Home"></iframe>
            </div>

            <div id="profile" class="content-section">
                <iframe src="student/profile/student_profile.php" title="My Profile"></iframe>
            </div>

            <div id="department" class="content-section">
                <iframe src="student/Enrolled_subject/grades.php" title="Research Fees"></iframe>
            </div>


            <div id="courses" class="content-section">
                <iframe src="registrar/instructor/student_by_subject.php" title="Courses"></iframe>
            </div>

            <div id="dropped" class="content-section">
                <iframe src="student/Enrolled_subject/view_dropped_students.php" title="dropped"></iframe>
            </div>


        </main>
    </div>

    <script>
    function showContent(id, element) {
        // Hide all content sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = 'none';
        });

        // Show the selected content section
        document.getElementById(id).style.display = 'block';

        // Remove active class from all tabs
        document.querySelectorAll('nav a').forEach(tab => {
            tab.classList.remove('tab-active');
        });

        // Add active class to the clicked tab (if available)
        if (element) {
            element.classList.add('tab-active');
        }

        // Show sidebar on mobile
        if (window.innerWidth <= 768) {
            document.getElementById('sidebar').classList.add('sidebar-visible');
        }

        // Store the currently selected section in localStorage
        localStorage.setItem('selectedSection', id);
    }

    // Function to toggle sidebar visibility
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('sidebar-visible');
    }

    // Check if there is a saved section in localStorage
    const savedSection = localStorage.getItem('selectedSection');
    if (savedSection) {
        const savedTab = document.querySelector(`nav a[onclick*="${savedSection}"]`);
        showContent(savedSection, savedTab);
    } else {
        // Default to showing home if no section is saved
        const homeTab = document.querySelector(`nav a[onclick*="home"]`);
        showContent('home', homeTab);
    }
</script>
</body>
</html>
