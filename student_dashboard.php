<?php
session_start(); // Start the session

// Include the Database class file (adjust the path if necessary)
require_once 'db/db_connection3.php'; // Adjust the path to where your Database class is defined



// // Fetch the student_number and email from the session
$student_number = $_SESSION['student_number'] ?? null;
// $email = $_SESSION['user_email'] ?? null;
// $role = $_SESSION['user_role'];


require 'session_student.php';

// Call the connect method to get PDO instance
$pdo = Database::connect();

$payment_method = null; // Initialize payment method

if ($student_number) {
    // Prepare the SQL query to fetch the payment method
    $sql = "SELECT payment_method, payment_status FROM payments WHERE student_number = :student_number LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':student_number', $student_number, PDO::PARAM_STR);
    // Execute the query
    $stmt->execute();

    // Fetch the result
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if a payment record was found
    if ($payment) {
        $payment_method = $payment['payment_method'];
    }

       // Check if a payment record was found
       if ($payment) {
        $payment_status = $payment['payment_status'];
    }
}

// Echo message based on payment method existence
// if ($payment_method) {
//     echo "<div class='text-green-600'>Payment method exists: $payment_method</div>";
// } else {
//     echo "<div class='text-red-600'>No payment method found for student number: $student_number</div>";
// }
?>

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
            <nav class="mt-4">
                <ul class="flex flex-col space-y-2">
                    <li><a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('home')"><i class="fas fa-home mr-3"></i><span class="hidden md:inline">Home</span></a></li>
                    <li><a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('profile')"><i class="fas fa-user mr-3"></i><span class="hidden md:inline">My Profile</span></a></li>

                    <?php if (!empty($payment_status)): ?>
                    <li><a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('subjects')"><i class="fas fa-book mr-3"></i><span class="hidden md:inline">My Subjects</span></a></li>
                    <li><a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('grades')"><i class="fas fa-graduation-cap mr-3"></i><span class="hidden md:inline">My Grades</span></a></li>
                    <?php endif; ?>

                    <?php if (empty($payment_method)): ?>
                        <li>
                            <a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('enrollment')">
                                <i class="fas fa-user-plus mr-3"></i><span class="hidden md:inline">Enrollment</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (!empty($payment_status)): ?>
                    <li><a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('department')"><i class="fas fa-file-invoice-dollar mr-3"></i><span class="hidden md:inline">My Payments</span></a></li>
                    <?php endif; ?>

                    <?php if ($payment_method == 'installment'): ?>
                    <li><a href="#" class="flex items-center py-3 px-4 hover:bg-red-500" onclick="showContent('make_payments')"><i class="fas fa-credit-card mr-3"></i><span class="hidden md:inline">Make a Payment</span></a></li>
                    <?php endif; ?>

                </ul>
            </nav>

        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6 overflow-hidden">
        <button id="toggle-sidebar" class="bg-red-800 text-white p-2 rounded mb-4" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i> <!-- Toggle Icon -->
            </button>
            <div id="home" class="content-section">
            <iframe src="front_page.php" title="My Subjects"></iframe>
            </div>
            <div id="profile" class="content-section">
                <iframe src="student/profile/student_profile.php" title="My Profile"></iframe>
            </div>
   
            <?php if (!empty($payment_status)): ?>
            <div id="subjects" class="content-section">
                <iframe src="student/Enrolled_subject/enrolled_subject.php" title="My Subject"></iframe>
            </div>

            <div id="grades" class="content-section">
                <iframe src="registrar/instructor/get_grades.php" title="My grades"></iframe>
            </div>
            <?php endif; ?>

            <?php if (empty($payment_method)): ?>
            <div id="enrollment" class="content-section">
                <iframe src="payments/enrollments/create_enrollment.php" title="New Enrollments"></iframe>
            </div>
            <?php endif; ?>

            <?php if (!empty($payment_status)): ?>
            <div id="department" class="content-section">
                <iframe src="student/Enrolled_subject/enrolled_payments.php" title="Research Fees"></iframe>
            </div>
            <?php endif; ?>
            <?php if ($payment_method == 'installment'): ?>
            <div id="make_payments" class="content-section " >
                <iframe src="payments/repayment.php" title="Research Fees"></iframe>
            </div>
            <?php endif; ?>
   


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
