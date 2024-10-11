<?php
require './db_connection1.php';

// Define paths to custom icons
$icons = [
    'success' => '../../assets/images/modal-icons/checked.png',
    'error' => '../../assets/images/modal-icons/cancel.png'
];

// Initialize variables
$message = '';
$messageType = '';
$customIcon = $icons['success']; // Default icon

// Handle create/update/delete actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = isset($_POST['course_id']) ? trim($_POST['course_id']) : null;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';

    if (isset($_POST['create'])) {
        if (!$course_id || !$name || !$description) {
            $message = 'Course ID, class name, and description are required.';
            $messageType = 'error';
            $customIcon = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO classes (course_id, name, description) VALUES (?, ?, ?)");
                $stmt->execute([$course_id, $name, $description]);
                $message = 'Class added successfully.';
                $messageType = 'success';
                $customIcon = '<img src="' . $icons['success'] . '" alt="Success Icon" class="w-12 h-12 mx-auto mb-4">';
            } catch (PDOException $e) {
                $message = 'Error: ' . htmlspecialchars($e->getMessage());
                $messageType = 'error';
                $customIcon = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
            }
        }
    } elseif (isset($_POST['update'])) {
        $class_id = $_POST['class_id'] ?? null;

        if (!$class_id || !$course_id || !$name || !$description) {
            $message = 'Error: Missing class ID, course ID, class name, or description.';
            $messageType = 'error';
            $customIcon = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT course_id, name, description FROM classes WHERE id = ?");
                $stmt->execute([$class_id]);
                $currentClass = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($currentClass['course_id'] == $course_id && $currentClass['name'] == $name && $currentClass['description'] == $description) {
                    $message = 'No changes detected. Please modify the values before updating.';
                    $messageType = 'error';
                    $customIcon = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
                } else {
                    $stmt = $pdo->prepare("UPDATE classes SET course_id = ?, name = ?, description = ? WHERE id = ?");
                    $stmt->execute([$course_id, $name, $description, $class_id]);
                    $message = 'Class updated successfully.';
                    $messageType = 'success';
                    $customIcon = '<img src="' . $icons['success'] . '" alt="Success Icon" class="w-12 h-12 mx-auto mb-4">';
                }
            } catch (PDOException $e) {
                $message = 'Error: ' . htmlspecialchars($e->getMessage());
                $messageType = 'error';
                $customIcon = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
            }
        }
    } elseif (isset($_POST['delete'])) {
        $class_id = $_POST['class_id'] ?? null;

        if (!$class_id) {
            $message = 'Error: Missing class ID.';
            $messageType = 'error';
            $customIcon = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
                $stmt->execute([$class_id]);
                $message = 'Class deleted successfully.';
                $messageType = 'success';
                $customIcon = '<img src="' . $icons['success'] . '" alt="Success Icon" class="w-12 h-12 mx-auto mb-4">';
            } catch (PDOException $e) {
                $message = 'Error: ' . htmlspecialchars($e->getMessage());
                $messageType = 'error';
                $customIcon = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
            }
        }
    } elseif(isset($_POST['bulk_delete'])) {
        $class_ids = $_POST['class_ids'] ?? [];

        if (empty($class_ids)) {
            $message = 'Error: No classes selected for deletion.';
            $messageType = 'error';
            $customIcon = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
        } else {
            try {
                $placeholders = rtrim(str_repeat('?,', count($class_ids)), ',');
                $stmt = $pdo->prepare("DELETE FROM classes WHERE id IN ($placeholders)");
                $stmt->execute($class_ids);
                $message = 'Selected classes deleted successfully.';
                $messageType = 'success';
                $customIcon = '<img src="' . $icons['success'] . '" alt="Success Icon" class="w-12 h-12 mx-auto mb-4">';
            } catch (PDOException $e) {
                // Check for foreign key constraint violation
                if ($e->getCode() == '23000') {
                    $message = 'Error: Some classes cannot be deleted because they are associated with other records. Please remove related records first.';
                } else {
                    $message = 'Error: An unexpected issue occurred. Please try again later.';
                }
                $messageType = 'error';
                $customIcon = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
            }
        }
    }}
// Pagination variables
$itemsPerPage = 10; // Number of items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page number
$page = max(1, $page); // Ensure page number is at least 1
$offset = ($page - 1) * $itemsPerPage;

// Fetch total number of classes
$totalStmt = $pdo->query("SELECT COUNT(*) FROM classes");
$totalItems = $totalStmt->fetchColumn();
$totalPages = ceil($totalItems / $itemsPerPage);

