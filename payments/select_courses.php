<?php
session_start();
require '../db/db_connection3.php'; // Adjust the filename as needed

try {
    $db = Database::connect();

    // Prepare and execute the query to fetch departments
    $stmt = $db->prepare("SELECT id, name FROM departments");
    $stmt->execute();

    // Fetch all departments
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

try {
    // Establish a single database connection
    $db = Database::connect();

    // Prepare and execute the query to fetch semesters
    $stmt = $db->prepare("SELECT id, semester_name FROM semesters");
    $stmt->execute();
    $semesters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare and execute the query to fetch school years
    $stmt = $db->prepare("SELECT id, year FROM school_years");
    $stmt->execute();
    $school_years = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}



$user_email = $_SESSION['user_email'] ?? '';
if (empty($user_email)) {
    echo "User email is not set in the session.";
    exit;
}

// Check if student_number is set in the session
if (isset($_SESSION['student_number'])) {
    $student_number = $_SESSION['student_number'];
} else {
    echo "Student number is not set in the session.";
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Course</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<div class="max-w-lg mx-auto mt-10 p-8 border border-red-300 rounded-lg shadow-md bg-white">
    <h2 class="text-3xl font-bold text-red-800 mb-6">Select Your Course</h2>
    <form id="selectionForm" method="POST" action="submit_selection.php">
        
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-red-700">Email Address</label>
            <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                <i class="fas fa-envelope px-3 text-red-500"></i> <!-- Email icon -->
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($student_number) ?>" required 
                       class="w-full h-12 px-3 py-2 bg-gray-100 cursor-not-allowed opacity-75 focus:outline-none" readonly>
            </div>
        </div>

        <div class="mb-4">
            <label for="school_year" class="block text-sm font-medium text-red-700">Select School Year</label>
            <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                <i class="fas fa-calendar-alt px-3 text-red-500"></i> <!-- School Year icon -->
                <select name="school_year" id="school_year" class="w-full h-12 px-3 bg-red-50 text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 rounded-md transition duration-200" onchange="fetchCourses()">
                    <option value="">Select School Year</option>
                    <?php foreach ($school_years as $year): ?>
                        <option value="<?php echo htmlspecialchars($year['id']); ?>">
                            <?php echo htmlspecialchars($year['year']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label for="semester" class="block text-sm font-medium text-red-700">Select Semester</label>
            <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                <i class="fas fa-clock px-3 text-red-500"></i> <!-- Semester icon -->
                <select name="semester" id="semester" class="w-full h-12 px-3 bg-red-50 text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 rounded-md transition duration-200" onchange="fetchCourses()">
                    <option value="">Select Semester</option>
                    <?php foreach ($semesters as $semester): ?>
                        <option value="<?php echo htmlspecialchars($semester['id']); ?>">
                            <?php echo htmlspecialchars($semester['semester_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label for="department" class="block text-sm font-medium text-red-700">Select Department</label>
            <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                <i class="fas fa-building px-3 text-red-500"></i> <!-- Department icon -->
                <select name="department" id="department" class="w-full h-12 px-3 bg-red-50 text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 rounded-md transition duration-200" onchange="fetchCourses()">
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?php echo htmlspecialchars($department['id']); ?>">
                            <?php echo htmlspecialchars($department['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label for="course" class="block text-sm font-medium text-red-700">Select Course</label>
            <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                <i class="fas fa-book px-3 text-red-500"></i> <!-- Course icon -->
                <select name="course" id="course" class="w-full h-12 px-3 bg-red-50 text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 rounded-md transition duration-200" onchange="fetchSections(this.value)">
                    <option value="">Select Course</option>
                </select>
            </div>
        </div>

        <div id="sectionsDisplay" class="mb-4"></div> <!-- Section to display corresponding sections -->
        <div id="subjectsDisplay" class="mt-4"></div> <!-- Placeholder for subjects -->
        <div id="scheduleDisplay" class="mt-4"></div> <!-- Container for displaying schedules -->

        <button type="submit" class="mt-6 w-full bg-red-700 text-white py-3 px-4 rounded-md hover:bg-red-800 transition duration-200 focus:outline-none focus:ring-2 focus:ring-red-500">
            <i class="fas fa-paper-plane mr-2"></i> <!-- Submit icon -->
            Submit
        </button>

        <!-- <button id="submitButton" type="button" class="px-4 py-2 bg-blue-500 text-white">Alert Selected Data</button> -->

    </form>
</div>



<script>// This function fetches courses based on the selected department, school year, and semester
function fetchCourses() {
    const departmentId = document.getElementById('department').value;
    const schoolYearId = document.getElementById('school_year').value;
    const semesterId = document.getElementById('semester').value;

    const courseDropdown = document.getElementById('course');
    courseDropdown.innerHTML = '<option value="">Loading...</option>';

    // Clear sections, subjects, and schedules whenever department changes
    document.getElementById('sectionsDisplay').innerHTML = ''; 
    document.getElementById('subjectsDisplay').innerHTML = ''; 
    document.getElementById('scheduleDisplay').innerHTML = ''; 

    if (departmentId !== "" && schoolYearId !== "" && semesterId !== "") {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "fetch_courses.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    const courses = JSON.parse(xhr.responseText);
                    courseDropdown.innerHTML = '<option value="">Select Course</option>';

                    courses.forEach(function (course) {
                        const option = document.createElement('option');
                        option.value = course.id;
                        option.text = course.course_name;
                        courseDropdown.appendChild(option);
                    });
                } catch (e) {
                    console.error("Error parsing JSON:", e);
                    courseDropdown.innerHTML = '<option value="">Error loading courses</option>';
                }
            }
        };
        xhr.send("department_id=" + departmentId + "&school_year_id=" + schoolYearId + "&semester_id=" + semesterId);
    } else {
        courseDropdown.innerHTML = '<option value="">Select Course</option>';
    }
}

// This function clears the department selection and resets displays when school year changes
function clearDepartment() {
    document.getElementById('department').selectedIndex = 0; // Reset department dropdown
    fetchCourses(); // Fetch courses with the cleared department
}

// This function clears the department selection and resets displays when semester changes
function clearDepartmentOnSemesterChange() {
    document.getElementById('department').selectedIndex = 0; // Reset department dropdown
    fetchCourses(); // Fetch courses with the cleared department
}

// Attach event listeners to the school year and semester dropdowns
document.getElementById('school_year').addEventListener('change', clearDepartment);
document.getElementById('semester').addEventListener('change', clearDepartmentOnSemesterChange);



function fetchSections(courseId) {
    const sectionsDisplay = document.getElementById('sectionsDisplay');
    sectionsDisplay.innerHTML = '<div class="text-gray-500">Loading sections...</div>';

    const schoolYearId = document.getElementById('school_year').value;
    const semesterId = document.getElementById('semester').value;

    if (courseId !== "") {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "fetch_sections.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    const sections = JSON.parse(xhr.responseText);
                    sectionsDisplay.innerHTML = ''; // Clear previous sections

                    if (sections.error) {
                        sectionsDisplay.innerHTML = `<div class="text-red-500">${sections.error}</div>`;
                    } else if (sections.length === 0) {
                        sectionsDisplay.innerHTML = '<div class="text-gray-500">No sections available for this course.</div>';
                    } else {
                        sections.forEach(function (section) {
                            const div = document.createElement('div');
                            div.className = "mb-4 p-4 border border-red-300 rounded-md shadow-sm bg-red-50";

                            const checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.name = 'sections[]';
                            checkbox.value = section.id;
                            checkbox.id = `section-${section.id}`;

                            const label = document.createElement('label');
                            label.htmlFor = `section-${section.id}`;
                            label.textContent = `Section: ${section.name} - ID: ${section.id}`;
                            label.className = "ml-2 text-red-700";

                            const subjectsContainer = document.createElement('div');
                            subjectsContainer.id = `subjects-${section.id}`;
                            subjectsContainer.className = "ml-4";

                            div.appendChild(checkbox);
                            div.appendChild(label);
                            div.appendChild(subjectsContainer);
                            sectionsDisplay.appendChild(div);

                            fetchSubjects(section.id, subjectsContainer);

                            checkbox.addEventListener('change', function () {
                                const subjectCheckboxes = subjectsContainer.querySelectorAll('input[type="checkbox"]');
                                subjectCheckboxes.forEach(function (subjectCheckbox) {
                                    subjectCheckbox.checked = checkbox.checked;
                                });
                            });
                        });
                    }
                } catch (e) {
                    console.error("Error parsing JSON:", e);
                    sectionsDisplay.innerHTML = '<div class="text-red-500">Error loading sections</div>';
                }
            }
        };
        xhr.send("course_id=" + courseId + "&school_year_id=" + schoolYearId + "&semester_id=" + semesterId);
    } else {
        sectionsDisplay.innerHTML = '';
    }
}
function fetchSubjects(sectionId, subjectsContainer) {
    subjectsContainer.innerHTML = '<div class="text-gray-500">Loading subjects...</div>';

    const schoolYearId = document.getElementById('school_year').value;
    const semesterId = document.getElementById('semester').value;

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "fetch_subjects.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const subjects = JSON.parse(xhr.responseText);
                subjectsContainer.innerHTML = '';

                if (subjects.error) {
                    subjectsContainer.innerHTML = `<div class="text-red-500">${subjects.error}</div>`;
                } else {
                    subjects.forEach(function (subject) {
                        const div = document.createElement('div');
                        div.className = "mb-4 p-4 border border-red-300 rounded-md shadow-sm bg-red-50";

                        const subjectCheckbox = document.createElement('input');
                        subjectCheckbox.type = 'checkbox';
                        subjectCheckbox.name = 'subjects[' + sectionId + '][]';
                        subjectCheckbox.value = subject.id;
                        subjectCheckbox.id = `subject-${subject.id}`;

                        const subjectLabel = document.createElement('label');
                        subjectLabel.htmlFor = `subject-${subject.id}`;
                        subjectLabel.textContent = `Subject: ${subject.title} - Code: ${subject.code}`;
                        subjectLabel.className = "ml-2 text-red-700";

                        div.appendChild(subjectCheckbox);
                        div.appendChild(subjectLabel);

                        const scheduleContainer = document.createElement('div');
                        scheduleContainer.id = `schedule-${subject.id}`;
                        scheduleContainer.className = "ml-4";

                        div.appendChild(scheduleContainer);
                        subjectsContainer.appendChild(div);

                        fetchSchedule(subject.id, scheduleContainer);

                        // Automatically check the section when a subject is selected
                        subjectCheckbox.addEventListener('change', function () {
                            const sectionCheckbox = document.getElementById(`section-${sectionId}`);
                            if (subjectCheckbox.checked) {
                                sectionCheckbox.checked = true; // Automatically select the section
                            }

                            const scheduleCheckboxes = scheduleContainer.querySelectorAll('input[type="checkbox"]');
                            scheduleCheckboxes.forEach(scheduleCheckbox => {
                                scheduleCheckbox.checked = subjectCheckbox.checked;
                            });
                        });
                    });
                }
            } catch (e) {
                console.error("Error parsing JSON:", e);
                subjectsContainer.innerHTML = '<div class="text-red-500">Error loading subjects</div>';
            }
        }
    };
    xhr.send("section_id=" + sectionId + "&school_year_id=" + schoolYearId + "&semester_id=" + semesterId);
}
function fetchSchedule(subjectId, container) {
    container.innerHTML = '<div class="text-gray-500">Loading schedule...</div>';

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "fetch_schedule.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const schedules = JSON.parse(xhr.responseText);
                container.innerHTML = '';

                if (schedules.error) {
                    container.innerHTML = `<div class="text-red-500">${schedules.error}</div>`;
                } else {
                    schedules.forEach(schedule => {
                        const div = document.createElement('div');
                        div.className = "mb-2 p-2 bg-red-50";

                        const scheduleCheckbox = document.createElement('input');
                        scheduleCheckbox.type = 'checkbox';
                        scheduleCheckbox.name = `schedules[${subjectId}][]`;
                        scheduleCheckbox.value = schedule.id;
                        scheduleCheckbox.id = `schedule-${schedule.id}`;

                        const scheduleLabel = document.createElement('label');
                        scheduleLabel.htmlFor = `schedule-${schedule.id}`;
                        scheduleLabel.textContent = `Day: ${schedule.day_of_week}, Time: ${schedule.start_time} - ${schedule.end_time}, Room: ${schedule.room}`;
                        scheduleLabel.className = "ml-2 text-red-700";

                        div.appendChild(scheduleCheckbox);
                        div.appendChild(scheduleLabel);

                        container.appendChild(div);

                        // Automatically check the subject and section when a schedule is selected
                        scheduleCheckbox.addEventListener('change', function () {
                            const subjectCheckbox = document.getElementById(`subject-${subjectId}`);
                            if (scheduleCheckbox.checked) {
                                subjectCheckbox.checked = true; // Automatically select the subject

                                // Automatically select the section
                                const sectionId = subjectCheckbox.name.match(/\d+/)[0]; // Extract the section ID from the name attribute
                                const sectionCheckbox = document.getElementById(`section-${sectionId
}`); sectionCheckbox.checked = true; 
// Automatically select the section
 } }); }); } } catch (e) { console.error("Error parsing JSON:", e); container.innerHTML = '<div class="text-red-500">Error loading schedule</div>'; } } else if (xhr.readyState === 4 && xhr.status !== 200) { container.innerHTML = '<div class="text-red-500">Error loading schedule</div>'; } }; xhr.send("subject_id=" + subjectId); }

