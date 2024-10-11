<?php
require './db_connection1.php';

// Define paths to custom icons
$icons = [
    'success' => '../../assets/images/modal-icons/checked.png', // Path to your success icon
    'error' => '../../assets/images/modal-icons/cancel.png'     // Path to your error icon
];

// Initialize message variables
$message = '';
$messageType = '';
$customIcon = $icons['success']; // Default icon

// Handle create/update/delete actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create'])) {
        $class_id = $_POST['class_id'] ?? null;
        $section_name = $_POST['section_name'] ?? '';

        if (!$class_id || !$section_name) {
            $message = 'Please select class and enter section name.';
            $messageType = 'error';
            $customIcon = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO sections (class_id, section_name) VALUES (?, ?)");
                $stmt->execute([$class_id, $section_name]);
                $message = 'Section added successfully.'; // Success message
                $messageType = 'success';
                $customIcon = '<img src="' . $icons['success'] . '" alt="Success Icon" class="w-12 h-12 mx-auto mb-4">';
            } catch (PDOException $e) {
                $message = '<p class="message">Error: ' . htmlspecialchars($e->getMessage()) . '</p>'; // Error message
                $messageType = 'error';
                $customIcon = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
            }
        }
    } elseif (isset($_POST['update'])) {
        $section_id = $_POST['section_id'] ?? null;
        $class_id = $_POST['class_id'] ?? null;
        $section_name = $_POST['section_name'] ?? '';

        if (!$section_id || !$class_id || !$section_name) {
            $message = 'Missing section ID, class ID, or section name.';
            $messageType = 'error';
            $customIcon = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
        } else {
            try {
                // Fetch the current values from the database
                $stmt = $pdo->prepare("SELECT class_id, section_name FROM sections WHERE id = ?");
                $stmt->execute([$section_id]);
                $current = $stmt->fetch(PDO::FETCH_ASSOC);

                // Check if there are any changes
                if ($current['class_id'] == $class_id && $current['section_name'] == $section_name) {
                    $message = 'No changes detected.';
                    $messageType = 'error';
                    $customIcon = '<img src="' . $icons['error'] . '" alt="Warning Icon" class="w-12 h-12 mx-auto mb-4">';
                } else {
                    // Perform the update
                    $stmt = $pdo->prepare("UPDATE sections SET class_id = ?, section_name = ? WHERE id = ?");
                    $stmt->execute([$class_id, $section_name, $section_id]);
                    $message = 'Section updated successfully.'; // Success message
                    $messageType = 'success';
                    $customIcon = '<img src="' . $icons['success'] . '" alt="Success Icon" class="w-12 h-12 mx-auto mb-4">';
                }
            } catch (PDOException $e) {
                $message = '<p class="message">Error: ' . htmlspecialchars($e->getMessage()) . '</p>'; // Error message
                $messageType = 'error';
                $customIcon = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
            }
        }
    } else if (isset($_POST['delete_selected']) && !empty($_POST['section_ids'])) {
        $section_ids = $_POST['section_ids'];
        $placeholders = implode(',', array_fill(0, count($section_ids), '?'));
    
        try {
            $stmt = $pdo->prepare("DELETE FROM sections WHERE id IN ($placeholders)");
            $stmt->execute($section_ids);
            $message = 'Selected sections deleted successfully.';
            $messageType = 'success';
            $customIcon = '<img src="' . $icons['success'] . '" alt="Success Icon" class="w-12 h-12 mx-auto mb-4">';
        } catch (PDOException $e) {
            $message = '<p class="message">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            $messageType = 'error';
            $customIcon = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
        }
    } else {
        $message = 'No sections selected for deletion.';
        $messageType = 'error';
        $customIcon = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
    }
    
}

