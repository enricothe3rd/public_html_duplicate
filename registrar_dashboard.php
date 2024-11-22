<?php 


require 'session_registrar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Dashboard</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
     <!-- You can also use PNG -->
     <!-- <link rel="icon" type="image/x-icon" href="assets/images/school-logo/bcc-icon1.ico"> -->
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

        .custom-logo-size{
            height:50%;
            width: 40%;
        }
    </style>
</head>
<body class="bg-gray-100 ">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class=" bg-red-800 h-[120vh] text-white flex-shrink-0">
            <!-- Logo -->
            <div class="p-6 text-center logo">
            <img src="assets/images/school-logo/bcc-icon1.jpg" alt="Logo" class="custom-logo-size mx-auto rounded-full">

            </div>

            <!-- Navigation -->
            <nav class="mt-4 sm:mt-1">
    <ul>
        <!-- Home -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('home', this)">
                <i class="fas fa-home mr-3"></i>
                <span class="hidden md:inline">Home</span>
            </a>
        </li>

        <!-- Profile -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('profile', this)">
                <i class="fas fa-user mr-3"></i>
                <span class="hidden md:inline">Profile</span>
            </a>
        </li>

        <!-- Department -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('department', this)">
                <i class="fas fa-building mr-3"></i>
                <span class="hidden md:inline">Department</span>
            </a>
        </li>

        <!-- Courses -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('courses', this)">
                <i class="fas fa-graduation-cap mr-3"></i>
                <span class="hidden md:inline">Courses</span>
            </a>
        </li>

        <!-- Sections -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('sections', this)">
                <i class="fas fa-list mr-3"></i>
                <span class="hidden md:inline">Sections</span>
            </a>
        </li>

        <!-- Subjects -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('subjects', this)">
                <i class="fas fa-book mr-3"></i>
                <span class="hidden md:inline">Subjects</span>
            </a>
        </li>

        <!-- Schedules -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('schedule', this)">
                <i class="fas fa-calendar-alt mr-3"></i>
                <span class="hidden md:inline">Schedules</span>
            </a>
        </li>

        <!-- Instructor -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('instructor', this)">
                <i class="fas fa-chalkboard-teacher mr-3"></i>
                <span class="hidden md:inline">Instructor</span>
            </a>
        </li>

        <!-- Instructor Subject Assignment -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('instructor-details', this)">
                <i class="fas fa-info-circle mr-3"></i>
                <span class="hidden md:inline">Instructor Subject Assignment</span>
            </a>
        </li>

        <!-- Semesters -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('set-semester', this)">
                <i class="fas fa-calendar-check mr-3"></i>
                <span class="hidden md:inline">Semesters</span>
            </a>
        </li>

        <!-- Classrooms -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('classroom', this)">
                <i class="fas fa-school mr-3"></i>
                <span class="hidden md:inline">Classrooms</span>
            </a>
        </li>

        <!-- Add Sex Options -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('sex_option', this)">
                <i class="fas fa-file-alt mr-3"></i>
                <span class="hidden md:inline">Add Sex Options</span>
            </a>
        </li>

        <!-- Add Status Options -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('status_option', this)">
                <i class="fas fa-file-alt mr-3"></i>
                <span class="hidden md:inline">Add Status Options</span>
            </a>
        </li>

        <!-- Add Suffixes -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('suffixes', this)">
                <i class="fas fa-file-alt mr-3"></i>
                <span class="hidden md:inline">Add Suffixes</span>
            </a>
        </li>

        <!-- Add School Year -->
        <li>
            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('school_year', this)">
                <i class="fas fa-file-alt mr-3"></i>
                <span class="hidden md:inline">Add School Year</span>
            </a>
        </li>
                <!-- Logout -->
<<<<<<< HEAD
                         <li>
  <a href="javascript:void(0);" onclick="toggleModal(true)" class="flex items-center py-3 px-4 hover:bg-red-500 text-white rounded-lg transition ease-in-out duration-300">
    <i class="fas fa-sign-out-alt mr-3"></i> 
    <span class="hidden md:inline">Logout</span>
  </a>
</li>
<?php include 'logout_modal.php'; ?>
=======
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
        <main class="flex-1 p-6 overflow-hidden ">
        <button id="toggle-sidebar" class="bg-red-800 text-white p-2 rounded mb-4" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i> <!-- Toggle Icon -->
            </button>
            <div id="home" class="content-section">
            <iframe src="student/profile/display_all_student.php" title="All Student"></iframe> 
            </div>

            <div id="profile" class="content-section">

                <iframe src="student/profile/student_profile.php" title="My Profile"></iframe>
            </div>
      


            <div id="department" class="content-section">
       
                <iframe src="registrar/departments/read_departments.php" title="Department"></iframe>
            </div>
            <div id="subjects" class="content-section">
                <iframe src="registrar/subjects/read_subjects.php" title="Subjects"></iframe>
            </div>
            <div id="courses" class="content-section">
                <iframe src="registrar/courses/read_courses.php" title="Courses"></iframe>
            </div>
            <div id="sections" class="content-section">
                <iframe src="registrar/sections/read_sections.php" title="Sections"></iframe>
            </div>
            <div id="schedule" class="content-section">
                <iframe src="registrar/schedule/read_schedules.php" title="Schedule"></iframe>
            </div>
            <div id="students" class="content-section">
                <iframe src="students.php" title="Students"></iframe>
            </div>
            <div id="instructor" class="content-section">
                <iframe src="registrar/instructor/read_instructors.php" title="Instructor"></iframe>
            </div>
            <div id="instructor-details" class="content-section">
                <iframe src="registrar/instructor/instructor_subject/read_instructor_subject.php" title="Instructor Details"></iframe>
            </div>
            <div id="set-semester" class="content-section">
                <iframe src="registrar/semesters/read_semesters.php" title="Set Semester"></iframe>
            </div>
            <div id="classroom" class="content-section">
                <iframe src="registrar/classrooms/read_classrooms.php" title="Classroom"></iframe>
            </div>
            <div id="payment" class="content-section">
                <iframe src="payments/read_payments.php" title="Payment"></iframe>
            </div>
            <div id="sex_option" class="content-section">
                <iframe src="registrar/enrollment/sex_options.php" title="sex_option"></iframe>
            </div>
            <div id="school_year" class="content-section">
                <iframe src="registrar/enrollment/school_year.php" title="school_year"></iframe>
            </div>
            <div id="status_option" class="content-section">
                <iframe src="registrar/enrollment/status_options.php" title="status_option"></iframe>
            </div>
            <div id="suffixes" class="content-section">
                <iframe src="registrar/enrollment/suffixes.php" title="suffixes"></iframe>
            </div>
            <div id="school_year" class="content-section">
                <iframe src="registrar/enrollment/school_year.php" title="school_year"></iframe>
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

        // Add active class to the clicked tab
        element.classList.add('tab-active');

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
        showContent(savedSection, document.querySelector(`nav a[href='#'][onclick*="${savedSection}"]`));
    } else {
        // Default to showing home if no section is saved
        showContent('home', document.querySelector(`nav a[href='#'][onclick*="home"]`));
    }
</script>

</body>
</html>
