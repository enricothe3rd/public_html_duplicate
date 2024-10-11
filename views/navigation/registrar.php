<?php
    $paths = [
    'registrar_icons' => '../../assets/images/registrar-icons',
    'navigation_icons' => '../../assets/images/navigation-icons',
    'schoolLogo' => '../../assets/images/school-logo',
    'courses' => '../registrar/courses.php',
    'classes' => '../registrar/classes.php',
    'sections' => '../registrar/sections.php',
    'subjects' => '../registrar/subjects.php'
    ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include '../../includes/favicon.php'; ?>
    <title>Bcc / Registrar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    html, body{
        overflow:hidden;
        height: 100%;
    }
    .content-section {
        height: 100%;
    }


</style>
</head>
<body class="bg-gray-100">


    <div class="flex h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="fixed inset-y-0 left-0 z-30 w-60 h-[160vh] bg-[#820300] text-white transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0">
            <div class="p-4 flex justify-between items-center lg:hidden">
                <h2 class="text-2xl font-semibold">Dashboard</h2>
                <button onclick="toggleSidebar()" class="text-white focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <nav class="pt-5">

                        <!-- <div id="profile-button" class="flex items-center py-3 px-10 rounded-lg transition duration-200 hover:bg-[#B80000] focus:outline-none focus:ring-2 focus:ring-[#B80000] focus:ring-offset1-21"
                tabindex="2"><img src="<?php// echo $paths['assets'] . 'profile.png'; ?>" alt="Profile Button" class="w-10 h-10">
                    <button class="ml-5 text-md font-semibold text-white hover:text-gray-200 focus:outline-none">Profile</button></div> -->
                <div class="flex items-center justify-center p-10 pb-5"  tabindex="1">
                <img src="<?php echo $paths['schoolLogo'] . '/bcc-icon.png'; ?>" alt="" class="w-15 h-15 bg-white rounded-full md:w-22 md:h-22">
                </div>

                <div id="profile-button" class="nav-button flex items-center py-3 px-10 rounded-lg transition duration-200 hover:bg-[#B80000] sm:py-2" 
                tabindex="2"><img src="<?php echo $paths['navigation_icons'] . '/profile.png'; ?>" alt="Profile Button" class="w-9 h-9 lg:w-10 lg:h-10">
                    <button class="ml-5 text-md font-semibold text-white hover:text-gray-200 focus:outline-none lg:text-lg">Profile</button></div>

                <div id="home-button" class="nav-button flex items-center py-3 px-10 rounded-lg transition duration-200 hover:bg-[#B80000] sm:py-2"
                tabindex="3" ><img src="<?php echo $paths['navigation_icons'] . '/home.png'; ?>" alt="Home Button" class="w-9 h-9 lg:w-10 lg:h-10">
                <button class="ml-5 text-md font-semibold text-white hover:text-gray-200 focus:outline-none lg:text-lg">Home</button></div>

                <div id="courses-button" class="nav-button flex items-center py-3 px-10 rounded-lg transition duration-200 hover:bg-[#B80000] sm:py-2"
                tabindex="4" ><img src="<?php echo $paths['registrar_icons'] . '/courses.png'; ?>" alt="Courses Button" class="w-9 h-9 lg:w-10 lg:h-10">
                    <button class="ml-5 text-md font-semibold text-white hover:text-gray-200 focus:outline-none lg:text-lg">Courses</button></div>


                <div id="classes-button" class="nav-button flex items-center py-3 px-10 rounded-lg transition duration-200 hover:bg-[#B80000] sm:py-2"
                tabindex="5" ><img src="<?php echo $paths['registrar_icons'] . '/classes.png'; ?>" alt="Classes Button" class="w-9 h-9 lg:w-10 lg:h-10">
                    <button class="ml-5 text-md font-semibold text-white hover:text-gray-200 focus:outline-none lg:text-lg">Classes</button></div>

                <div id="sections-button" class="nav-button flex items-center py-3 px-10 rounded-lg transition duration-200 hover:bg-[#B80000] sm:py-2"
                tabindex="6"><img src="<?php echo $paths['registrar_icons'] . '/sections.png'; ?>" alt="Sections Button" class="w-9 h-9 lg:w-10 lg:h-10">
                    <button class="ml-5 text-md font-semibold text-white hover:text-gray-200 focus:outline-none lg:text-lg">Sections</button></div>

                <div id="subjects-button" class="nav-button flex items-center py-3 px-10 rounded-lg transition duration-200 hover:bg-[#B80000] sm:py-2"
                tabindex="7"><img src="<?php echo $paths['registrar_icons'] . '/subjects.png'; ?>" alt="Subjects Button" class="w-9 h-9 lg:w-10 lg:h-10">
                    <button class="ml-5 text-md font-semibold text-white hover:text-gray-200 focus:outline-none lg:text-lg">Subjects</button></div>

                <div id="settings-button" class="nav-button flex items-center py-3 px-10 rounded-lg transition duration-200 hover:bg-[#B80000] sm:py-2"
                tabindex="8"><img src="<?php echo $paths['navigation_icons'] . '/settings.png'; ?>" alt="Settings Button" class="w-9 h-9 lg:w-10 lg:h-10">
                    <button class="ml-5 text-md font-semibold text-white hover:text-gray-200 focus:outline-none lg:text-lg">Settings</button></div>

                <div id="messages-button" class="nav-button flex items-center py-3 px-10 rounded-lg transition duration-200 hover:bg-[#B80000] sm:py-2"
                tabindex="9"><img src="<?php echo $paths['navigation_icons'] . '/messages.png'; ?>" alt="Messages Button" class="w-9 h-9 lg:w-10 lg:h-10">
                    <button class="ml-5 text-md font-semibold text-white hover:text-gray-200 focus:outline-none lg:text-lg">Messages</button></div>

                <div id="logout-button" class="nav-button flex items-center py-3 px-10 rounded-lg transition duration-200 hover:bg-[#B80000] sm:py-2"
                tabindex="10" ><img src="<?php echo $paths['navigation_icons'] . '/logout.png'; ?>" alt="Log out Button" class="w-9 h-9 lg:w-10 lg:h-10">
                    <button class="ml-5 text-md font-semibold text-white hover:text-gray-200 focus:outline-none lg:text-lg">Logout</button></div>
            </nav>
        </div>



        <!-- Main Content -->
        <div class="flex-1 p-10 bg-gray-200">
            <div class="lg:hidden">
                <button onclick="toggleSidebar()" class="text-gray-800 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
        
        </div>

            <div id="home" class="content-section hidden">
                <h1 class="text-3xl font-bold pt-20">Welcome to Home</h1>
                <p class="mt-4 text-gray-600">Here is your home page content.</p>
            </div>


            <div id="profile" class="content-section hidden">
            <h1 class="text-3xl font-bold pt-20">Welcome to profile</h1>
            <p class="mt-4 text-gray-600">Here is your profile page content.</p>
            </div>


            <div id="courses" class="content-section hidden">
                <iframe src="<?php echo $paths['courses']; ?>" class="w-full h-full border-0" frameborder="0"></iframe>
            </div>


            <div id="classes" class="content-section hidden">
                <iframe src="<?php echo $paths['classes']; ?>" class="w-full h-full border-0" frameborder="0"></iframe>
            </div>

            <div id="sections" class="content-section hidden ">
                <iframe src="<?php echo $paths['sections']; ?>" class="w-full h-full border-0" frameborder="0"></iframe>
            </div>

            <div id="subjects" class="content-section hidden">
                <iframe src="<?php echo $paths['subjects']; ?>" class="w-full h-full border-0" frameborder="0"></iframe>
            </div>

            <div id="settings" class="content-section hidden">
                <h1 class="text-3xl font-bold">Settings</h1>
                <p class="mt-4 text-gray-600">Here are your settings.</p>
            </div>
            <div id="messages" class="content-section hidden">
                <h1 class="text-3xl font-bold">Messages</h1>
                <p class="mt-4 text-gray-600">Here are your messages.</p>
            </div>
            <div id="logout" class="content-section hidden">
                <h1 class="text-3xl font-bold">Logout</h1>
                <p class="mt-4 text-gray-600">You have logged out.</p>
            </div>
        </div>
    </div>

    <script>

function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    if (sidebar.classList.contains('-translate-x-full')) {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
    } else {
        sidebar.classList.remove('translate-x-0');
        sidebar.classList.add('-translate-x-full');
    }
}

// Get all buttons with the class 'nav-button'
const buttons = document.querySelectorAll('.nav-button');

buttons.forEach(button => {
    button.addEventListener('click', function() {
        // Remove the active class from all buttons
        buttons.forEach(btn => btn.classList.remove('bg-[#B80000]'));

        // Add the active class to the clicked button
        this.classList.add('bg-[#B80000]');
    });
});

// Function to handle button clicks
function setupButtonClickListener(buttonId, sectionId) {
    document.getElementById(buttonId).addEventListener('click', function() {
        // Hide all content sections
        document.querySelectorAll('.content-section').forEach(function(section) {
            section.classList.add('hidden');
        });
        // Show the targeted section
        document.getElementById(sectionId).classList.remove('hidden');
        // Store the active section in localStorage
        localStorage.setItem('activeSection', sectionId);
    });
}

// Function to show the active section based on stored state
function showActiveSection() {
    const activeSection = localStorage.getItem('activeSection');
    if (activeSection) {
        // Hide all sections
        document.querySelectorAll('.content-section').forEach(function(section) {
            section.classList.add('hidden');
        });
        // Show the active section
        document.getElementById(activeSection).classList.remove('hidden');
    }
}

// Set up event listeners for all buttons
const buttonSectionMapping = {
    'home-button': 'home',
    'courses-button': 'courses',
    'classes-button': 'classes',
    'sections-button': 'sections',
    'subjects-button': 'subjects',
    'settings-button': 'settings',
    'messages-button': 'messages',
    'logout-button': 'logout',
    'profile-button': 'profile'
};

Object.keys(buttonSectionMapping).forEach(function(buttonId) {
    setupButtonClickListener(buttonId, buttonSectionMapping[buttonId]);
});

// Call the function to show the active section when the page loads
document.addEventListener('DOMContentLoaded', function() {
    showActiveSection();
});


    </script>

</body>
</html>
