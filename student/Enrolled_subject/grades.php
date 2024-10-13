<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../../db/db_connection3.php';

$db = Database::connect();

session_start(); // Start session to access user data

// Get the instructor's email from the session
$email = $_SESSION['user_email'] ?? null;

// Initialize instructor_id
$instructor_id = null;

// Fetch the instructor's ID based on email
if ($email) {
    $stmt = $db->prepare("SELECT id FROM instructors WHERE email = ?");
    $stmt->execute([$email]);
    $instructor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($instructor) {
        $instructor_id = $instructor['id']; // Set the instructor_id if found
    } else {
        die("Instructor not found."); // Handle case where instructor does not exist
    }
} else {
    die("No instructor email found in session."); // Handle case where email is not in session
}

// Fetch subjects assigned to this instructor
$assigned_subjects = [];
if ($instructor_id) {
    $stmt = $db->prepare("
    SELECT 
        s.id, 
        s.title, 
        s.units, 
        sec.name AS section_name,  -- Add section name column
        c.course_name               -- Add course name column
    FROM instructor_subjects isub 
    JOIN subjects s ON isub.subject_id = s.id 
    JOIN sections sec ON s.section_id = sec.id  -- Join with sections table
    JOIN courses c ON sec.course_id = c.id      -- Join with courses table to get the course name
    WHERE isub.instructor_id = ?
");


    $stmt->execute([$instructor_id]);
    $assigned_subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}



$students = [];
$subject_id = null; // Initialize subject_id

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['subject_id'])) {
    $subject_id = $_POST['subject_id'];
    
// Prepare and execute the query
// Prepare and execute the query
$stmt = $db->prepare("
    SELECT 
        e.student_number, 
        e.firstname, 
        e.lastname, 
        e.suffix,              -- Add suffix column
        sub.title AS subject_title,  -- Add subject title column
        sec.name AS section_name      -- Add section name column
    FROM subject_enrollments se 
    JOIN enrollments e ON se.student_number = e.student_number 
    JOIN subjects sub ON se.subject_id = sub.id  -- Join with subjects table
    JOIN sections sec ON sub.section_id = sec.id  -- Join with sections table
    WHERE se.subject_id = ?
");

    $stmt->execute([$subject_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check if no students were enrolled in the selected subject
    if (empty($students)) {
        $_SESSION['error_message'] = "No students enrolled in that subject."; // Set error message
    } else {
        $_SESSION['success_message'] = "Students are enrolled in that subject."; // Set success message
    }


}





// Handle form submission for adding or updating grades
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_grade'])) {
    $student_number = $_POST['student_number'] ?? null; // Get student number from form
    $subject_id = $_POST['subject_id'] ?? null; // Get subject ID from form
    $prelim = $_POST['prelim'] ?? 0; // Ensure you get prelim value
    $midterm = $_POST['midterm'] ?? 0; // Ensure you get midterm value
    $finals = $_POST['finals'] ?? 0; // Ensure you get finals value

    // Debugging output to check received values
    error_log("Student Number: $student_number, Subject ID: $subject_id, Prelim: $prelim, Midterm: $midterm, Finals: $finals");

    // Validate grades only if they are set and not empty
    if (!empty($prelim) && ($prelim < 1 || $prelim > 5)) {
        $_SESSION['error_message'] = "Prelim grade must be between 1 and 5.";
    }

    if (!empty($midterm) && ($midterm < 1 || $midterm > 5)) {
        $_SESSION['error_message'] = "Midterm grade must be between 1 and 5.";
    }

    if (!empty($finals) && ($finals < 1 || $finals > 5)) {
        $_SESSION['error_message'] = "Finals grade must be between 1 and 5.";
    }

    // Proceed only if there is no error message
    if (!isset($_SESSION['error_message'])) {
        // Check if a grade entry already exists for this student and subject
        $stmt = $db->prepare("SELECT id FROM grades WHERE student_number = ? AND subject_id = ?");
        $stmt->execute([$student_number, $subject_id]);
        $existing_grade = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get the number of units for the subject
        $stmt = $db->prepare("SELECT units FROM subjects WHERE id = ?");
        $stmt->execute([$subject_id]);
        $subject = $stmt->fetch(PDO::FETCH_ASSOC);
        $units = $subject['units'] ?? 0; // Assuming the units column in subjects table

// Convert grades and units to floats
$prelim = floatval($prelim);
$midterm = floatval($midterm);
$finals = floatval($finals);
$units = floatval($units); // Convert units to float

// Calculate total grade using the overall units
if ($units > 0) { // Ensure units are valid for division
    // Calculate the total grades based on the weights
    $total_grades = ($prelim * 0.3) + ($midterm * 0.3) + ($finals * 0.4);
} else {
    $total_grades = 0; // If no units, set total grades to 0 to avoid division by zero
}


        if ($existing_grade) {
            // Update existing grade
            $stmt = $db->prepare("UPDATE grades SET prelim = ?, midterm = ?, finals = ?, total_grade = ? WHERE id = ?");
            $stmt->execute([$prelim, $midterm, $finals, $total_grades, $existing_grade['id']]);
            $_SESSION['success_message'] = "Grades successfully updated."; // Set success message in session
        } else {
            // Insert new grade
            $stmt = $db->prepare("INSERT INTO grades (student_number, subject_id, prelim, midterm, finals, total_grade) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$student_number, $subject_id, $prelim, $midterm, $finals, $total_grades]);
            $_SESSION['success_message'] = "Grades successfully added."; // Set success message in session
        }
    }
}



// Include the message handler to display messages
include '../../message/message_handler.php';



// Handle grade deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_grade_id'])) {
    $grade_id = $_POST['grade_id'];

    // Prepare the delete statement
    $stmt = $db->prepare("DELETE FROM grades WHERE id = ?");
    if ($stmt->execute([$grade_id])) {
        $_SESSION['message'] = "Grade ID $grade_id deleted successfully."; // Set success message
        error_log("Grade ID $grade_id deleted successfully.");
    } else {
        $_SESSION['message'] = "Failed to delete Grade ID $grade_id."; // Set error message
        error_log("Failed to delete Grade ID $grade_id.");
    }

  
}

// Display success or error message if available
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after displaying it
}

$grades = $db->prepare("
    SELECT 
        g.id, 
        e.student_number, 
        e.firstname, 
        e.lastname, 
             e.suffix, 
        sub.title AS subject_title, 
        g.prelim, 
        g.midterm, 
        g.finals, 
        g.total_grade,  -- Add total_grade column here
        sub.units, 
        sec.name AS section_name  -- Add section name column
    FROM grades g 
    JOIN enrollments e ON g.student_number = e.student_number 
    JOIN subjects sub ON g.subject_id = sub.id 
    JOIN sections sec ON sub.section_id = sec.id  -- Join with sections table
");

$grades->execute();
$grades = $grades->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <title>Instructor Manage Grades</title>
</head>
<body class=" font-sans leading-normal tracking-normal">
    <div class="">
        <h1 class="text-2xl font-semibold text-red-800 mb-4">Manage Grades</h1>
<!-- Form for Selecting Subject -->
<form method="POST" action="" class="mb-6">
    <h2 class="text-xl font-semibold mb-4 text-red-800">Select Subject to View Students</h2>
    <div class="mb-4">
        <label for="subject_id" class="block px-3 text-red-700 font-medium">Subject:</label>
        <select name="subject_id" class="border rounded p-2 w-full border-red-300 outline-none" required>
            <option value="" disabled selected>Select a subject</option>
            <?php foreach ($assigned_subjects as $subject): ?>
                <option value="<?= $subject['id']; ?>" <?= isset($subject_id) && $subject_id == $subject['id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars(strtoupper(($subject['title'] ?? '') . ' - ' . ($subject['section_name'] ?? ''). ' - ' . ($subject['course_name'] ?? ''))); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="submit" class="bg-red-700 text-white rounded py-2 px-4 hover:bg-red-800">View Students</button>
</form>


<!-- If subject_id is selected, display students -->
<?php if (!empty($students)): ?>
    <form method="POST" action="" class="mb-6" id="gradeForm">
        <h2 class="text-xl font-semibold mb-4">Add Grades</h2>
        <input type="hidden" name="subject_id" value="<?= htmlspecialchars($subject_id ?? ''); ?>">

        <div class="mb-4">
            <label for="student_number" class="block px-3 text-red-700 font-medium">
                <i class="fas fa-user"></i> Student:
            </label>
            <select name="student_number" class="border border-red-300 outline-none rounded p-2 w-full" required>
                <?php foreach ($students as $student): ?>
                    <option value="<?= htmlspecialchars($student['student_number'] ?? ''); ?>">
                        <?= htmlspecialchars(strtoupper(($student['firstname'] ?? '') . ' ' . ($student['lastname'] ?? '') . ' ' . ($student['suffix'] ?? ''))
                                            . ' , ' . ($student['section_name'] ?? '') . ' , ' . ($student['subject_title'] ?? '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>


        
       

        <div class="mb-4">
            <label for="prelim" class="block px-3 text-red-700 font-medium">
                <i class="fas fa-pencil-alt"></i> Prelim (1.0-5.0):
            </label>
            <div class="flex items-center">
                <input type="number" name="prelim" id="prelim" step="0.01" class="border border-red-300 rounded p-2 w-full focus:border-red-500 outline-none" value="<?= htmlspecialchars($_POST['prelim'] ?? ''); ?>" >
            </div>
        </div>

        <div class="mb-4">
            <label for="midterm" class="block px-3 text-red-700 font-medium">
                <i class="fas fa-pencil-alt"></i> Midterm (1.0-5.0):
            </label>
            <div class="flex items-center">
                <input type="number" name="midterm" id="midterm" step="0.01" class="border border-red-300 rounded p-2 w-full focus:border-red-500 outline-none" value="<?= htmlspecialchars($_POST['midterm'] ?? ''); ?>" >
            </div>
        </div>

        <div class="mb-4">
            <label for="finals" class="block px-3 text-red-700 font-medium">
                <i class="fas fa-pencil-alt"></i> Finals (1.0-5.0):
            </label>
            <div class="flex items-center">
                <input type="number" name="finals" id="finals" step="0.01" class="border border-red-300 rounded p-2 w-full focus:border-red-500 outline-none" value="<?= htmlspecialchars($_POST['finals'] ?? ''); ?>" >
            </div>
        </div>

        <button type="submit" name="add_grade" class="bg-red-700 text-white rounded py-2 px-4 hover:bg-red-800">
            <i class="fas fa-plus"></i> Add Grades
        </button>
    </form>
<?php endif; ?>


        <!-- Table for Displaying Grades -->
        <h2 class="text-xl font-semibold mb-4 text-red-700">Grades List</h2>
        <table class="w-full border-collapse  shadow-md rounded-lg">
            <thead class="bg-red-800">
                <tr>
                    <th class="px-4 py-4 border-b text-left text-white">Student Number</th>
                    <th class="px-4 py-4 border-b text-left text-white">Student Name</th>
                    <th class="px-4 py-4 border-b text-left text-white">Section Name</th>
                    <th class="px-4 py-4 border-b text-left text-white">Subject</th>
                    <th class="px-4 py-4 border-b text-left text-white">Units</th>
                    <th class="px-4 py-4 border-b text-left text-white">Prelim</th>
                    <th class="px-4 py-4 border-b text-left text-white">Midterm</th>
                    <th class="px-4 py-4 border-b text-left text-white">Finals</th>
                    <th class="px-4 py-4 border-b text-left text-white">Total</th>
                    <th class="px-4 py-4 border-b text-left text-white">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grades as $grade): ?>
                    <tr class="border-b bg-red-50 hover:bg-red-200">
                        <td class="px-4 py-4"><?= htmlspecialchars($grade['student_number'] ?? ''); ?></td>
                        <td class="px-4 py-4"><?= htmlspecialchars(strtoupper($grade['firstname'] . ' ' . $grade['suffix']. ' ' . $grade['lastname'])); ?></td>

                        <td class="px-4 py-4"><?= htmlspecialchars($grade['section_name'] ?? ''); ?></td>
                        <td class="px-4 py-4"><?= htmlspecialchars($grade['subject_title'] ?? ''); ?></td>
                        <td class="px-4 py-4"><?= htmlspecialchars($grade['units'] ?? ''); ?></td>
                        <td class="px-4 py-4"><?= htmlspecialchars($grade['prelim'] ?? ''); ?></td>
                        <td class="px-4 py-4"><?= htmlspecialchars($grade['midterm'] ?? ''); ?></td>
                        <td class="px-4 py-4"><?= htmlspecialchars($grade['finals'] ?? ''); ?></td>
                        <td class="px-4 py-4"><?= htmlspecialchars($grade['total_grade'] ?? ''); ?></td>
                        <td class="px-4 py-4">
                            <form method="POST" action="" class="inline">
                                <input type="hidden" name="grade_id" value="<?= htmlspecialchars($grade['id'] ?? ''); ?>">
            <button  class="text-red-600 hover:text-red-800 delete-button" 
        data-grade-id="<?= htmlspecialchars($grade['id'] ?? ''); ?>" type="button">Delete
    </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 flex items-center justify-center hidden ">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm mx-4">
        <img src="../assets/images/modal-icons/checked.png" alt="Success Image" class="w-16 h-16 mx-auto mb-4 rounded-full border-2 border-green-500">
        <p class="text-green-600 font-semibold text-center text-2xl">
            Deletion Success!
        </p>

    </div>
</div>


<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 flex items-center justify-center  hidden">
    <div class="bg-white p-6 rounded shadow-md max-w-sm">
        <h2 class="text-lg font-semibold mb-4">Confirm Delete</h2>
        <p>Are you sure you want to delete this grade?</p>
        <div class="flex justify-end mt-4">
            <button id="cancelDelete" class="mr-2 bg-gray-300 text-black px-4 py-2 rounded">Cancel</button>
            <button id="confirmDelete" class="bg-red-500 text-white px-4 py-2 rounded">Delete</button>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let gradeIdToDelete;

        // Open modal on delete button click
        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', function() {
                gradeIdToDelete = this.getAttribute('data-grade-id');
                document.getElementById('confirmationModal').classList.remove('hidden');
            });
        });

        // Handle confirmation
        document.getElementById('confirmDelete').addEventListener('click', function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = ''; // Replace with your PHP script URL if needed
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'grade_id';
            input.value = gradeIdToDelete;
            form.appendChild(input);
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_grade_id';
            deleteInput.value = '1'; // Add value to confirm deletion
            form.appendChild(deleteInput);
            document.body.appendChild(form);

            // Use Fetch API for smooth redirect after submission
            fetch(form.action, {
                method: form.method,
                body: new FormData(form)
            })
            .then(response => {
                if (response.ok) {
                    // Hide confirmation modal and show success modal
                    document.getElementById('confirmationModal').classList.add('hidden');
                    
                    const successModal = document.getElementById('successModal');
                    successModal.classList.remove('hidden');
                    const modalContent = successModal.querySelector('div'); // Select the modal content
                    modalContent.classList.add('animate__bounceIn'); // Add fade-in animation
                    
                    // Redirect after a few seconds
                    setTimeout(() => {
                        window.location.href = 'grades.php'; // Replace with your grades page
                    }, 2000); // Adjusted to 3 seconds for a better user experience
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });

        // Close modal on cancel
        document.getElementById('cancelDelete').addEventListener('click', function() {
            document.getElementById('confirmationModal').classList.add('hidden');
        });

        // Close success modal on button click
        document.getElementById('closeSuccessModal').addEventListener('click', function() {
            const successModal = document.getElementById('successModal');
            const modalContent = successModal.querySelector('div'); // Select the modal content
            modalContent.classList.remove('animate__fadeIn'); // Reset for future animations
            modalContent.classList.add('animate__fadeOut'); // Add fade-out animation

            // Use a timeout to hide the modal completely after the animation
            setTimeout(() => {
                successModal.classList.add('hidden');
                modalContent.classList.remove('animate__fadeOut'); // Reset for future animations
            }, 300); // Match this duration with the CSS transition duration
        });
    });
</script>
