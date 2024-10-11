<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar with Multiple Content Sections</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        .additional-buttons {
            display: none; /* Hide additional buttons by default */
        }
        .arrow-icon {
            transition: transform 0.3s ease;
        }
        .arrow-icon.expanded {
            transform: rotate(90deg); /* Rotate icon when expanded */
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-200 text-red-500 flex-shrink-0">
            <!-- Logo -->
            <div class="p-6 text-center">
                <img src="assets/images/school-logo/bcc-icon.png" alt="Logo" class="mx-auto">
            </div>

            <!-- Navigation -->
            <nav class="mt-4">
                <ul>
                    <li><a href="#" class="flex items-center py-2 px-4 hover:bg-gray-300" onclick="showContent('home')"><i class="fas fa-home mr-3"></i> Home</a></li>
                    <li><a href="#" class="flex items-center py-2 px-4 hover:bg-gray-300" onclick="showContent('enrollment')"><i class="fas fa-user-plus mr-3"></i> New Enrollments</a></li>
                    <li><a href="#" class="flex items-center py-2 px-4 hover:bg-gray-300" onclick="showContent('student')"><i class="fas fa-user mr-3"></i> Student</a></li>
                    <li><a href="#" class="flex items-center py-2 px-4 hover:bg-gray-300" onclick="showContent('department')"><i class="fas fa-building mr-3"></i> Department</a></li>
                    <li><a href="#" class="flex items-center py-2 px-4 hover:bg-gray-300" onclick="showContent('courses')"><i class="fas fa-graduation-cap mr-3"></i> Courses <i class="fas fa-chevron-right arrow-icon"></i></a></li>
                    <li><a href="#" class="flex items-center py-2 px-4 hover:bg-gray-300" onclick="showContent('sections')"><i class="fas fa-list mr-3"></i> Sections <i class="fas fa-chevron-right arrow-icon"></i></a></li>
                    <li><a href="#" class="flex items-center py-2 px-4 hover:bg-gray-300" onclick="showContent('subjects')"><i class="fas fa-book mr-3"></i> Subjects</a></li>
                    <li><a href="#" class="flex items-center py-2 px-4 hover:bg-gray-300" onclick="showContent('schedule')"><i class="fas fa-calendar-alt mr-3"></i> Schedule</a></li>
                    <li><a href="#" class="flex items-center py-2 px-4 hover:bg-gray-300" onclick="showContent('students')"><i class="fas fa-users mr-3"></i> Students</a></li>
                    <li><a href="#" class="flex items-center py-2 px-4 hover:bg-gray-300" onclick="showContent('instructor')"><i class="fas fa-chalkboard-teacher mr-3"></i> Instructor</a></li>
                    <li class="pl-8 additional-buttons">
                        <a href="#" class="flex items-center py-2 px-4 hover:bg-gray-300" onclick="showContent('instructor-details')"><i class="fas fa-info-circle mr-3"></i> Instructor Subject Assignment</a>
                    </li>
                    <li><a href="#" class="flex items-center py-2 px-4 hover:bg-gray-300" onclick="showContent('set-semester')"><i class="fas fa-calendar-check mr-3"></i> Set Semester</a></li>
                    <li><a href="#" class="flex items-center py-2 px-4 hover:bg-gray-300" onclick="showContent('classroom')"><i class="fas fa-school mr-3"></i> Classroom</a></li>
                    <li><a href="#" class="flex items-center py-2 px-4 hover:bg-gray-300" onclick="showContent('payment')"><i class="fas fa-money-check-alt mr-3"></i> Payments</a></li>
                    <li><a href="#" class="flex items-center py-2 px-4 hover:bg-gray-300" onclick="showContent('subjectamount')"><i class="fas fa-file-alt mr-3"></i> Add Subject Amount</a></li>
                    <li><a href="#" class="flex items-center py-2 px-4 hover:bg-gray-300" onclick="showContent('report')"><i class="fas fa-file-alt mr-3"></i> Report</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-hidden  rounded-lg shadow-lg">
            <div id="home" class="content-section">
                <iframe src="home.php" title="Home"></iframe>
            </div>
            <div id="enrollment" class="content-section">
                <iframe src="enrollments/create_enrollment.php" title="New Enrollments"></iframe>
            </div>
            <div id="department" class="content-section">
                <iframe src="departments/read_departments.php" title="Department"></iframe>
            </div>
            <div id="subjects" class="content-section">
                <iframe src="subjects/read_subjects.php" title="Subjects"></iframe>
            </div>
            <div id="courses" class="content-section">
                <iframe src="courses/read_courses.php" title="Courses"></iframe>
            </div>
            <div id="sections" class="content-section">
                <iframe src="sections/read_sections.php" title="Courses"></iframe>
            </div>
            <div id="schedule" class="content-section">
                <iframe src="schedule/read_schedules.php" title="Schedule"></iframe>
            </div>
            <div id="students" class="content-section">
                <iframe src="students.php" title="Students"></iframe>
            </div>
            <div id="instructor" class="content-section">
                <iframe src="instructor/read_instructors.php" title="Instructor"></iframe>
            </div>
            <div id="instructor-details" class="content-section">
                <iframe src="instructor/instructor_subject/read_instructor_subject.php" title="Instructor Details"></iframe>
            </div>
            <div id="set-semester" class="content-section">
                <iframe src="semesters/read_semesters.php" title="Set Semester"></iframe>
            </div>
            <div id="classroom" class="content-section">
                <iframe src="classrooms/read_classrooms.php" title="Classroom"></iframe>
            </div>
            <div id="student" class="content-section">
                <iframe src="students/read_students.php" title="Student"></iframe>
            </div>
            <div id="payment" class="content-section">
                <iframe src="payments/read_payments.php" title="Payment"></iframe>
            </div>
            <div id="subjectamount" class="content-section">
                <iframe src="payments/subjectAmount/manage_subject_amount.php" title="Subject Amount"></iframe>
            </div>
            <div id="report" class="content-section">
                <iframe src="instructor/instructor_subject/read_instructor_subject.php" title="Report"></iframe>
            </div>
        </main>
    </div>

    <script>
    function showContent(id) {
        // Hide all content sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = 'none';
        });

        // Show the selected content section
        document.getElementById(id).style.display = 'block';

        // Toggle additional buttons visibility if instructor section is selected
        document.querySelectorAll('.additional-buttons').forEach(button => {
            if (id === 'instructor') {
                button.style.display = 'block';
            } else {
                button.style.display = 'none';
            }
        });

        // Store the currently selected section in localStorage
        localStorage.setItem('currentSection', id);
    }

    // Load the last viewed section from localStorage on page load
    window.onload = function() {
        const lastViewedSection = localStorage.getItem('currentSection') || 'home';
        showContent(lastViewedSection);
    };
    </script>
</body>
</html>
