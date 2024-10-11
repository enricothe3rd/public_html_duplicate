<?php
require 'Instructor_subject.php'; // Adjust the path if necessary

// Instantiate the InstructorSubject class
$instructorSubject = new InstructorSubject();

// Fetch departments
$departments = $instructorSubject->getDepartments();

// Fetch instructors
$instructors = $instructorSubject->getInstructors();


$message = isset($_GET['message']) ? $_GET['message'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Instructor Data</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet"> <!-- Font Awesome -->
    <script>
        async function fetchCourses(departmentId) {
            try {
                const response = await fetch(`fetch_courses.php?department_id=${departmentId}`);
                const data = await response.json();
                let courseDropdown = document.getElementById('course');
                courseDropdown.innerHTML = '<option>Select a course</option>';
                data.forEach(course => {
                    courseDropdown.innerHTML += `<option value="${course.id}">${course.course_name}</option>`;
                });
            } catch (error) {
                console.error('Error fetching courses:', error);
            }
        }

        async function fetchSections(courseId) {
            try {
                const response = await fetch(`fetch_sections.php?course_id=${courseId}`);
                const data = await response.json();
                let sectionDropdown = document.getElementById('section');
                sectionDropdown.innerHTML = '<option>Select a section</option>';
                data.forEach(section => {
                    sectionDropdown.innerHTML += `<option value="${section.id}">${section.name}</option>`;
                });
            } catch (error) {
                console.error('Error fetching sections:', error);
            }
        }

        async function fetchSubjects(sectionId, semesterId) {
            try {
                const response = await fetch(`fetch_subjects.php?section_id=${sectionId}&semester_id=${semesterId}`);
                const data = await response.json();
                let subjectDropdown = document.getElementById('subject');
                subjectDropdown.innerHTML = '<option>Select a subject</option>';
                data.forEach(subject => {
                    subjectDropdown.innerHTML += `<option value="${subject.id}">${subject.title}</option>`;
                });
            } catch (error) {
                console.error('Error fetching subjects:', error);
            }
        }

        function updateSubjects() {
            const sectionId = document.getElementById('section').value;
            const semesterId = document.getElementById('semester').value;
            if (sectionId && semesterId) {
                fetchSubjects(sectionId, semesterId);
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div  class="container mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg max-w-2xl">
    <button 
            onclick="goBack()" 
            class="mb-4 px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition duration-200 flex items-center"
        >
            <i class="fas fa-arrow-left mr-2"></i> <!-- Arrow icon -->
            Back
        </button>
        <h1 class="text-2xl font-semibold text-red-800 mb-4">Assign Subject to Instructor</h1>
        
        
        <?php if (isset($_GET['message'])): ?>
    <?php if ($_GET['message'] == 'success'): ?>
        <div class="mt-4 bg-green-200 text-green-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Success</h2>
            <p>The instructor was added to the subject successfully.</p>
        </div>
        <script>
            // Set a timeout to redirect after 3 seconds
            setTimeout(function() {
                window.location.href = 'read_instructor_subject.php'; // Adjust the redirection as needed
            }, 3000);
        </script>

    <?php elseif ($_GET['message'] == 'error'): ?>
        <div class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>Failed to add the subject to the instructor. Please try again.</p>
        </div>

    <?php elseif ($_GET['message'] == 'invalid_name'): ?>
        <div id="error-message" class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>The subject name is invalid. Please use only alphanumeric characters and hyphens.</p>
        </div>
        <script>
            // Set a timeout to hide the error message after 3 seconds
            setTimeout(function() {
                var errorMessage = document.getElementById('error-message');
                if (errorMessage) {
                    errorMessage.style.display = 'none'; // Hide the message
                }
            }, 3000); // Hide after 3000 milliseconds (3 seconds)
        </script>

    <?php elseif ($_GET['message'] == 'invalid_instructor'): ?>
        <div id="error-message" class="mt-4 bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>The selected instructor does not exist. Please select a valid instructor.</p>
        </div>
        <script>
            // Set a timeout to hide the error message after 3 seconds
            setTimeout(function() {
                var errorMessage = document.getElementById('error-message');
                if (errorMessage) {
                    errorMessage.style.display = 'none'; // Hide the message
                }
            }, 3000); // Hide after 3000 milliseconds (3 seconds)
        </script>
    <?php endif; ?>
<?php endif; ?>

<form method="POST" action="process_instructor.php" class="space-y-4" id="instructorForm">
    <!-- Instructor Information -->
    <div class="mb-4">
        <label for="instructor" class="block text-red-700 font-medium">
            <i class="fas fa-user-tie mr-2"></i> Select Instructor: <span class="text-red-600">*</span>
        </label>
        <select id="instructor" name="instructor" class="bg-red-50 block w-full px-3 py-3 text-red-800 border-red-300 rounded-md shadow-sm focus:outline-none focus:bg-red-100 focus:border-red-500 sm:text-sm" required>
            <option>Select an instructor</option>
            <?php foreach ($instructors as $inst): ?>
                <option value="<?= $inst['id'] ?>"><?= $inst['first_name'] ?> <?= $inst['last_name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Department Dropdown -->
    <div class="mb-4">
        <label for="department" class="block text-red-700 font-medium">
            <i class="fas fa-building mr-2"></i> Select Department: <span class="text-red-600">*</span>
        </label>
        <select id="department" name="department" onchange="fetchCourses(this.value); checkFormCompletion();" class="bg-red-50 block w-full px-3 py-3 text-red-800 border-red-300 rounded-md shadow-sm focus:outline-none focus:bg-red-100 focus:border-red-500 sm:text-sm" required>
            <option>Select a department</option>
            <?php foreach ($departments as $department): ?>
                <option value="<?= $department['id'] ?>"><?= $department['name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Course Dropdown -->
    <div class="mb-4">
        <label for="course" class="block text-red-700 font-medium">
            <i class="fas fa-book mr-2"></i> Select Course: <span class="text-red-600">*</span>
        </label>
        <select id="course" name="course" onchange="fetchSections(this.value); checkFormCompletion();" class="bg-red-50 block w-full px-3 py-3 text-red-800 border-red-300 rounded-md shadow-sm focus:outline-none focus:bg-red-100 focus:border-red-500 sm:text-sm" required>
            <option>Select a course</option>
        </select>
    </div>

    <!-- Section Dropdown -->
    <div class="mb-4">
        <label for="section" class="block text-red-700 font-medium">
            <i class="fas fa-chalkboard-teacher mr-2"></i> Select Section: <span class="text-red-600">*</span>
        </label>
        <select id="section" name="section" onchange="updateSubjects(); checkFormCompletion();" class="bg-red-50 block w-full px-3 py-3 text-red-800 border-red-300 rounded-md shadow-sm focus:outline-none focus:bg-red-100 focus:border-red-500 sm:text-sm" required>
            <option>Select a section</option>
        </select>
    </div>

<!-- Semester Dropdown -->
<div class="mb-4">
    <label for="semester" class="block text-red-700 font-medium">
        <i class="fas fa-calendar-alt mr-2"></i> Select Semester: *
    </label>
    <select id="semester" name="semester" onchange="updateSubjects()" class="bg-red-50 block w-full px-3 py-3 text-red-800 border-red-300 rounded-md shadow-sm focus:outline-none focus:bg-red-100 focus:border-red-500 sm:text-sm" required>
        <option value="">Select Semester</option> <!-- Default value -->
        <option value="1">1st Semester</option>
        <option value="2">2nd Semester</option>
    </select>
</div>


    <!-- Subject Dropdown -->
    <div class="mb-4">
        <label for="subject" class="block text-red-700 font-medium">
            <i class="fas fa-book-open mr-2"></i> Select Subject: <span class="text-red-600">*</span>
        </label>
        <select id="subject" name="subject" class="bg-red-50 block w-full px-3 py-3 text-red-800 border-red-300 rounded-md shadow-sm focus:outline-none focus:bg-red-100 focus:border-red-500 sm:text-sm" required>
            <option>Select a subject</option>
        </select>
    </div>

    <!-- Submit Button -->
    <button type="submit" id="submitButton" class="w-full bg-red-700 text-white py-2 px-4 rounded-lg hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-blue-500" disabled style="opacity: 0.5;">
        <i class="fas fa-plus-circle mr-2"></i> Assign Subject
    </button>
</form>
<script>
    // Function to check if all required fields are filled
    function checkFormCompletion() {
        const fields = [
            document.getElementById('instructor'),
            document.getElementById('department'),
            document.getElementById('course'),
            document.getElementById('section'),
            document.getElementById('semester'),
            document.getElementById('subject')
        ];

        // Check if all fields have a valid selection
        const allFilled = fields.every(field => {
            // Ensure the field value is neither empty nor the default options
            return field.value !== '' &&
                field.value !== 'Select an instructor' &&
                field.value !== 'Select a department' && 
                field.value !== 'Select a course' && 
                field.value !== 'Select a section' && 
                field.value !== 'Select a subject' && 
                field.value !== 'Select Semester'; // Check for the semester default
        });

        const submitButton = document.getElementById('submitButton');
        if (allFilled) {
            submitButton.disabled = false;
            submitButton.style.opacity = 1; // Reset opacity
        } else {
            submitButton.disabled = true;
            submitButton.style.opacity = 0.5; // Set low opacity
        }
    }

    // Attach change event listeners to each select element
    document.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', checkFormCompletion);
    });

    // Call checkFormCompletion on page load to initialize button state
    document.addEventListener('DOMContentLoaded', checkFormCompletion);
</script>


    </div>
</body>
<script>
        function goBack() {
            window.history.back(); // Navigates to the previous page
        }
    </script>
</html>
