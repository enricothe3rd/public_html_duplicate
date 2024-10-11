<?php
require 'Instructor_subject.php';

$instructorSubject = new InstructorSubject();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the assignment details
    $assignment = $instructorSubject->getAssignmentById($id);
} else {
    // Handle the case where no ID is provided
    die('Invalid assignment ID');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instructor_id = $_POST['instructor_id'];
    $subject_id = $_POST['subject_id'];
    // $semester_id is not needed since it's not editable

    // Update the assignment
    $instructorSubject->updateAssignment($id, $instructor_id, $subject_id);

    // Redirect or display a success message
    header('Location: read_instructor_subjects.php');
    exit;
}

$instructors = $instructorSubject->getInstructors();
$subjects = $instructorSubject->getSubjects();
$semesters = $instructorSubject->getSemesters();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Instructor Subject</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div class="container mx-auto mt-10 p-6  max-w-2xl">
    <button 
            onclick="goBack()" 
            class="mb-4 px-4 py-2 bg-red-700 text-white rounded hover:bg-red-800 transition duration-200 flex items-center"
        >
            <i class="fas fa-arrow-left mr-2"></i> <!-- Arrow icon -->
            Back
        </button>
        <h1  class="text-3xl font-semibold text-red-800 mb-4">Edit Instructor Subject</h1>
        
        <form method="POST" action="" class="bg-white p-6 rounded-lg shadow-lg">
            <div class="mb-4">
                <label for="instructor_id" class="block text-sm font-medium text-red-700">Instructor:</label>
                             <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <i class="fas fa-user-tie p-2 text-red-500"></i>
                    <select id="instructor_id" name="instructor_id" class="w-full h-12 px-3 bg-red-50 text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 rounded-md transition duration-200" required>
                        <?php foreach ($instructors as $instructor): ?>
                            <option value="<?php echo htmlspecialchars($instructor['id']); ?>" <?php echo $instructor['id'] == $assignment['instructor_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($instructor['first_name'] . ' ' . $instructor['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label for="subject_id" class="block text-sm font-medium text-red-700">Subject:</label>
                             <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <i class="fas fa-book p-2 text-red-500"></i>
                    <select id="subject_id" name="subject_id" class="w-full h-12 px-3 bg-red-50 text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 rounded-md transition duration-200" required>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo htmlspecialchars($subject['id']); ?>" <?php echo $subject['id'] == $assignment['subject_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($subject['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label for="semester_id" class="block text-sm font-medium text-red-700">Semester:</label>
                             <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <i class="fas fa-calendar-alt p-2 text-red-500"></i>
                    <span class="block w-full bg-gray-200 py-2 px-3 text-gray-700">
                        <?php echo htmlspecialchars($assignment['semester_name']); ?>
                    </span>
                </div>
            </div>

            <button type="submit" class="w-full bg-red-700 text-white py-2 px-4 rounded-lg hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-save"></i> Update
            </button>
        </form>
    </div>
</body>
</html>
<script>
    function goBack() {
        window.history.back(); // Navigates to the previous page
    }
</script>