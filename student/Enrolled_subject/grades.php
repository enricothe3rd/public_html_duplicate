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
// if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_grade'])) {
//     $student_number = $_POST['student_number'] ?? null; // Get student number from form
//     $subject_id = $_POST['subject_id'] ?? null; // Get subject ID from form
//     $prelim = $_POST['prelim'] ?? 0; // Ensure you get prelim value
//     $midterm = $_POST['midterm'] ?? 0; // Ensure you get midterm value
//     $finals = $_POST['finals'] ?? 0; // Ensure you get finals value

//     // Debugging output to check received values
//     error_log("Student Number: $student_number, Subject ID: $subject_id, Prelim: $prelim, Midterm: $midterm, Finals: $finals");

//     // Validate grades only if they are set and not empty
//     if (!empty($prelim) && ($prelim < 1 || $prelim > 5)) {
//         $_SESSION['error_message'] = "Prelim grade must be between 1 and 5.";
//     }

//     if (!empty($midterm) && ($midterm < 1 || $midterm > 5)) {
//         $_SESSION['error_message'] = "Midterm grade must be between 1 and 5.";
//     }

//     if (!empty($finals) && ($finals < 1 || $finals > 5)) {
//         $_SESSION['error_message'] = "Finals grade must be between 1 and 5.";
//     }

//     // Proceed only if there is no error message
//     if (!isset($_SESSION['error_message'])) {
//         // Check if a grade entry already exists for this student and subject
//         $stmt = $db->prepare("SELECT id FROM grades WHERE student_number = ? AND subject_id = ?");
//         $stmt->execute([$student_number, $subject_id]);
//         $existing_grade = $stmt->fetch(PDO::FETCH_ASSOC);

//         // Get the number of units for the subject
//         $stmt = $db->prepare("SELECT units FROM subjects WHERE id = ?");
//         $stmt->execute([$subject_id]);
//         $subject = $stmt->fetch(PDO::FETCH_ASSOC);
//         $units = $subject['units'] ?? 0; // Assuming the units column in subjects table

// // Convert grades and units to floats
// $prelim = floatval($prelim);
// $midterm = floatval($midterm);
// $finals = floatval($finals);
// $units = floatval($units); // Convert units to float

// // Calculate total grade using the overall units
// if ($units > 0) { // Ensure units are valid for division
//     // Calculate the total grades based on the weights
//     $total_grades = ($prelim * 0.3) + ($midterm * 0.3) + ($finals * 0.4);
// } else {
//     $total_grades = 0; // If no units, set total grades to 0 to avoid division by zero
// }


//         if ($existing_grade) {
//             // Update existing grade
//             $stmt = $db->prepare("UPDATE grades SET prelim = ?, midterm = ?, finals = ?, total_grade = ? WHERE id = ?");
//             $stmt->execute([$prelim, $midterm, $finals, $total_grades, $existing_grade['id']]);
//             $_SESSION['success_message'] = "Grades successfully updated."; // Set success message in session
//         } else {
//             // Insert new grade
//             $stmt = $db->prepare("INSERT INTO grades (student_number, subject_id, prelim, midterm, finals, total_grade) VALUES (?, ?, ?, ?, ?, ?)");
//             $stmt->execute([$student_number, $subject_id, $prelim, $midterm, $finals, $total_grades]);
//             $_SESSION['success_message'] = "Grades successfully added."; // Set success message in session
//         }
//     }
// }



// Include the message handler to display messages
include '../../message/message_handler.php';



// Handle grade deletion
// if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_grade_id'])) {
//     $grade_id = $_POST['grade_id'];

//     // Prepare the delete statement
//     $stmt = $db->prepare("DELETE FROM grades WHERE id = ?");
//     if ($stmt->execute([$grade_id])) {
//         $_SESSION['message'] = "Grade ID $grade_id deleted successfully."; // Set success message
//         error_log("Grade ID $grade_id deleted successfully.");
//     } else {
//         $_SESSION['message'] = "Failed to delete Grade ID $grade_id."; // Set error message
//         error_log("Failed to delete Grade ID $grade_id.");
//     }

  
// }

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




