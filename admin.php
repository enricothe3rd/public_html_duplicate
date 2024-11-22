<?php
// Include the Database class file (adjust the path if necessary)
require_once 'db/db_connection3.php';

// Call the connect method to get PDO instance
$pdo = Database::connect();




// Handle Adding New User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (strlen($password) < 8) { // Password must be at least 8 characters long
        $error_message = "Password must be at least 8 characters long.";
    } else {
        // Check if the email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $email_exists = $stmt->fetchColumn();

        if ($email_exists > 0) {
            // Email already exists, handle the error accordingly
            $error_message = "Error: Email already exists.";
        } else {
            // Email does not exist, proceed to insert the new user
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, role, status) VALUES (:email, :password, :role, :status)");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
                $success_message = "User added successfully!";
                // Redirect after successful POST using PRG pattern
                header("Location: admin.php?success=" . urlencode($success_message));
                exit(); // Ensure no further script execution
            } else {
                $error_message = "Error: Unable to add user.";
            }
        }
    }
}

// Handle Editing User
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        // Check if the email already exists for other users
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email AND id != :id");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $email_exists = $stmt->fetchColumn();

        if ($email_exists > 0) {
            // Email already exists, handle the error accordingly
            $error_message = "Error: Email already exists for another user.";
        } else {
            // Update user details
            $stmt = $pdo->prepare("UPDATE users SET email = :email, role = :role, status = :status WHERE id = :id");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                $success_message = "User updated successfully!";
                header("Location: admin.php?success=" . urlencode($success_message));
                exit(); // Ensure no further script execution
            } else {
                $error_message = "Error: Unable to update user.";
            }
        }
    }
}

// Handle Deleting User
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    // Check the user's status before deleting
    $stmt = $pdo->prepare("SELECT status FROM users WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['status'] !== 'active') {
        // Delete related records in user_registration first
        $stmt = $pdo->prepare("DELETE FROM user_registration WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $id);
        $stmt->execute();

        // Then delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    header("Location: admin.php");
    exit();
}

// Fetch All Users
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-transparent font-sans leading-normal tracking-normal">
    <div class="flex flex-col md:flex-row min-h-screen">


        <!-- Main Content -->
        <div class="flex-1 p-6">
            <h2 class="text-3xl font-bold mb-6 text-red-800">Manage Users</h2>

            <!-- Display Success/Error Messages -->
            <div id="message" class="mt-4 mb-2">
                <?php if (!empty($success_message)) : ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert" id="success-message">
                        <strong class="font-bold">Success!</strong>
                        <span class="block sm:inline"><?php echo $success_message; ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($error_message)) : ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert" id="error-message">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline"><?php echo $error_message; ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <script>
                // Function to hide message after a few seconds
                function hideMessage() {
                    const messageDiv = document.getElementById('message');
                    if (messageDiv) {
                        setTimeout(() => {
                            messageDiv.style.display = 'none';
                        }, 5000); // Hide after 5 seconds
                    }
                }
                // Call the hideMessage function
                hideMessage();
            </script>

           <!-- Add New User Form -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h3 class="text-xl font-semibold mb-4 text-red-600">Add New User</h3>
    <form action="admin.php" method="POST" class="space-y-4">
        <div>
            <label for="email" class="block text-red-600">Email</label>
            <input type="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300" required>
        </div>
        <div>
            <label for="password" class="block text-red-600">Password</label>
            <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300" required>
        </div>
        <div>
            <label for="role" class="block text-red-600">Role</label>
            <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300" required>
                <option value="" disabled selected>Select a role</option>
                <option value="student">Student</option>
                <option value="cashier">Cashier</option>
                <option value="college_department">College Department</option>
                <option value="registrar">Registrar</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div>
            <label for="status" class="block text-red-600">Status</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300" required>
                <option value="" disabled selected>Select a status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <button type="submit" name="add_user" class="w-full bg-red-800 text-white px-4 py-2 rounded-md hover:bg-red-700 transition">Add User</button>
    </form>
</div>


            <!-- Users Table -->
            <div class="bg-white rounded-lg shadow-md">
            <table class="w-full border-collapse shadow-md rounded-lg">
            <thead class="bg-red-800">
                        <tr>
                            <th class="px-4 py-4 border-b text-left text-white">Email</th>
                            <th class="px-4 py-4 border-b text-left text-white">Role</th>
                            <th class="px-4 py-4 border-b text-left text-white">Status</th>
                            <th class="px-4 py-4 border-b text-left text-white">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="border-b bg-red-50 hover:bg-red-200">
                            <td class="px-4 py-4"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="px-4 py-4"><?php echo htmlspecialchars($user['role']); ?></td>
                            <td class="px-4 py-4"><?php echo htmlspecialchars($user['status']); ?></td>
                            <td class="px-4 py-4">
                                <button class="bg-yellow-500 hover:bg-yellow-700 text-white font-semibold py-1 px-2 rounded edit-user" data-id="<?php echo $user['id']; ?>" data-email="<?php echo htmlspecialchars($user['email']); ?>" data-role="<?php echo htmlspecialchars($user['role']); ?>" data-status="<?php echo htmlspecialchars($user['status']); ?>">Edit</button>
                                <a href="?delete_id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');" class="bg-red-500 hover:bg-red-700 text-white font-semibold py-1 px-2 rounded">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Edit User Modal -->
            <div id="editModal" class="hidden fixed inset-0 flex justify-center items-center ">
                <div class="bg-white rounded-lg shadow-md p-6 w-11/12 md:w-1/3">
                    <h3 class="text-xl font-semibold mb-4">Edit User</h3>
                    <form action="admin.php" method="POST" id="editUserForm">
                        <input type="hidden" name="id" id="userId">
                        <div>
                            <label for="editEmail" class="block text-gray-700">Email</label>
                            <input type="email" name="email" id="editEmail" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300" required>
                        </div>
                        <div>
                            <label for="editRole" class="block text-gray-700">Role</label>
                            <select name="role" id="editRole" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300" required>
                                <option value="" disabled>Select a role</option>
                                <option value="student">Student</option>
                                <option value="cashier">Cashier</option>
                                <option value="college_department">College Department</option>
                                <option value="registrar">Registrar</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label for="editStatus" class="block text-gray-700">Status</label>
                            <select name="status" id="editStatus" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="mt-4 flex space-x-2">
                            <button type="submit" name="edit_user" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">Update User</button>
                            <button type="button" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition" onclick="closeModal()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                // Open Edit User Modal
                const editButtons = document.querySelectorAll('.edit-user');
                editButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const userId = this.getAttribute('data-id');
                        const userEmail = this.getAttribute('data-email');
                        const userRole = this.getAttribute('data-role');
                        const userStatus = this.getAttribute('data-status');

                        document.getElementById('userId').value = userId;
                        document.getElementById('editEmail').value = userEmail;
                        document.getElementById('editRole').value = userRole;
                        document.getElementById('editStatus').value = userStatus;

                        document.getElementById('editModal').classList.remove('hidden');
                    });
                });

                function closeModal() {
                    document.getElementById('editModal').classList.add('hidden');
                }
            </script>
        </div>
    </div>
</body>

</html>
