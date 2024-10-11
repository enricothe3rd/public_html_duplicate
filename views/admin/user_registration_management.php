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

// Pagination and data fetching for User Registrations
$page_user_registration = isset($_GET['page_user_registration']) ? (int)$_GET['page_user_registration'] : 1;
$start_user_registration = ($page_user_registration - 1) * $records_per_page;

$stmt_user_registration = $pdo->query("SELECT COUNT(*) FROM user_registration");
$total_user_registration = $stmt_user_registration->fetchColumn();
$total_pages_user_registration = ceil($total_user_registration / $records_per_page);

$stmt_user_registration = $pdo->prepare("SELECT id, user_id, token, type, created_at FROM user_registration LIMIT :start, :records_per_page");
$stmt_user_registration->bindParam(':start', $start_user_registration, PDO::PARAM_INT);
$stmt_user_registration->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
$stmt_user_registration->execute();
$user_registration = $stmt_user_registration->fetchAll(PDO::FETCH_ASSOC);

// Get message and type from session
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$messageType = isset($_SESSION['messageType']) ? $_SESSION['messageType'] : '';
$customIcon = isset($_SESSION['customIcon']) ? $_SESSION['customIcon'] : '';

unset($_SESSION['message']);
unset($_SESSION['messageIcon']);
unset($_SESSION['messageType']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration Management Dashboard</title>
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

        <!-- Display User Registrations Table -->
        <h2 class="text-xl font-bold mt-8 mb-4">User Registrations</h2>
        <form method="post" action="delete_registrations.php">
            <table class="min-w-full bg-white shadow-md rounded my-6">
                <thead>
                    <tr class="bg-gray-800 text-white text-left">
                        <th class="py-3 px-4">
                            <input type="checkbox" id="select_all_registrations">
                        </th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">ID</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">User ID</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Token</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Type</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Created At</th>
                        <th class="py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($user_registration as $registration): ?>
                        <tr class="text-gray-700">
                            <td class="py-3 px-4">
                                <input type="checkbox" name="delete_ids[]" value="<?php echo $registration['id']; ?>">
                            </td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($registration['id']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($registration['user_id']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($registration['token']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($registration['type']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($registration['created_at']); ?></td>
                            <td class="py-3 px-4">
                                <a href="delete_registration.php?id=<?php echo $registration['id']; ?>" class="text-red-600">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="bg-red-600 text-white px-4 py-2 mt-4">Delete Selected</button>
        </form>

        <!-- Pagination for User Registrations -->
        <div class="flex justify-center items-center mt-6 space-x-2">
            <?php if ($page_user_registration > 1): ?>
                <a href="<?php echo paginationUrl(['page_user_registration' => $page_user_registration - 1]); ?>" class="px-4 py-2 bg-gray-300 text-gray-600">Previous</a>
            <?php endif; ?>

            <div class="flex space-x-2">
                <?php for ($i = 1; $i <= $total_pages_user_registration; $i++): ?>
                    <a href="<?php echo paginationUrl(['page_user_registration' => $i]); ?>"
                       class="px-4 py-2 <?php echo $i == $page_user_registration ? 'bg-blue-500 text-white' : 'bg-gray-300 text-gray-600'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>

            <?php if ($page_user_registration < $total_pages_user_registration): ?>
                <a href="<?php echo paginationUrl(['page_user_registration' => $page_user_registration + 1]); ?>" class="px-4 py-2 bg-gray-300 text-gray-600">Next</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.getElementById('select_all_registrations').addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('input[name="delete_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
    </script>
</body>
</html>
