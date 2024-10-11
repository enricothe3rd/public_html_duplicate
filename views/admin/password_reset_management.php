<?php
// Include the database connection file
require '../../db/db_connection1.php';
session_start();

// Set number of records per page
$records_per_page = 10;

// Helper function to generate query params for pagination links
function paginationUrl($params) {
    $query = http_build_query(array_merge($_GET, $params));
    return '?' . $query;
}

// Pagination and data fetching for Password Resets
$page_password_resets = isset($_GET['page_password_resets']) ? (int)$_GET['page_password_resets'] : 1;
$start_password_resets = ($page_password_resets - 1) * $records_per_page;

$stmt_password_resets = $pdo->query("SELECT COUNT(*) FROM password_resets");
$total_password_resets = $stmt_password_resets->fetchColumn();
$total_pages_password_resets = ceil($total_password_resets / $records_per_page);

$stmt_password_resets = $pdo->prepare("SELECT id, email, token, created_at, expires_at FROM password_resets LIMIT :start, :records_per_page");
$stmt_password_resets->bindParam(':start', $start_password_resets, PDO::PARAM_INT);
$stmt_password_resets->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
$stmt_password_resets->execute();
$password_resets = $stmt_password_resets->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Password Reset Management Dashboard</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
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

        <!-- Display Password Resets Table -->
        <h2 class="text-xl font-bold mt-8 mb-4">Password Resets</h2>
        <form method="post" action="delete_password_resets.php">
            <table class="min-w-full bg-white shadow-md rounded my-6">
                <thead>
                    <tr class="bg-gray-800 text-white text-left">
                        <th class="py-3 px-4">
                            <input type="checkbox" id="select_all">
                        </th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Email</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Token</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Created At</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Expires At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($password_resets as $reset): ?>
                        <tr class="text-gray-700">
                            <td class="py-3 px-4">
                                <input type="checkbox" name="delete_ids[]" value="<?php echo $reset['id']; ?>">
                            </td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($reset['id']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($reset['email']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($reset['token']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($reset['created_at']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($reset['expires_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="bg-red-600 text-white px-4 py-2 mt-4">Delete Selected</button>
        </form>

        <!-- Pagination for Password Resets -->
        <div class="flex justify-center items-center mt-6 space-x-2">
            <?php if ($page_password_resets > 1): ?>
                <a href="<?php echo paginationUrl(['page_password_resets' => $page_password_resets - 1]); ?>" class="px-4 py-2 bg-gray-300 text-gray-600">Previous</a>
            <?php endif; ?>

            <div class="flex space-x-2">
                <?php for ($i = 1; $i <= $total_pages_password_resets; $i++): ?>
                    <a href="<?php echo paginationUrl(['page_password_resets' => $i]); ?>"
                       class="px-4 py-2 <?php echo $i == $page_password_resets ? 'bg-blue-500 text-white' : 'bg-gray-300 text-gray-600'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>

            <?php if ($page_password_resets < $total_pages_password_resets): ?>
                <a href="<?php echo paginationUrl(['page_password_resets' => $page_password_resets + 1]); ?>" class="px-4 py-2 bg-gray-300 text-gray-600">Next</a>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>

<script>
    document.getElementById('select_all').addEventListener('click', function() {
        var checkboxes = document.querySelectorAll('input[name="delete_ids[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });
</script>
