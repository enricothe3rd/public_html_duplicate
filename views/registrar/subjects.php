<?php
require 'db_connection1.php';

// Define paths to custom icons
$icons = [
    'success' => '../../assets/images/modal-icons/checked.png', // Path to your success icon
    'error' => '../../assets/images/modal-icons/cancel.png'   // Path to your error icon
];

// Initialize feedback variables
$message = '';
$messageType = '';
$customIcon = $icons['success']; // Default icon

// Pagination settings
$items_per_page = 200;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Handle create/update/delete actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Handle create/update actions
        if (isset($_POST['create']) || isset($_POST['update'])) {
            // Common validation
            $required_fields = ['section_id', 'code', 'subject_title', 'units', 'room', 'day', 'start_time', 'end_time'];

            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception('All fields are required.');
                }
            }

            $section_id = $_POST['section_id'];
            $code = $_POST['code'];
            $subject_title = $_POST['subject_title'];
            $units = $_POST['units'];
            $room = $_POST['room'];
            $day = $_POST['day'];
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];

            if (isset($_POST['create'])) {
                $stmt = $pdo->prepare("INSERT INTO subjects (section_id, code, subject_title, units, room, day, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$section_id, $code, $subject_title, $units, $room, $day, $start_time, $end_time]);

                $message = 'Subject added successfully.';
                $messageType = 'success';
                $customIcon = '<img src="' . $icons['success'] . '" alt="Success Icon" class="w-12 h-12 mx-auto mb-4">';
            } elseif (isset($_POST['update'])) {
                $subject_id = $_POST['subject_id'];

                // Fetch the current values from the database
                $stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
                $stmt->execute([$subject_id]);
                $current_values = $stmt->fetch(PDO::FETCH_ASSOC);

                // Compare current values with new values
                if ($current_values['section_id'] == $section_id &&
                    $current_values['code'] == $code &&
                    $current_values['subject_title'] == $subject_title &&
                    $current_values['units'] == $units &&
                    $current_values['room'] == $room &&
                    $current_values['day'] == $day &&
                    $current_values['start_time'] == $start_time &&
                    $current_values['end_time'] == $end_time) {
                    
                    throw new Exception('No changes detected.');
                }

                // Perform the update
                $stmt = $pdo->prepare("UPDATE subjects SET section_id = ?, code = ?, subject_title = ?, units = ?, room = ?, day = ?, start_time = ?, end_time = ? WHERE id = ?");
                $stmt->execute([$section_id, $code, $subject_title, $units, $room, $day, $start_time, $end_time, $subject_id]);

                $message = 'Subject updated successfully.';
                $messageType = 'success';
                $customIcon = '<img src="' . $icons['success'] . '" alt="Success Icon" class="w-12 h-12 mx-auto mb-4">';
            }
        } elseif (isset($_POST['delete'])) {
            $subject_id = $_POST['subject_id'];
            $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
            $stmt->execute([$subject_id]);

            $message = 'Subject deleted successfully.';
            $messageType = 'success';
            $customIcon = '<img src="' . $icons['success'] . '" alt="Success Icon" class="w-12 h-12 mx-auto mb-4">';
        } elseif (isset($_POST['delete_selected'])) {
            if (empty($_POST['subject_ids'])) {
                throw new Exception('No subjects selected for deletion.');
            }

            $subject_ids = $_POST['subject_ids'];
            $placeholders = rtrim(str_repeat('?, ', count($subject_ids)), ', ');

            $stmt = $pdo->prepare("DELETE FROM subjects WHERE id IN ($placeholders)");
            $stmt->execute($subject_ids);

            $message = 'Selected subjects deleted successfully.';
            $messageType = 'success';
            $customIcon = '<img src="' . $icons['success'] . '" alt="Success Icon" class="w-12 h-12 mx-auto mb-4">';
        }
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
        $customIcon = '<img src="' . $icons['error'] . '" alt="Error Icon" class="w-12 h-12 mx-auto mb-4">';
    }
}