// Fetch paginated classes and courses for dropdowns
$stmt = $pdo->prepare("SELECT classes.id, classes.name, classes.description, courses.course_name 
                       FROM classes 
                       JOIN courses ON classes.course_id = courses.id 
                       LIMIT ? OFFSET ?");
$stmt->execute([$itemsPerPage, $offset]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch courses for dropdowns
$stmt = $pdo->query("SELECT * FROM courses");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Classes</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        /* Add custom styles here if needed */
    </style>
</head>
<body>
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Manage Classes</h1>

    <!-- Success/Error Modal -->
    <?php if ($message): ?>
        <div id="messageModal" class="fixed inset-0 flex items-center justify-center z-50 animate__animated <?php echo $messageType == 'success' ? 'animate__bounceIn' : 'animate__shakeX'; ?>">
            <div class="bg-white p-6 rounded-lg shadow-lg text-center max-w-md w-full sm:max-w-sm md:max-w-md lg:max-w-lg xl:max-w-xl">
                <?php echo $customIcon; ?>
                <div class="<?php echo $messageType == 'success' ? 'text-green-500' : 'text-red-500'; ?> text-lg sm:text-xl font-semibold mb-4">
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
                <button onclick="closeModal('messageModal')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Close
                </button>
            </div>
        </div>
    <?php endif; ?>

 <!-- Add New Class Modal -->
<div id="addClassModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="bg-red-900 p-6 rounded-lg shadow-lg text-center max-w-md w-full sm:max-w-sm md:max-w-md lg:max-w-lg xl:max-w-xl border border-gray-300 relative">
        <h2 class="text-xl font-semibold mb-4 uppercase" style="color:#e8e8e6;">Add New Class</h2>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="space-y-4">
            <div class="mb-4">
                <label class="block text-lg font-bold mb-2" style="color:#e8e8e6;" for="course_id">Course</label>
                <select name="course_id" id="course_id" class="block w-full shadow-sm border rounded-lg py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select a course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo htmlspecialchars($course['id']); ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-lg font-bold mb-2" style="color:#e8e8e6;" for="name">Class Name</label>
                <input type="text" name="name" id="name" class="block w-full shadow-sm border rounded-lg py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-4">
                <label class="block text-lg font-bold mb-2" style="color:#e8e8e6;" for="description">Description</label>
                <textarea name="description" id="description" rows="4" class="block w-full shadow-sm border rounded-lg py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
            </div>
            <div class="flex items-center justify-between mt-4">
                <button type="submit" name="create" class="bg-blue-900 hover:bg-blue-800 text-white text-lg font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">Add Class</button>
                <button type="button" onclick="closeModal('addClassModal')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-gray-500">Cancel</button>
            </div>
        </form>
    </div>
</div>





   <!-- Consolidated Bulk Delete Form and Action Buttons -->
<form id="bulkDeleteForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
    <div class="flex justify-between mb-4">
        <!-- Add New Class Button -->
        <button type="button" onclick="openModal('addClassModal')" class="bg-red-900 hover:bg-red-800 text-lg font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" style="color:#e8e8e6;">
            Add New Class
        </button>

        <!-- Delete Selected Button -->
        <button type="submit" name="bulk_delete" class="bg-red-900 hover:bg-red-800 text-lg font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" style="color:#e8e8e6;">
            Delete Selected
        </button>
    </div>

    <!-- List All Classes -->
    <div class="bg-transparent border-0">
        <h2 class="text-xl font-semibold mb-4">Classes List</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse border border-gray-400" style="box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);">
                <thead>
                    <tr class="bg-red-900 text-left" style="color:#e8e8e6;">
                        <th class="px-4 py-3 border border-gray-300 text-center">
                                <input type="checkbox" id="selectAll" onclick="toggleSelectAll()" class="w-6 h-6">
                            </th>
                        <!-- <th class="px-4 py-2 border border-gray-300 text-lg">ID</th> -->
                        <th class="px-4 py-2 border border-gray-300 text-lg">Course Name</th>
                        <th class="px-4 py-2 border border-gray-300 text-lg">Department Name</th>
                        <th class="px-4 py-2 border border-gray-300 text-lg">Description</th>
                        <th class="px-4 py-2 border border-gray-300 text-lg">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td class="px-4 py-3 border text-center border-gray-300" style="background-color:#E2CDCD; border-color:#DFB8B8;">
                                <input type="checkbox" name="class_ids[]" value="<?php echo htmlspecialchars($class['id']); ?> "  class="w-6 h-6">
                            </td>
                            <!-- <td  class="px-4 py-3 border border-gray-300 text-lg" style="background-color:#E2CDCD; border-color:#DFB8B8;"><?php //echo htmlspecialchars($class['id']); ?></td> -->
                            <td  class="px-4 py-3 border border-gray-300 text-lg" style="background-color:#E2CDCD; border-color:#DFB8B8;"><?php echo htmlspecialchars($class['name']); ?></td>
                            <td  class="px-4 py-3 border border-gray-300 text-lg" style="background-color:#E2CDCD; border-color:#DFB8B8;"><?php echo htmlspecialchars(trim($class['course_name'])); ?></td>
                            <td  class="px-4 py-3 border border-gray-300 text-lg" style="background-color:#E2CDCD; border-color:#DFB8B8;"><?php echo htmlspecialchars($class['description']); ?></td>
                            <td  class="px-4 py-3 border border-gray-300 text-lg" style="background-color:#E2CDCD; border-color:#DFB8B8;">
                                <button  data-class-id="<?php echo htmlspecialchars($class['id']); ?>" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline" style="background-color:#D77976;">Edit</button>
                                <!-- <form method="POST" class="inline">
                                    <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($class['id']); ?>">
                                    <button type="submit" name="delete" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete</button>
                                </form> -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>


    
<!-- Edit Class Modal -->
<div id="editClassModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="bg-red-900 p-6 rounded-lg shadow-lg text-center max-w-md w-full sm:max-w-sm md:max-w-md lg:max-w-lg xl:max-w-xl" style="color:#e8e8e6;">
        <h2 class="text-xl font-semibold mb-2 uppercase" style="color:#e8e8e6;">Edit Class</h2>
        <form id="editClassForm" method="POST" class="space-y-4">
            <input type="hidden" name="class_id" id="edit_class_id">

            <!-- Course Selection -->
            <div class="mb-4">
                <label class="block text-lg font-bold mb-2" for="edit_course_id" style="color:#e8e8e6;">Course</label>
                <select name="course_id" id="edit_course_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    <option value="">Select a course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo htmlspecialchars($course['id']); ?>"><?php echo htmlspecialchars($course['course_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Class Name Input -->
            <div class="mb-4">
                <label class="block text-lg font-bold mb-2" for="edit_name" style="color:#e8e8e6;">Course Name</label>
                <input type="text" name="name" id="edit_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500">
            </div>

            <!-- Description Input -->
            <div class="mb-4">
                <label class="block text-lg font-bold mb-2" for="edit_description" style="color:#e8e8e6;">Description</label>
                <textarea name="description" id="edit_description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500"></textarea>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-between">
                <button type="submit" name="update" class="bg-yellow-500 hover:bg-yellow-700 font-bold text-lg text-white py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-yellow-500">Update Class</button>
                <button type="button" data-close-modal="editClassModal" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-gray-500">Cancel</button>

            </div>
        </form>
    </div>
</div>


    <!-- Pagination Controls -->
    <div class="flex justify-center mt-6">
    <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mx-2">Previous</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?php echo $i; ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mx-1 <?php echo $i == $page ? 'bg-gray-700' : ''; ?>">
            <?php echo $i; ?>
        </a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?page=<?php echo $page + 1; ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mx-2">Next</a>
    <?php endif; ?>
</div>





</body>
</html>
<script>
         function toggleSelectAll() {
            var selectAllCheckbox = document.getElementById('selectAll');
            var checkboxes = document.querySelectorAll('input[name="class_ids[]"]');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }
    
  function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }
    document.addEventListener('DOMContentLoaded', function () {
    function openEditModal(classId) {
        console.log('Opening modal for class ID:', classId);
        const classRow = document.querySelector(`[data-class-id="${classId}"]`);
        if (classRow) {
            document.getElementById('edit_class_id').value = classId;
            document.getElementById('edit_name').value = classRow.querySelector('.class-name')?.innerText || '';
            document.getElementById('edit_description').value = classRow.querySelector('.class-description')?.innerText || '';
            document.getElementById('editClassModal').classList.remove('hidden');
        } else {
            console.error('Class row not found.');
        }
    }

    function closeModal(modalId) {
        console.log('Closing modal:', modalId);
        document.getElementById(modalId).classList.add('hidden');
    }

    document.querySelectorAll('button[data-class-id]').forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const classId = this.getAttribute('data-class-id');
            openEditModal(classId);
        });
    });

    document.querySelectorAll('button[data-close-modal]').forEach(button => {
        button.addEventListener('click', function () {
            const modalId = this.getAttribute('data-close-modal');
            closeModal(modalId);
        });
    });

    // Close the modal if clicking outside of it
    document.getElementById('editClassModal').addEventListener('click', function (event) {
        if (event.target === this) {
            closeModal('editClassModal');
        }
    });
});




</script>