// Function to gather and alert selected sections, subjects, and schedules
function gatherSelectedData() {
    // Arrays to store selected data
    let selectedSections = [];
    let selectedSubjects = [];
    let selectedSchedules = [];

    // Get all selected section checkboxes
    const sectionCheckboxes = document.querySelectorAll('input[name="sections[]"]:checked');
    sectionCheckboxes.forEach(function (sectionCheckbox) {
        // Push selected section ID to the array
        selectedSections.push(sectionCheckbox.value);

        // Find and gather the selected subjects for this section
        const subjectCheckboxes = document.querySelectorAll(`input[name="subjects[${sectionCheckbox.value}][]"]:checked`);
        subjectCheckboxes.forEach(function (subjectCheckbox) {
            // Push selected subject ID to the array
            selectedSubjects.push(subjectCheckbox.value);

            // Find and gather the selected schedules for this subject
            const scheduleCheckboxes = document.querySelectorAll(`input[name="schedules[${subjectCheckbox.value}][]"]:checked`);
            scheduleCheckboxes.forEach(function (scheduleCheckbox) {
                // Push selected schedule ID to the array
                selectedSchedules.push(scheduleCheckbox.value);
            });
        });
    });

    // Combine all the selected data into a readable format
    let alertMessage = "Selected Sections: " + selectedSections.join(", ") + "\n" +
                       "Selected Subjects: " + selectedSubjects.join(", ") + "\n" +
                       "Selected Schedules: " + selectedSchedules.join(", ");

    // Show the combined data in an alert
    alert(alertMessage);
}

// Add an event listener to a button (you can change this to any event trigger)
document.getElementById('submitButton').addEventListener('click', function (e) {
    e.preventDefault(); // Prevent form submission (if it's a form)
    gatherSelectedData(); // Call the function to gather and alert data
});


</script>

</body>
</html>
