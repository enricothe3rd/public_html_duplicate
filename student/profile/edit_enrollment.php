<?php
// Start session
session_start();

require '../../db/db_connection3.php';
$pdo = Database::connect();

// Check if a student number is provided via GET
if (isset($_GET['student_number'])) {
    $student_number = $_GET['student_number'];

    // Fetch the student's current enrollment data
    $stmt = $pdo->prepare("
        SELECT e.student_number, 
               e.firstname, 
               e.middlename, 
               e.lastname, 
               e.suffix, 
               e.student_type, 
               e.sex, 
               e.dob, 
               e.email, 
               e.contact_no, 
               e.address, 
               e.status,
               c.course_name, 
               c.id AS course_id, 
               s.name AS section_name, 
               s.id AS section_id, 
               d.name AS department_name, 
               d.id AS department_id,
               se.id AS subject_enrollment_id  -- Add this line to fetch the subject_enrollment id
        FROM enrollments e
        LEFT JOIN subject_enrollments se ON e.student_number = se.student_number
        LEFT JOIN courses c ON se.course_id = c.id
        LEFT JOIN sections s ON se.section_id = s.id
        LEFT JOIN departments d ON c.department_id = d.id
        WHERE e.student_number = :student_number
    ");

    $stmt->bindParam(':student_number', $student_number, PDO::PARAM_STR);
    $stmt->execute();
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

    // If form is submitted, process the form data
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Debugging: Print POST data
        echo '<pre>';
        print_r($_POST); // To see all incoming POST data
        echo '</pre>';

        $firstname = $_POST['firstname'];
        $middlename = $_POST['middlename'];
        $lastname = $_POST['lastname'];
        $suffix = $_POST['suffix'];
        $student_type = $_POST['student_type'];
        $sex = $_POST['sex'];
        $dob = $_POST['dob'];
        $email = $_POST['email'];
        $contact_no = $_POST['contact_no'];
        $address = $_POST['address'];
        $status = $_POST['status'];
        $course_id = $_POST['course_id'];
        $section_id = $_POST['section_id'];
        $department_id = $_POST['department_id']; // Ensure this field is in your form
        $subject_id = $_POST['subject_id']; // Ensure this field is in your form
        $schedule_id = $_POST['schedule_id']; // Ensure this field is in your form
        $subject_enrollment_id = $_POST['subject_enrollment_id']; // New line to capture subject enrollment ID

        // Debugging: Print variable values before update
        echo '<pre>';
        echo "Before update: ";
        echo "firstname: $firstname, middlename: $middlename, lastname: $lastname, suffix: $suffix, student_type: $student_type, sex: $sex, dob: $dob, email: $email, contact_no: $contact_no, address: $address, status: $status, course_id: $course_id, section_id: $section_id, department_id: $department_id, subject_id: $subject_id, schedule_id: $schedule_id, subject_enrollment_id: $subject_enrollment_id";
        echo '</pre>';

        // Update the student's enrollment details
        $updateStmt = $pdo->prepare("
            UPDATE enrollments SET
                firstname = :firstname,
                middlename = :middlename,
                lastname = :lastname,
                suffix = :suffix,
                student_type = :student_type,
                sex = :sex,
                dob = :dob,
                email = :email,
                contact_no = :contact_no,
                address = :address,
                status = :status
            WHERE student_number = :student_number
        ");

        // Bind parameters
        $updateStmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);
        $updateStmt->bindParam(':middlename', $middlename, PDO::PARAM_STR);
        $updateStmt->bindParam(':lastname', $lastname, PDO::PARAM_STR);
        $updateStmt->bindParam(':suffix', $suffix, PDO::PARAM_STR);
        $updateStmt->bindParam(':student_type', $student_type, PDO::PARAM_STR);
        $updateStmt->bindParam(':sex', $sex, PDO::PARAM_STR);
        $updateStmt->bindParam(':dob', $dob, PDO::PARAM_STR);
        $updateStmt->bindParam(':email', $email, PDO::PARAM_STR);
        $updateStmt->bindParam(':contact_no', $contact_no, PDO::PARAM_STR);
        $updateStmt->bindParam(':address', $address, PDO::PARAM_STR);
        $updateStmt->bindParam(':status', $status, PDO::PARAM_STR);
        $updateStmt->bindParam(':student_number', $student_number, PDO::PARAM_STR);
        $updateStmt->execute();

        // Update subject_enrollments with course and section using student_number
        $subjectUpdateStmt = $pdo->prepare("
            UPDATE subject_enrollments SET
                course_id = :course_id,
                section_id = :section_id,
                department_id = :department_id,
                subject_id = :subject_id,
                schedule_id = :schedule_id
            WHERE student_number = :student_number  
        ");

        // Bind parameters
        $subjectUpdateStmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $subjectUpdateStmt->bindParam(':section_id', $section_id, PDO::PARAM_INT);
        $subjectUpdateStmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
        $subjectUpdateStmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
        $subjectUpdateStmt->bindParam(':schedule_id', $schedule_id, PDO::PARAM_INT);
        $subjectUpdateStmt->bindParam(':student_number', $student_number, PDO::PARAM_STR); // Bind the student number
       
        
        // Execute the update and check for errors
        if ($subjectUpdateStmt->execute()) {
            // Redirect after update
            header("Location: display_all_student.php");
            exit;
        } else {
            // Handle any errors
            echo "Error updating subject enrollments: " . implode(", ", $subjectUpdateStmt->errorInfo());
        }
    }
} else {
    echo "No student number provided.";
    exit;
}


// Fetch departments for the dropdown
try {
    $departmentStmt = $pdo->prepare("SELECT id, name FROM departments");
    $departmentStmt->execute();
    $departments = $departmentStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching departments: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Enrollment</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Edit Enrollment</title>
    <script>
    function fetchCourses(departmentId) {
        const courseSelect = document.getElementById('course_id');
        const sectionSelect = document.getElementById('section_id');
        const subjectSelect = document.getElementById('subject_id'); // Added to clear subjects

        // Clear previous options
        courseSelect.innerHTML = '<option value="">Select a Course</option>';
        sectionSelect.innerHTML = '<option value="">Select a Section</option>'; // Clear sections
        subjectSelect.innerHTML = '<option value="">Select a Subject</option>'; // Clear subjects

        if (departmentId) {
            fetch(`fetch_courses.php?department_id=${departmentId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(course => {
                        const option = new Option(course.course_name, course.id);
                        courseSelect.add(option);
                    });
                });
        }
    }

    function fetchSections(courseId) {
        const sectionSelect = document.getElementById('section_id');
        const subjectSelect = document.getElementById('subject_id'); // Added to clear subjects

        sectionSelect.innerHTML = '<option value="">Select a Section</option>'; // Clear previous sections
        subjectSelect.innerHTML = '<option value="">Select a Subject</option>'; // Clear subjects

        if (courseId) {
            fetch(`fetch_sections.php?course_id=${courseId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(section => {
                        const option = new Option(section.name, section.id);
                        sectionSelect.add(option);
                    });
                });
        }
    }

    function fetchSubjects(sectionId) {
        const subjectSelect = document.getElementById('subject_id');
        subjectSelect.innerHTML = '<option value="">Select a Subject</option>'; // Clear previous subjects

        if (sectionId) {
            console.log(`Fetching subjects for section ID: ${sectionId}`); // Debug log
            fetch(`fetch_subjects.php?section_id=${sectionId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Fetched subjects:', data); // Debug log
                    data.forEach(subject => {
                        const option = new Option(subject.title, subject.id);
                        subjectSelect.add(option);
                    });

                    // Automatically fetch schedules for the first subject if any are returned
                    if (data.length > 0) {
                        fetchSchedules(data[0].id); // Automatically fetch schedules for the first subject
                    }
                })
                .catch(error => console.error('There was a problem with the fetch operation:', error));
        }
    }

    function fetchSchedules(subjectId) {
    const scheduleSelect = document.getElementById('schedule_id');
    scheduleSelect.innerHTML = '<option value="">Select a Schedule</option>'; // Clear previous schedules

    if (subjectId) {
        fetch(`fetch_schedules.php?subject_id=${subjectId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                data.forEach(schedule => {
                    const optionText = `${schedule.day_of_week} - ${schedule.start_time} to ${schedule.end_time} (Room: ${schedule.room})`; // Include room number
                    const option = new Option(optionText, schedule.id);
                    scheduleSelect.add(option);
                });
            })
            .catch(error => console.error('There was a problem with the fetch operation:', error));
    }
}

    // Add event listeners for when sections or subjects are selected
    document.getElementById('section_id').addEventListener('change', function() {
        fetchSubjects(this.value);
    });

    document.getElementById('subject_id').addEventListener('change', function() {
        fetchSchedules(this.value);
    });
</script>
<head>
    <!-- Include Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-semibold text-red-800 mb-4">
            <i class="fas fa-user-edit mr-2 text-red-600"></i>
            Edit Enrollment
        </h1>
        
        <form method="POST" class="bg-white p-6 rounded-lg shadow-md space-y-4">
            <div>
                <label class="block font-semibold text-gray-700">
                    <i class="fas fa-id-card mr-2 text-red-600"></i>
                    Student Number
                </label>
                <input type="text" name="student_number" value="<?= htmlspecialchars($enrollment['student_number']) ?>" readonly class="bg-gray-200 border border-red-300 rounded w-full p-2">
            </div>

            <div>
                <label class="block font-semibold text-gray-700">
                    <i class="fas fa-user mr-2 text-red-600"></i>
                    First Name
                </label>
                <input type="text" name="firstname" value="<?= htmlspecialchars($enrollment['firstname']) ?>" class="border border-red-300 rounded w-full p-2 focus:border-red-600 focus:ring focus:ring-red-200">
            </div>

            <div>
                <label class="block font-semibold text-gray-700">
                    <i class="fas fa-user-circle mr-2 text-red-600"></i>
                    Middle Name
                </label>
                <input type="text" name="middlename" value="<?= htmlspecialchars($enrollment['middlename']) ?>" class="border border-red-300 rounded w-full p-2 focus:border-red-600 focus:ring focus:ring-red-200">
            </div>

            <div>
                <label class="block font-semibold text-gray-700">
                    <i class="fas fa-user-alt mr-2 text-red-600"></i>
                    Last Name
                </label>
                <input type="text" name="lastname" value="<?= htmlspecialchars($enrollment['lastname']) ?>" class="border border-red-300 rounded w-full p-2 focus:border-red-600 focus:ring focus:ring-red-200">
            </div>

            <div>
                <label class="block font-semibold text-gray-700">
                    <i class="fas fa-user-tag mr-2 text-red-600"></i>
                    Suffix
                </label>
                <input type="text" name="suffix" value="<?= htmlspecialchars($enrollment['suffix']) ?>" class="border border-red-300 rounded w-full p-2 focus:border-red-600 focus:ring focus:ring-red-200">
            </div>

            <div>
                <label class="block font-semibold text-gray-700">
                    <i class="fas fa-users mr-2 text-red-600"></i>
                    Student Type
                </label>
                <select name="student_type" class="border border-red-300 rounded w-full p-2 focus:border-red-600 focus:ring focus:ring-red-200">
                    <option value="">Select Student Type</option>
                    <option value="regular" <?= $enrollment['student_type'] == 'regular' ? 'selected' : '' ?>>Regular</option>
                    <option value="new student" <?= $enrollment['student_type'] == 'new student' ? 'selected' : '' ?>>New Student</option>
                    <option value="irregular" <?= $enrollment['student_type'] == 'irregular' ? 'selected' : '' ?>>Irregular</option>
                    <option value="summer" <?= $enrollment['student_type'] == 'summer' ? 'selected' : '' ?>>Summer</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-gray-700">
                    <i class="fas fa-venus-mars mr-2 text-red-600"></i>
                    Sex
                </label>
                <select name="sex" class="border border-red-300 rounded w-full p-2 focus:border-red-600 focus:ring focus:ring-red-200">
                    <option value="">Select Sex</option>
                    <option value="male" <?= $enrollment['sex'] == 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= $enrollment['sex'] == 'female' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-gray-700">
                    <i class="fas fa-calendar-alt mr-2 text-red-600"></i>
                    Date of Birth
                </label>
                <input type="date" name="dob" value="<?= htmlspecialchars($enrollment['dob']) ?>" class="border border-red-300 rounded w-full p-2 focus:border-red-600 focus:ring focus:ring-red-200">
            </div>

            <div>
                <label class="block font-semibold text-gray-700">
                    <i class="fas fa-envelope mr-2 text-red-600"></i>
                    Email
                </label>
                <input type="email" name="email" value="<?= htmlspecialchars($enrollment['email']) ?>" class="border border-red-300 rounded w-full p-2 focus:border-red-600 focus:ring focus:ring-red-200">
            </div>

            <div>
                <label class="block font-semibold text-gray-700">
                    <i class="fas fa-phone mr-2 text-red-600"></i>
                    Contact Number
                </label>
                <input type="text" name="contact_no" value="<?= htmlspecialchars($enrollment['contact_no']) ?>" class="border border-red-300 rounded w-full p-2 focus:border-red-600 focus:ring focus:ring-red-200">
            </div>

            <div>
                <label class="block font-semibold text-gray-700">
                    <i class="fas fa-home mr-2 text-red-600"></i>
                    Address
                </label>
                <input type="text" name="address" value="<?= htmlspecialchars($enrollment['address']) ?>" class="border border-red-300 rounded w-full p-2 focus:border-red-600 focus:ring focus:ring-red-200">
            </div>

            <div>
                <label class="block font-semibold text-gray-700">
                    <i class="fas fa-clipboard-check mr-2 text-red-600"></i>
                    Status
                </label>
                <input type="text" name="status" value="<?= htmlspecialchars($enrollment['status']) ?>" class="border border-red-300 rounded w-full p-2 focus:border-red-600 focus:ring focus:ring-red-200">
            </div>

       

            <div>
                <label class="block font-semibold text-gray-700">
                    <i class="fas fa-building mr-2 text-red-600"></i>
                    Department
                </label>
                <select name="department_id" id="departmentSelect" class="border border-red-300 rounded w-full p-2 focus:border-red-600 focus:ring focus:ring-red-200" onchange="fetchCourses(this.value)">
                    <option value="">Select a Department</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= $department['id']; ?>">
                            <?= htmlspecialchars($department['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-gray-700">
                    <i class="fas fa-book mr-2 text-red-600"></i>
                    Course
                </label>
                <select id="course_id" name="course_id" class="border border-red-300 rounded w-full p-2 focus:border-red-600 focus:ring focus:ring-red-200" onchange="fetchSections(this.value)">
                    <option value="">Select a Course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['id']; ?>" <?= $enrollment['course_id'] == $course['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($course['course_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-gray-700">
                    <i class="fas fa-th-list mr-2 text-red-600"></i>
                    Section
                </label>
                <select id="section_id" name="section_id" class="border border-red-300 rounded w-full p-2 focus:border-red-600 focus:ring focus:ring-red-200" onchange="fetchSubjects(this.value)">
                    <option value="">Select a Section</option>
                    <?php foreach ($sections as $section): ?>
                        <option value="<?= $section['id']; ?>" <?= $enrollment['section_id'] == $section['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($section['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-gray-700">
                    <i class="fas fa-book-open mr-2 text-red-600"></i>
                    Subject
                </label>
                <select id="subject_id" name="subject_id" class="border border-red-300 rounded w-full p-2 focus:border-red-600 focus:ring focus:ring-red-200">
                    <option value="">Select a Subject</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?= $subject['id']; ?>" <?= $enrollment['subject_id'] == $subject['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($subject['subject_title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-gray-700">Schedule</label>
                <select id="schedule_id" name="schedule_id" class="border border-red-300 rounded w-full p-2 focus:border-red-600 focus:ring focus:ring-red-200">
                    <option value="">Select a Schedule</option>
                    <?php foreach ($schedules as $schedule): ?>
                        <option value="<?= $schedule['id']; ?>" <?= $enrollment['schedule_id'] == $schedule['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($schedule['day_of_week']) . ' - ' . htmlspecialchars($schedule['start_time']) . ' to ' . htmlspecialchars($schedule['end_time'])  . htmlspecialchars($schedule['room']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="bg-red-600 text-white rounded p-2 mt-4 hover:bg-red-500">
                <i class="fas fa-save mr-2"></i>
                Save Changes
            </button>
        </form>
    </div>

 
</body>