// Fetch paginated subjects
$stmt = $pdo->prepare("
    SELECT subjects.*, sections.section_name 
    FROM subjects 
    JOIN sections ON subjects.section_id = sections.id
    LIMIT :offset, :items_per_page
");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':items_per_page', $items_per_page, PDO::PARAM_INT);
$stmt->execute();
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total number of subjects for pagination controls
$total_stmt = $pdo->query("SELECT COUNT(*) FROM subjects");
$total_items = $total_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);




// Fetch all sections for dropdowns
$stmt = $pdo->query("SELECT * FROM sections");
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Fetch subjects without class and section information
$stmt = $pdo->prepare("
    SELECT subjects.* 
    FROM subjects 
    LIMIT :offset, :items_per_page
");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':items_per_page', $items_per_page, PDO::PARAM_INT);
$stmt->execute();
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// No grouping needed, just pass the subjects array directly



// Retain POST values for form fields
$input_values = [
    'section_id' => $_POST['section_id'] ?? '',
    'code' => $_POST['code'] ?? '',
    'subject_title' => $_POST['subject_title'] ?? '',
    'units' => $_POST['units'] ?? '',
    'room' => $_POST['room'] ?? '',
    'day' => $_POST['day'] ?? '',
    'start_time' => $_POST['start_time'] ?? '',
    'end_time' => $_POST['end_time'] ?? ''
];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
</head>
<body class="bg-gray-200 text-gray-900">
    <div class="container mx-auto">
        <h1 class="text-2xl font-bold mb-4">Manage Subjects</h1>

        <!-- Success/Error Modal -->
        <?php if ($message): ?>
        <div id="messageModal" class="fixed inset-0 flex items-center justify-center z-50 animate__animated <?php echo $messageType == 'success' ? 'animate__bounceIn' : 'animate__shakeX'; ?>">
            <div class="bg-white p-6 rounded-lg shadow-lg text-center max-w-md w-full sm:max-w-sm md:max-w-md lg:max-w-lg xl:max-w-xl">
                <?php echo $customIcon; ?>
                <div class="<?php echo $messageType == 'success' ? 'text-green-500' : 'text-red-500'; ?> text-lg sm:text-xl font-semibold mb-4">
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
                <button onclick="closeModal()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Close
                </button>
            </div>
        </div>

        <script>
            function closeModal() {
                document.getElementById('messageModal').style.display = 'none';
            }
            document.getElementById('messageModal').style.display = 'flex';
        </script>
        <?php endif; ?>

        <!-- Add New Subject Modal -->
        <div id="addModal" class="fixed inset-0 hidden items-center justify-center z-50">
            <div class="bg-white p-8 rounded-lg shadow-lg max-w-full sm:max-w-sm md:max-w-md lg:max-w-lg xl:max-w-xl">
                <h2 class="text-2xl font-semibold mb-6 text-gray-900">Add New Subject</h2>
                <form method="POST" class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Section Select Field -->
                    <div>
                        <label class="block text-gray-700 text-lg font-medium mb-2" for="section_id">Section</label>
                        <select name="section_id" id="section_id" 
    class="shadow border border-gray-300 rounded-lg w-full py-3 px-4 text-base text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
    <?php foreach ($sections as $section): ?>
        <option value="<?php echo htmlspecialchars($section['id']); ?>">
            <?php echo htmlspecialchars($section['section_name']); ?>
        </option>
    <?php endforeach; ?>
</select>

                    </div>

                    <!-- Code Input Field -->
                    <div>
                        <label class="block text-gray-700 text-lg font-medium mb-2" for="code">Code</label>
                        <input type="text" name="code" id="code" 
                            class="shadow border border-gray-300 rounded-lg w-full py-3 px-4 text-base text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            required>
                    </div>

                    <!-- Subject Title Input Field -->
                    <div>
                        <label class="block text-gray-700 text-lg font-medium mb-2" for="subject_title">Subject Title</label>
                        <input type="text" name="subject_title" id="subject_title" 
                            class="shadow border border-gray-300 rounded-lg w-full py-3 px-4 text-base text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            required>
                    </div>

                    <!-- Units Input Field -->
                    <div>
                        <label class="block text-gray-700 text-lg font-medium mb-2" for="units">Units</label>
                        <input type="number" name="units" id="units" 
                            class="shadow border border-gray-300 rounded-lg w-full py-3 px-4 text-base text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            required>
                    </div>

                    <!-- Room Input Field -->
                    <div>
                        <label class="block text-gray-700 text-lg font-medium mb-2" for="room">Room</label>
                        <input type="text" name="room" id="room" 
                            class="shadow border border-gray-300 rounded-lg w-full py-3 px-4 text-base text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            required>
                    </div>

                    <!-- Day Select Field -->
                    <div>
                        <label class="block text-gray-700 text-lg font-medium mb-2" for="day">Day</label>
                        <select name="day" id="day" 
                            class="shadow border border-gray-300 rounded-lg w-full py-3 px-4 text-base text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            required>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>

                    <!-- Start Time Input Field -->
                    <div>
                        <label class="block text-gray-700 text-lg font-medium mb-2" for="start_time">Start Time</label>
                        <input type="time" name="start_time" id="start_time" 
                            class="shadow border border-gray-300 rounded-lg w-full py-3 px-4 text-base text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            required>
                    </div>

                    <!-- End Time Input Field -->
                    <div>
                        <label class="block text-gray-700 text-lg font-medium mb-2" for="end_time">End Time</label>
                        <input type="time" name="end_time" id="end_time" 
                            class="shadow border border-gray-300 rounded-lg w-full py-3 px-4 text-base text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            required>
                    </div>

                    <!-- Buttons Section -->
                    <div class="col-span-2 flex justify-end mt-6">
                        <!-- Cancel Button -->
                        <button type="button" onclick="closeAddModal()" 
                            class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline mr-2">
                            Cancel
                        </button>
                        
                        <!-- Add Button -->
                        <button type="submit" name="create" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline">
                            Add Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>


        <!-- Update Subject Modal -->
        <div id="updateModal" class="fixed inset-0 hidden items-center justify-center z-50">
            <div class="bg-red-900 p-8 rounded-lg shadow-lg max-w-full sm:max-w-md lg:max-w-lg xl:max-w-xl" style="color:#e8e8e6;">
                <!-- Modal Title -->
                <h2 class="text-2xl font-semibold mb-6 uppercase" style="color:#e8e8e6;">Update Subject</h2>

                <!-- Form Section -->
                <form method="POST" class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Hidden Input for Subject ID -->
                    <input type="hidden" name="subject_id" id="update_subject_id">

                    <!-- Section Select Field -->
                    <div>
                        <label class="block text-gray-700 text-lg font-medium mb-2" for="update_section_id" style="color:#e8e8e6;">Section</label>
                        <select name="section_id" id="update_section_id"
                            class="shadow border border-gray-300 rounded-lg w-full px-4 py-2 border border-gray-300 text-lg text-base text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <?php foreach ($sections as $section): ?>
                                <option value="<?php echo $section['id']; ?>">
                                    <?php echo htmlspecialchars($section['section_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Code Input Field -->
                    <div>
                        <label class="block text-gray-700 text-lg font-medium mb-2" for="update_code" style="color:#e8e8e6;">Code</label>
                        <input type="text" name="code" id="update_code" 
                            class="shadow border border-gray-300 rounded-lg w-full px-4 py-2 border border-gray-300 text-lg text-base text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            required>
                    </div>

                    <!-- Subject Title Input Field -->
                    <div>
                        <label class="block text-gray-700 text-lg font-medium mb-2" for="update_subject_title" style="color:#e8e8e6;">Subject Title</label>
                        <input type="text" name="subject_title" id="update_subject_title" 
                            class="shadow border border-gray-300 rounded-lg w-full px-4 py-2 border border-gray-300 text-lg text-base text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            required>
                    </div>

                    <!-- Units Input Field -->
                    <div>
                        <label class="block text-gray-700 text-lg font-medium mb-2" for="update_units" style="color:#e8e8e6;">Units</label>
                        <input type="number" name="units" id="update_units" 
                            class="shadow border border-gray-300 rounded-lg w-full px-4 py-2 border border-gray-300 text-lg text-base text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            required>
                    </div>

                    <!-- Room Input Field -->
                    <div>
                        <label class="block text-gray-700 text-lg font-medium mb-2" for="update_room" style="color:#e8e8e6;">Room</label>
                        <input type="text" name="room" id="update_room" 
                            class="shadow border border-gray-300 rounded-lg w-full px-4 py-2 border border-gray-300 text-lg text-base text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            required>
                    </div>

                    <!-- Day Select Field -->
                    <div>
                        <label class="block text-gray-700 text-lg font-medium mb-2" for="update_day" style="color:#e8e8e6;">Day</label>
                        <select name="day" id="update_day" 
                            class="shadow border border-gray-300 rounded-lg w-full px-4 py-2 border border-gray-300 text-lg text-base text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            required>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>

                    <!-- Start Time Input Field -->
                    <div>
                        <label class="block text-gray-700 text-lg font-medium mb-2" for="update_start_time" style="color:#e8e8e6;">Start Time</label>
                        <input type="time" name="start_time" id="update_start_time" 
                            class="shadow border border-gray-300 rounded-lg w-full px-4 py-2 border border-gray-300 text-lg text-base text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            required>
                    </div>

                    <!-- End Time Input Field -->
                    <div>
                        <label class="block text-gray-700 text-lg font-medium mb-2" for="update_end_time" style="color:#e8e8e6;">End Time</label>
                        <input type="time" name="end_time" id="update_end_time" 
                            class="shadow border border-gray-300 rounded-lg w-full px-4 py-2 border border-gray-300 text-lg text-base text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            required>
                    </div>

                    <!-- Buttons Section -->
                    <div class="col-span-2 flex justify-end mt-6">
                        <!-- Cancel Button -->
                        <button type="button" onclick="closeUpdateModal()" 
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold mr-2 py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Cancel
                        </button>

                        <!-- Update Button -->
                        <button type="submit" name="update" 
                        class="bg-yellow-500 hover:bg-yellow-700 font-bold text-lg text-white py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            Update Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>
<!-- Subject Table -->
<div class="bg-transparent border-0">

    <form method="POST">
        <div class="flex justify-between items-center mb-4">
            <button type="button" onclick="openAddModal()" class="bg-red-900 hover:bg-red-800 text-lg font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" style="color:#e8e8e6;">
                Add New Subject
            </button>
            <button type="submit" name="delete_selected" class="bg-red-900 hover:bg-red-800 text-lg font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" style="color:#e8e8e6;">
                Delete Selected
            </button>
        </div>
        <h2 class="text-xl font-semibold mb-4">Subject List</h2>

        <table class="min-w-full table-auto border-collapse border border-gray-400" style="box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);">
            <thead>
                <tr class="bg-red-900 text-left" style="color:#e8e8e6;">
                    <th class="px-4 py-3 border border-gray-300 text-center">
                        <input type="checkbox" id="select_all" class="form-checkbox w-6 h-6">
                    </th>
                    <th class="px-4 py-2 border border-gray-300 text-lg">Code</th>
                    <th class="px-4 py-2 border border-gray-300 text-lg">Subject Title</th>
                    <th class="px-4 py-2 border border-gray-300 text-lg">Units</th>
                    <th class="px-4 py-2 border border-gray-300 text-lg">Room</th>
                    <th class="px-4 py-2 border border-gray-300 text-lg">Day</th>
                    <th class="px-4 py-2 border border-gray-300 text-lg">Start Time</th>
                    <th class="px-4 py-2 border border-gray-300 text-lg">End Time</th>
                    <th class="px-4 py-2 border border-gray-300 text-lg">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject): ?>
                    <tr>
                        <td class="px-2 py-1 border text-center border-gray-300" style=" border-color:#DFB8B8;">
                            <input type="checkbox" name="subject_ids[]" value="<?php echo $subject['id']; ?>" class="form-checkbox w-6 h-6">
                        </td>
                        <td class="px-2 py-1 border border-gray-300 text-lg" style=" border-color:#DFB8B8;"><?php echo htmlspecialchars($subject['code']); ?></td>
                        <td class="px-2 py-1 border border-gray-300 text-lg" style=" border-color:#DFB8B8;"><?php echo htmlspecialchars($subject['subject_title']); ?></td>
                        <td class="px-2 py-1 border border-gray-300 text-lg" style=" border-color:#DFB8B8;"><?php echo intval($subject['units']); ?></td>
                        <td class="px-2 py-1 border border-gray-300 text-lg" style=" border-color:#DFB8B8;"><?php echo htmlspecialchars($subject['room']); ?></td>
                        <td class="px-2 py-1 border border-gray-300 text-lg" style=" border-color:#DFB8B8;"><?php echo htmlspecialchars($subject['day']); ?></td>
                        <td class="px-2 py-1 border border-gray-300 text-lg" style=" border-color:#DFB8B8;">
                            <?php
                                $startTime = new DateTime($subject['start_time']);
                                echo $startTime->format('h:i A'); // Format to 12-hour time with AM/PM
                            ?>
                        </td>
                        <td class="px-4 py-3 border border-gray-300 text-lg" style=" border-color:#DFB8B8;">
                            <?php
                                $endTime = new DateTime($subject['end_time']);
                                echo $endTime->format('h:i A'); // Format to 12-hour time with AM/PM
                            ?>
                        </td>
                        <td class="px-4 py-3 border border-gray-300 text-lg" style=" border-color:#DFB8B8;">
                            <button type="button" onclick="openUpdateModal(<?php echo $subject['id']; ?>, '<?php echo htmlspecialchars($subject['code']); ?>', '<?php echo htmlspecialchars($subject['subject_title']); ?>', '<?php echo htmlspecialchars($subject['units']); ?>', '<?php echo htmlspecialchars($subject['room']); ?>', '<?php echo htmlspecialchars($subject['day']); ?>', '<?php echo htmlspecialchars($subject['start_time']); ?>', '<?php echo htmlspecialchars($subject['end_time']); ?>')" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded focus:outline-none focus:shadow-outline">
                                Edit
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>
</div>


<script>
    function toggleSubjects(sectionId, button) {
        var section = document.getElementById(sectionId);
        if (section.style.display === 'none') {
            section.style.display = '';
            button.innerText = '˅'; // Change icon to expanded state
        } else {
            section.style.display = 'none';
            button.innerText = '_'; // Change icon to minimized state
        }
    }

    function toggleSectionSubjects(sectionId, checkbox) {
        var subjects = document.querySelectorAll('#section-' + sectionId + ' input[type="checkbox"]');
        subjects.forEach(function(subjectCheckbox) {
            subjectCheckbox.checked = checkbox.checked;
        });
    }
</script>


            <!-- Pagination Controls -->
            <div class="flex justify-center mt-6">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1; ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mx-2">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i; ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mx-1 <?= $i == $page ? 'bg-gray-700' : ''; ?>">
                        <?= $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1; ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mx-2">Next</a>
                <?php endif; ?>
            </div>
                    </form>
                </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }

        function openUpdateModal(id, section_id, code, subject_title, units, room, day, start_time, end_time) {
            document.getElementById('update_subject_id').value = id;
            document.getElementById('update_section_id').value = section_id;
            document.getElementById('update_code').value = code;
            document.getElementById('update_subject_title').value = subject_title;
            document.getElementById('update_units').value = units;
            document.getElementById('update_room').value = room;
            document.getElementById('update_day').value = day;
            document.getElementById('update_start_time').value = start_time;
            document.getElementById('update_end_time').value = end_time;
            document.getElementById('updateModal').style.display = 'flex';
        }

        function closeUpdateModal() {
            document.getElementById('updateModal').style.display = 'none';
        }

        // Select/Deselect All Checkboxes
        document.getElementById('select_all').addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('input[name="subject_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
    </script>
</body>
</html>
