<?php
require '../../db/db_connection1.php';
session_start();

// Set number of records per page
$records_per_page = 10;

// Helper function to generate query params for pagination links
function paginationUrl($params) {
    $query = http_build_query(array_merge($_GET, $params));
    return '?' . $query;
}

// Pagination and data fetching for Users
$page_users = isset($_GET['page_users']) ? (int)$_GET['page_users'] : 1;
$start_users = ($page_users - 1) * $records_per_page;

// Get total number of users
$stmt_users = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $stmt_users->fetchColumn();
$total_pages_users = ceil($total_users / $records_per_page);

// Fetch users with pagination
$stmt_users = $pdo->prepare("
    SELECT id, email, password, role, status, created_at, updated_at, email_confirmed, failed_attempts, account_locked, lock_time 
    FROM users 
    LIMIT :start, :records_per_page
");
$stmt_users->bindValue(':start', $start_users, PDO::PARAM_INT);
$stmt_users->bindValue(':records_per_page', $records_per_page, PDO::PARAM_INT);
$stmt_users->execute();
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Get message from session
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$messageType = isset($_SESSION['messageType']) ? $_SESSION['messageType'] : '';
$customIcon = isset($_SESSION['customIcon']) ? $_SESSION['customIcon'] : '';

unset($_SESSION['message']);
unset($_SESSION['messageType']);
unset($_SESSION['customIcon']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        #messageModal {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-100 p-6">

    <div class="container mx-auto">
        
        <!-- Success/Error Modal -->
        <?php if ($message): ?>
            <div id="messageModal" class="fixed inset-0 flex items-center justify-center z-50 <?php echo $messageType == 'success' ? 'animate__bounceIn' : 'animate__shakeX'; ?>">
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
                document.addEventListener('DOMContentLoaded', function() {
                    var messageModal = document.getElementById('messageModal');
                    if (messageModal) {
                        messageModal.style.display = 'flex';
                    }
                });
            </script>
        <?php endif; ?>

        <!-- Display Users Table -->
        <h2 class="text-xl font-bold mb-4">User Management</h2>
        <form method="post" action="delete_users.php">
            <table class="min-w-full bg-white shadow-md rounded my-6">
                <thead>
                    <tr class="bg-gray-800 text-white text-left">
                        <th class="py-3 px-4">
                            <input type="checkbox" id="select_all_users">
                        </th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Email</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Password</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Role</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Status</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Created At</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Updated At</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Email Confirmed</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Failed Attempts</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Account Locked</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Lock Time</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="text-gray-700">
                            <td class="py-3 px-4">
                                <input type="checkbox" name="delete_ids[]" value="<?php echo htmlspecialchars($user['id']); ?>">
                            </td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($user['id']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($user['password']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($user['role']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($user['status']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($user['created_at']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($user['updated_at']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($user['email_confirmed']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($user['failed_attempts']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($user['account_locked']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($user['lock_time']); ?></td>
                            <td class="py-3 px-4">
                                <!-- Edit Button -->
                                <button type="button" class="text-blue-600" onclick="openEditModal(<?php echo $user['id']; ?>, '<?php echo $user['email']; ?>', '<?php echo $user['role']; ?>', '<?php echo $user['status']; ?>', <?php echo $user['failed_attempts']; ?>)">Edit</button>
                                
                                <!-- Delete Button -->
                                <button type="button" class="text-red-600" onclick="openDeleteModal(<?php echo $user['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="bg-red-600 text-white px-4 py-2 mt-4">Delete Selected</button>
        </form>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
                <h2 class="text-xl font-bold mb-4">Edit User</h2>
                <form method="POST" action="edit_user.php">
                    <input type="hidden" name="id" id="editUserId">

                    <div class="mb-4">
                        <label for="editEmail" class="block text-sm font-semibold">Email</label>
                        <input type="email" name="email" id="editEmail" class="w-full px-4 py-2 border rounded">
                    </div>

                    <div class="mb-4">
                        <label for="editRole" class="block text-sm font-semibold">Role</label>
                        <select name="role" id="editRole" class="w-full px-4 py-2 border rounded">
                            <option value="Student">Student</option>
                            <option value="Cashier">Cashier</option>
                            <option value="College Department">College Department</option>
                            <option value="Registrar">Registrar</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="editStatus" class="block text-sm font-semibold">Status</label>
                        <select name="status" id="editStatus" class="w-full px-4 py-2 border rounded">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save Changes</button>
                    <button type="button" onclick="closeEditModal()" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-sm p-6">
                <h2 class="text-xl font-bold mb-4">Confirm Delete</h2>
                <p class="mb-4">Are you sure you want to delete this user?</p>
                <form method="POST" action="delete_user.php">
                    <input type="hidden" name="id" id="deleteUserId">
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Delete</button>
                    <button type="button" onclick="closeDeleteModal()" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openEditModal(id, email, role, status, failed_attempts) {
            document.getElementById('editUserId').value = id;
            document.getElementById('editEmail').value = email;
            document.getElementById('editRole').value = role;
            document.getElementById('editStatus').value = status;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function openDeleteModal(id) {
            document.getElementById('deleteUserId').value = id;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        document.getElementById('select_all_users').addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('input[name="delete_ids[]"]');
            for (var checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
        });
    </script>
</body>
</html>
