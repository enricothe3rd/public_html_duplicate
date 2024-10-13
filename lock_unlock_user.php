<?php
require 'db/db_connection3.php'; // Ensure to replace with your actual database connection file

class UserAccount {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect(); // Assuming you have a Database class for connection
    }
    public function getAllUsers() {
        try {
            $stmt = $this->pdo->prepare("SELECT id, email, role, status, account_locked, lock_time, failed_attempts FROM users ORDER BY email ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    

    public function toggleLock($userId) {
        try {
            // Check the current account status
            $stmt = $this->pdo->prepare("SELECT account_locked FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch();

            if ($user) {
                $isLocked = $user['account_locked'];
                $newStatus = $isLocked ? 0 : 1; // Toggle lock status
                $lockTime = $newStatus ? date('Y-m-d H:i:s') : null; // Set lock time if locking

                // Prepare the update statement
                $updateStmt = $this->pdo->prepare("
                    UPDATE users 
                    SET account_locked = :account_locked, 
                        lock_time = :lock_time, 
                        updated_at = CURRENT_TIMESTAMP(),
                        failed_attempts = CASE WHEN :account_locked = 0 THEN 0 ELSE failed_attempts END
                    WHERE id = :id
                ");

                // Execute the update
                $updateStmt->execute([
                    ':account_locked' => $newStatus,
                    ':lock_time' => $lockTime,
                    ':id' => $userId
                ]);

                return true; // Success
            } else {
                return false; // User not found
            }
        } catch (PDOException $e) {
            return false; // Error
        }
    }
}

// Check if the request method is POST for locking/unlocking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];
    $userAccount = new UserAccount();
    $userAccount->toggleLock($userId);
    header('Location: lock_unlock_user.php'); // Redirect back to the user list
    exit;
}

$userAccount = new UserAccount();
$users = $userAccount->getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="font-sans leading-normal tracking-normal">
<div class="mt-10">
        <h2 class="text-2xl font-semibold text-red-800 mb-4">User List</h2>
        <table class="w-full border-collapse shadow-md rounded-lg">
        <thead class="bg-red-800">
                <tr >
                    <th class="px-4 py-4 border-b text-left text-white">ID</th>
                    <th class="px-4 py-4 border-b text-left text-white">Email</th>
                    <th class="px-4 py-4 border-b text-left text-white">Role</th>
                    <th class="px-4 py-4 border-b text-left text-white">Status</th>
                    <th class="px-4 py-4 border-b text-left text-white">Account Locked</th>
                    <th class="px-4 py-4 border-b text-left text-white">Lock Time</th>
                    <th class="px-4 py-4 border-b text-left text-white">Failed Attempts</th>
                    <th class="px-4 py-4 border-b text-left text-white">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr class="border-b bg-red-50 hover:bg-red-200">
                        <td class="px-4 py-4"><?= htmlspecialchars($user['id']) ?></td>
                        <td class="px-4 py-4"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="px-4 py-4"><?= htmlspecialchars($user['role']) ?></td>
                        <td class="px-4 py-4"><?= htmlspecialchars($user['status']) ?></td>
                        <td class="px-4 py-4"><?= $user['account_locked'] ? 'Yes' : 'No' ?></td>
                        <td class="px-4 py-4"><?= $user['lock_time'] ? htmlspecialchars($user['lock_time']) : 'N/A' ?></td>
                        <td class="px-4 py-4"><?= htmlspecialchars($user['failed_attempts']) ?></td>
                        <td class="px-4 py-4">
                            <form method="POST" action="" class="inline-block">
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                <button type="submit" class="bg-yellow-500 hover:bg-yellow-700 text-white font-semibold py-1 px-2 rounded">
                                    <?= $user['account_locked'] ? 'Unlock' : 'Lock' ?>
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
