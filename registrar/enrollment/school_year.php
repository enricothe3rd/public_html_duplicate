<?php
session_start();
require '../../db/db_connection3.php';

// Check if user is logged in and has the correct role
// Uncomment if needed
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
//     header("Location: index.php"); // Redirect to login page
//     exit(); // Stop further execution
// }

$pdo = Database::connect();
$message = ''; // Initialize message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $school_year = filter_input(INPUT_POST, 'school_year', FILTER_SANITIZE_STRING);
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

        // Validation
        if (empty($school_year)) {
            $message = 'invalid';
        } else {
            switch ($action) {
                case 'create':
                    $sql = "INSERT INTO school_years (year) VALUES (:school_year)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':school_year' => $school_year]);
                    $message = 'success';
                    break;
                case 'update':
                    if (empty($id)) {
                        $message = 'error';
                    } else {
                        $sql = "UPDATE school_years SET year = :school_year WHERE id = :id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([':school_year' => $school_year, ':id' => $id]);
                        $message = 'updated';
                    }
                    break;
                case 'delete':
                    if (empty($id)) {
                        $message = 'error';
                    } else {
                        $sql = "DELETE FROM school_years WHERE id = :id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([':id' => $id]);
                        $message = 'deleted';
                    }
                    break;
                default:
                    $message = 'error';
                    break;
            }
        }
        
        // Redirect to the appropriate page after processing
        header("Location: school_year.php?message=$message&status_name=" . urlencode($school_year)); // Pass message and status name
        exit();
    }
}


// Fetch all school years
$sql = "SELECT * FROM school_years";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$school_years = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage School Years</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="container mx-auto max-w-4xl  ">
        <h1 class="text-2xl font-semibold text-red-800 mb-4">Manage School Years</h1>
        
        <div class="mt-4">
    <?php if (isset($_GET['message'])): ?>
        <?php
            $status_name = isset($_GET['status_name']) ? $_GET['status_name'] : '';
            $msg_type = $_GET['message'];
        ?>
        <?php if ($msg_type == 'invalid'): ?>
            <div class="message bg-red-200 text-red-700 p-4 rounded">
                <h2 class="text-lg font-semibold">Invalid Input</h2>
                <p>Please use only letters and spaces.</p>
            </div>
        <?php elseif ($msg_type == 'success'): ?>
            <div class="message bg-green-200 text-green-700 p-4 rounded">
                <h2 class="text-lg font-semibold">Success</h2>
                <p>The school year '<?php echo htmlspecialchars($status_name); ?>' has been added successfully.</p>
            </div>
        <?php elseif ($msg_type == 'updated'): ?>
            <div class="message bg-blue-200 text-blue-700 p-4 rounded">
                <h2 class="text-lg font-semibold">Updated</h2>
                <p>The school year has been updated successfully.</p>
            </div>
        <?php elseif ($msg_type == 'deleted'): ?>
            <div class="message bg-red-200 text-red-700 p-4 rounded">
                <h2 class="text-lg font-semibold">Deleted</h2>
                <p>The school year has been deleted successfully.</p>
            </div>
        <?php elseif ($msg_type == 'error'): ?>
            <div class="message bg-red-200 text-red-700 p-4 rounded">
                <h2 class="text-lg font-semibold">Error</h2>
                <p>An error occurred while processing your request. Please try again.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    // Hide message after a few seconds
    setTimeout(function() {
        const messages = document.querySelectorAll('.message');
        messages.forEach(message => {
            message.style.display = 'none'; // Hide the message
        });
    }, 3000); // Adjust time in milliseconds (5000 ms = 5 seconds)
</script>

        <!-- Form to add new school year -->
        <form method="POST" class="mb-6 flex space-x-4 mt-2">
            <input type="hidden" name="action" value="create">
            <input type="text" name="school_year" placeholder="New School Year" class="border border-red-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring focus:border-red-300">
            <button type="submit" class="bg-red-700 text-white px-6 py-2 rounded-lg shadow-lg hover:bg-red-700 transition duration-300">Add</button>
        </form>

        <!-- School Years Table -->
        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-lg">
            <thead class="bg-red-700 text-white uppercase text-sm leading-normal">
                <tr>
                    <th class="py-3 px-6 text-left">ID</th>
                    <th class="py-3 px-6 text-left">Year</th>
                    <th class="py-3 px-6 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                <?php foreach ($school_years as $year): ?>
                <tr class="border-b bg-red-50 hover:bg-red-200">
                    <form method="POST">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($year['id']); ?>">
                        <td class="py-3 px-6 border-b"><?php echo htmlspecialchars($year['id']); ?></td>
                        <td class="py-3 px-6 border-b">
                            <input type="text" name="school_year" value="<?php echo htmlspecialchars($year['year']); ?>" class="border border-red-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring focus:border-red-300">
                        </td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center space-x-2">
                                <button type="submit" name="action" value="update" class="bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg hover:bg-green-700 transition duration-300">Update</button>
                                <button type="submit" name="action" value="delete" onclick="return confirmDelete();" class="bg-red-600 text-white px-4 py-2 rounded-lg shadow-lg hover:bg-red-700 transition duration-300">Delete</button>
                            </div>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<script>
    function confirmDelete() {
        return confirm("Are you sure you want to delete this school year?");
    }
</script>