// Fetch all sections and classes for dropdowns
$stmt = $pdo->query("SELECT sections.id, sections.section_name, classes.name AS class_name, sections.class_id 
                     FROM sections 
                     JOIN classes ON sections.class_id = classes.id");
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM classes");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);



// Pagination variables
$limit = 10; // Number of results per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch total number of sections
$totalStmt = $pdo->query("SELECT COUNT(*) FROM sections");
$totalSections = $totalStmt->fetchColumn();
$totalPages = ceil($totalSections / $limit);

// Fetch sections for the current page with limit and offset
$stmt = $pdo->prepare("SELECT sections.id, sections.section_name, classes.name AS class_name, sections.class_id 
                     FROM sections 
                     JOIN classes ON sections.class_id = classes.id 
                     LIMIT ? OFFSET ?");
$stmt->bindParam(1, $limit, PDO::PARAM_INT);
$stmt->bindParam(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);



// Fetch classes
$stmt = $pdo->query("SELECT * FROM classes");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sections</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body class="bg-gray-200 text-gray-900">
    <div class="container mx-auto">
        <h1 class="text-2xl font-bold mb-4">Manage Sections</h1>
        
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


<!-- Add New Section Modal -->
<div id="addSectionModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="bg-red-900 p-6 rounded-lg shadow-lg text-center max-w-md w-full sm:max-w-sm md:max-w-md lg:max-w-lg xl:max-w-xl border border-gray-300 relative" >
        <h2 class="text-xl font-semibold mb-4 uppercase" style="color:#e8e8e6;">Add New Section</h2>
        <form method="POST" class="space-y-4">
            <div class="mb-4 relative">
                <label class="block text-lg font-bold mb-2" style="color:#e8e8e6;">Class</label>
                <div class="relative">
                    <button type="button" id="classDropdownBtn" class="block w-full shadow-sm border rounded-lg py-2 px-3 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" onclick="toggleClassDropdown()" style="color:#e8e8e6;">
                        <span id="selected_class" class="text-lg">Select a class</span>
                        <svg class="w-5 h-5 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <!-- Custom Scrollable Dropdown -->
                    <div id="classDropdownMenu" class="absolute w-full bg-white shadow-md border border-gray-300 mt-2 hidden max-h-48 overflow-y-scroll">
    <ul>
        <?php foreach ($classes as $class): ?>
            <li class="p-2 hover:bg-gray-100 cursor-pointer" onclick="selectClass('<?php echo htmlspecialchars($class['id']); ?>', '<?php echo htmlspecialchars($class['name']); ?>')">
                <?php echo htmlspecialchars($class['name']); ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

                    <!-- Hidden input to store the selected class ID -->
                    <input type="hidden" name="class_id" id="class_id" value="">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-lg font-bold mb-2" style="color:#e8e8e6;">Section Name</label>
                <input type="text" name="section_name" id="add_section_name" class="block w-full shadow-sm border rounded-lg py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-center justify-between mt-4">
                <button type="submit" name="create" class="bg-blue-900 hover:bg-blue-800 text-white text-lg font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">Add Section</button>
                <button type="button" onclick="closeModal('addSectionModal')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-gray-500">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Function to toggle the visibility of the class dropdown menu
    function toggleClassDropdown() {
        var dropdown = document.getElementById('classDropdownMenu');
        dropdown.classList.toggle('hidden');
    }

    // Function to select a class and update the button's display text
    function selectClass(classId, className) {
        document.getElementById('selected_class').innerText = className;
        document.getElementById('class_id').value = classId; // Set the hidden input value to the class ID
        toggleClassDropdown(); // Hide the dropdown after selection
    }
</script>




<!-- Update Section Modal -->
<div id="updateSectionModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="bg-red-900 p-6 rounded-lg shadow-lg text-center max-w-md w-full sm:max-w-sm md:max-w-md lg:max-w-lg xl:max-w-xl" style="color:#e8e8e6;">
        <h2 class="text-xl font-semibold mb-2 uppercase" style="color:#e8e8e6;">Update Section</h2>
        <form id="updateForm" method="POST" class="space-y-4">
            <input type="hidden" name="section_id" id="update_section_id">
            
            <!-- Class Selection -->
            <div class="mb-4">
                <label class="block text-lg font-bold mb-2" for="class_id" style="color:#e8e8e6;">Class</label>
                <select name="class_id" id="update_class_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Section Name Input -->
            <div class="mb-4">
                <label class="block text-lg font-bold mb-2" for="section_name" style="color:#e8e8e6;">Section Name</label>
                <input type="text" name="section_name" id="update_section_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-yellow-500">
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-between">
                <button type="submit" name="update" class="bg-yellow-500 hover:bg-yellow-700 font-bold text-lg text-white py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-yellow-500">Update Section</button>
                <button type="button" onclick="closeModal('updateSectionModal')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-gray-500">Cancel</button>
            </div>
        </form>
    </div>
</div>

        <!-- Buttons to trigger modals -->
        <!-- Container for both Add New Section and Delete Selected buttons -->
        <div class="flex justify-between mb-4">
            <!-- Add New Section Button -->
            <button onclick="openModal('addSectionModal')" class="bg-red-900 hover:bg-red-800 text-lg font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" style="color:#e8e8e6;">
                Add New Section
            </button>

            <!-- Delete Selected Button -->
            <form method="POST" class="flex items-center">
                <button type="submit" name="delete_selected" class="bg-red-900 hover:bg-red-800 text-lg font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" style="color:#e8e8e6;">
                    Delete Selected
                </button>
            </form>
        </div>

        <!-- List All Sections -->
        <div class="bg-transparent border-0">
            <h2 class="text-xl font-semibold mb-4">Sections List</h2>
            <!-- Form with the Delete Selected Button -->
            <form method="POST" class="space-y-4">
                <table class="min-w-full table-auto border-collapse border border-gray-400" style="box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);">
                    <thead>
                        <tr class="bg-red-900 text-left" style="color:#e8e8e6;">
                            <th class="px-4 py-3 border border-gray-300 text-center">
                                <input type="checkbox" id="selectAll" onclick="toggleSelectAll()" class="w-6 h-6">
                            </th>
                            <th class="px-4 py-2 border border-gray-300 text-lg">Class Name</th>
                            <th class="px-4 py-2 border border-gray-300 text-lg">Section Name</th>
                            <th class="px-4 py-2 border border-gray-300 text-lg">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sections as $section): ?>
                        <tr>
                            <td class="px-4 py-3 border text-center border-gray-300" style="background-color:#E2CDCD; border-color:#DFB8B8;"> 
                                <input type="checkbox" name="section_ids[]" value="<?php echo $section['id']; ?>"  class="w-6 h-6">
                            </td>
                            
                            <td class="px-4 py-3 border border-gray-300 text-lg" style="background-color:#E2CDCD; border-color:#DFB8B8;"><?php echo htmlspecialchars($section['class_name']); ?></td>
                            <td class="px-4 py-3 border border-gray-300 text-lg" style="background-color:#E2CDCD; border-color:#DFB8B8;"><?php echo htmlspecialchars($section['section_name']); ?></td>
                            <td class="px-4 py-3 border border-gray-300 text-lg" style="background-color:#E2CDCD; border-color:#DFB8B8;">
                                <button type="button" onclick="openUpdateModal(<?php echo $section['id']; ?>, '<?php echo addslashes($section['section_name']); ?>')" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline" style="background-color:#D77976;">
                                    Edit
                                </button>
                                <!-- <button type="button" onclick="if (confirm('Are you sure you want to delete this section?')) window.location.href='delete_section.php?id=<?php echo $section['id']; ?>'" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline">
                                    Delete
                                </button> -->
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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

    <script>
        function openUpdateModal(sectionId, sectionName) {
            document.getElementById('updateSectionModal').style.display = 'flex';
            document.getElementById('update_section_id').value = sectionId;
            document.getElementById('update_section_name').value = sectionName;
        }

        function toggleSelectAll() {
            var selectAllCheckbox = document.getElementById('selectAll');
            var checkboxes = document.querySelectorAll('input[name="section_ids[]"]');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        function openModal(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
        } else {
            console.error("Modal with ID '" + modalId + "' not found.");
        }
    }

        function closeModal(modalId) {
            var modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
            } else {
                console.error("Modal with ID '" + modalId + "' not found.");
            }
        }

        // Automatically open the message modal if it exists
        var messageModal = document.getElementById('messageModal');
        if (messageModal) {
            messageModal.style.display = 'flex';
        }
    </script>
</body>
</html>