<?php
// Assuming a database connection is already established
$subject_id = $subject_id ?? null; // Subject ID
$section_name = $students[0]['section_name'] ?? null; // Dynamically fetch the section name

// Fetch grades for all students in the section for the specific subject
$grades = [];
if ($subject_id) {
    $stmt = $db->prepare("
        SELECT g.student_number, g.prelim, g.midterm, g.finals
        FROM grades g
        JOIN enrollments e ON g.student_number = e.student_number
        WHERE g.subject_id = :subject_id
        AND e.status != 'dropped'
    ");
    $stmt->execute([':subject_id' => $subject_id]);
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$grades = array_column($grades, null, 'student_number'); // Reindex by student_number

?>

<?php if (!empty($students)): ?>
    <h2 class="text-xl font-semibold mb-4">
        Section: <?= htmlspecialchars($section_name ?? 'No Section Assigned'); ?>
        Subject ID: <?= htmlspecialchars($subject_id ?? 'No Subject Assigned'); ?>
    </h2>

    <form method="POST" action="" class="mb-6" id="gradeForm">
        <input type="hidden" id="subject_id" name="subject_id" value="<?= htmlspecialchars($subject_id ?? ''); ?>"> <!-- Subject ID hidden -->
        <div class="overflow-x-auto">
            <table class="min-w-full border border-red-300 text-left rounded shadow-lg">
                <thead class="bg-red-700 text-white">
                    <tr>
                        <th class="px-4 py-2">Student Name</th>
                        <th class="px-4 py-2">Prelim Grades</th>
                        <th class="px-4 py-2"></th>
                        <th class="px-4 py-2">Midterm Grades</th>
                        <th class="px-4 py-2"></th>
                        <th class="px-4 py-2">Finals Grades</th>
                        <th class="px-4 py-2"></th>
                        <th class="px-4 py-2">Drop</th>
                    </tr>
                </thead>
                <tbody>
                <?php
// Assuming you already have a PDO connection available as $pdo

// Assuming you already have a PDO connection available as $pdo
foreach ($students as $student):
    $student_number = $student['student_number'];

    // Query the enrollments table to get the status of this student
    $stmt = $db->prepare("SELECT status FROM enrollments WHERE student_number = :student_number");
    $stmt->bindParam(':student_number', $student_number, PDO::PARAM_STR);
    $stmt->execute();
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

    // Skip this student if the status is 'dropped'
    if ($enrollment && $enrollment['status'] === 'dropped') {
        continue; // Skip the current iteration if the student is dropped
    }

    $existing_grades = $grades[$student_number] ?? null;
?>
<tr class="hover:bg-red-100">
    <td class="border px-4 py-2">
        <?= htmlspecialchars(strtoupper(($student['firstname'] ?? '') . ' ' . ($student['lastname'] ?? ''))); ?>
        <input type="hidden" name="students[<?= $student_number; ?>][student_number]" value="<?= htmlspecialchars($student_number); ?>">
    </td>
    <td class="border px-4 py-2">
        <input 
            type="number" 
            name="students[<?= $student_number; ?>][prelim]" 
            step="0.01" 
            class="border border-red-300 rounded p-2 w-full focus:border-red-500 outline-none" 
            value="<?= htmlspecialchars($existing_grades['prelim'] ?? ''); ?>">
    </td>
    <td class="border px-4 py-2">
        <button 
            type="button" 
            class="bg-red-700 text-white rounded py-1 px-2 hover:bg-red-800" 
            onclick="confirmAddGrade('<?= $student_number; ?>', 'Prelim')">
            <?= isset($existing_grades['prelim']) && $existing_grades['prelim'] !== null ? 'Update Prelim' : 'Add Prelim'; ?>
        </button>
    </td>
    <td class="border px-4 py-2">
        <input 
            type="number" 
            name="students[<?= $student_number; ?>][midterm]" 
            step="0.01" 
            class="border border-red-300 rounded p-2 w-full focus:border-red-500 outline-none" 
            value="<?= htmlspecialchars($existing_grades['midterm'] ?? ''); ?>">
    </td>
    <td class="border px-4 py-2">
        <button 
            type="button" 
            class="bg-red-700 text-white rounded py-1 px-2 hover:bg-red-800" 
            onclick="confirmAddGrade('<?= $student_number; ?>', 'Midterm')">
            <?= isset($existing_grades['midterm']) && $existing_grades['midterm'] !== null ? 'Update Midterm' : 'Add Midterm'; ?>
        </button>
    </td>
    <td class="border px-4 py-2">
        <input 
            type="number" 
            name="students[<?= $student_number; ?>][finals]" 
            step="0.01" 
            class="border border-red-300 rounded p-2 w-full focus:border-red-500 outline-none" 
            value="<?= htmlspecialchars($existing_grades['finals'] ?? ''); ?>">
    </td>
    <td class="border px-4 py-2">
        <button 
            type="button" 
            class="bg-red-700 text-white rounded py-1 px-2 hover:bg-red-800" 
            onclick="confirmAddGrade('<?= $student_number; ?>', 'Finals')">
            <?= isset($existing_grades['finals']) && $existing_grades['finals'] !== null ? 'Update Finals' : 'Add Finals'; ?>
        </button>
    </td>
    <td class="border px-4 py-2">
        <button 
            type="button" 
            class="bg-red-700 text-white rounded py-1 px-2 hover:bg-red-800" 
            onclick="confirmDropStudent('<?= $student_number; ?>', '<?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?>', '<?= $section_name; ?>', '<?= $student['firstname']; ?>', '<?= $student['lastname']; ?>')">
            Dropped
        </button>
    </td>
</tr>
<?php endforeach; ?>

              
                </tbody>
            </table>
        </div>

        <button type="submit" name="save_grades" class="mt-4 bg-red-700 text-white rounded py-2 px-4 hover:bg-red-800">
            Save Grades
        </button>
    </form>
<?php else: ?>
    <p class="text-red-500">No students found in this section.</p>
<?php endif; ?>


<div id="confirmationModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
        <h2 class="text-lg font-semibold mb-4">Confirm Action</h2>
        <p id="modalMessage" class="text-gray-700 mb-4">Are you sure?</p>
        <p id="studentDetails" class="text-gray-600 mb-4"></p> <!-- Details about the student and grade -->
        <div class="flex justify-end space-x-4">
            <button id="cancelButton" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Cancel</button>
            <button id="confirmButton" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Confirm</button>
        </div>
    </div>
</div>


<script>

function confirmAddGrade(studentNumber, term) {
    // Get the grade input field and its value
    const gradeInput = document.querySelector(
        `input[name="students[${studentNumber}][${term.toLowerCase()}]"]`
    );
    const grade = gradeInput.value;

    // Fetch the student's full name from the table row
    const studentRow = gradeInput.closest("tr");
    const fullName = studentRow.querySelector("td:first-child").textContent.trim();

    // Check if grade is entered
    if (!grade) {
        // Show modal for missing grade
        showModal(
            `Please enter a ${term} grade before submitting.`,
            null, // No confirm action
            null
        );
        return;
    }

    // Check the button text to determine action (Add or Update)
    const actionButton = studentRow.querySelector(`button[onclick*="${term}"]`);
    const isUpdate = actionButton.textContent.includes("Update");

    // Prepare different messages for Add and Update
    const message = isUpdate
        ? `Are you sure you want to update the ${term} grade for ${fullName}?`
        : `Are you sure you want to add the ${term} grade for ${fullName}?`;
    const details = `Grade: ${grade}, Student Number: ${studentNumber}`;

    // Show the modal with details
    showModal(message, () => addGrade(studentNumber, term, isUpdate), null, details);
}

function showModal(message, onConfirm, onCancel, details = "") {
    // Get modal elements
    const modal = document.getElementById("confirmationModal");
    const modalMessage = document.getElementById("modalMessage");
    const studentDetails = document.getElementById("studentDetails");
    const confirmButton = document.getElementById("confirmButton");
    const cancelButton = document.getElementById("cancelButton");

    // Set modal content
    modalMessage.textContent = message;
    studentDetails.textContent = details;

    // Show the modal
    modal.classList.remove("hidden");

    // Add event listeners for buttons
    confirmButton.onclick = () => {
        modal.classList.add("hidden");
        if (onConfirm) onConfirm();
    };

    cancelButton.onclick = () => {
        modal.classList.add("hidden");
        if (onCancel) onCancel();
    };
}
function addGrade(studentNumber, term, isUpdate) {
    const gradeInput = document.querySelector(
        `input[name="students[${studentNumber}][${term.toLowerCase()}]"]`
    );
    const grade = gradeInput.value;
    const subjectId = document.getElementById("subject_id").value; // Get subject_id from hidden field

    if (!grade) {
        // Show modal for missing grade
        showModal(
            `Please enter a ${term} grade before submitting.`,
            null,
            null
        );
        return;
    }

    fetch("update_grade.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            student_number: studentNumber,
            grade: grade,
            term: term,
            subject_id: subjectId, // Pass subject_id
            is_update: isUpdate // Indicate whether this is an update or add operation
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            // Show success or error modal based on the response
            const modalMessage = data.success
                ? `${term} grade ${isUpdate ? "updated" : "added"} successfully for Student: ${studentNumber}`
                : `Failed to ${isUpdate ? "update" : "add"} ${term} grade: ${data.message}`;
            showModal(modalMessage, null, null);

            // Reload the page on success
            if (data.success) {
                location.reload(); // Reload the page to reflect the changes
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            showModal(
                "An error occurred while processing the grade.",
                null,
                null
            );
        });
}


function confirmDropStudent(studentNumber, fullName, section_name, first_name, last_name) {
    // Construct the message and details
    const message = `Are you sure you want to mark ${fullName} as dropped?`;
    const details = `Student Number: ${studentNumber} Section: ${section_name}`;

    // Show confirmation modal and trigger the dropStudent function if confirmed
    showModal(message, () => dropStudent(studentNumber, section_name, first_name, last_name), null, details);
}


function dropStudent(studentNumber, section_name, first_name, last_name) {
    console.log('Student Number:', studentNumber);
    console.log('Section Name:', section_name);
    console.log('First Name:', first_name);
    console.log('Last Name:', last_name);

    // Proceed with the fetch request
    fetch("mark_as_dropped.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            student_number: studentNumber,
            section_name: section_name,
            firstname: first_name,
            lastname: last_name
        }),
    })
    .then((response) => response.json()) // Parse JSON response
    .then((data) => {
        if (data.success) {
            alert(`Student ${studentNumber} marked as dropped successfully.`);
            location.reload(); // Refresh the page to reflect changes
        } else {
            alert(`Failed to mark student as dropped: ${data.message}`);
        }
    })
    .catch((error) => {
        console.error("Error:", error);
        alert("An error occurred while marking the student as dropped.");
    });
}


</script>

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
<!-- <div id="successModal" class="fixed inset-0 flex items-center justify-center hidden ">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm mx-4">
        <img src="../assets/images/modal-icons/checked.png" alt="Success Image" class="w-16 h-16 mx-auto mb-4 rounded-full border-2 border-green-500">
        <p class="text-green-600 font-semibold text-center text-2xl">
            Deletion Success!
        </p>

    </div>
</div> -->


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
