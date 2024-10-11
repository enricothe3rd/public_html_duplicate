<?php
session_start();
require '../../db/db_connection3.php';

$pdo = Database::connect();

// Initialize message variable
$message = '';
$suffix_name = ''; // Initialize suffix_name

// Check if there is a message set in session
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after displaying it
}

// Handle Create
if (isset($_POST['action']) && $_POST['action'] === 'create') {
    $suffix_name = trim($_POST['suffix_name']);
    $errors = [];

    // Validate input
    if (empty($suffix_name)) {
        $errors[] = "Suffix name is required.";
    } elseif (strlen($suffix_name) > 50) {
        $errors[] = "Suffix name must not exceed 50 characters.";
    } elseif (!preg_match("/^[a-zA-Z\s]*$/", $suffix_name)) {
        $_SESSION['message'] = 'invalid'; // Set message for invalid input
    }

    if (empty($errors) && !isset($_SESSION['message'])) {
        // Check if the suffix already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM suffixes WHERE suffix_name = :suffix_name");
        $stmt->bindParam(':suffix_name', $suffix_name);
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['message'] = 'exists'; // Set message if suffix already exists
        } else {
            $stmt = $pdo->prepare("INSERT INTO suffixes (suffix_name) VALUES (:suffix_name)");
            $stmt->bindParam(':suffix_name', $suffix_name);
            if ($stmt->execute()) {
                $_SESSION['message'] = 'success'; // Set message for success
            } else {
                $_SESSION['message'] = 'error'; // Set message for error
            }
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to refresh the page
            exit;
        }
    }
}

// Handle Read
$suffixes = [];
$stmt = $pdo->query("SELECT * FROM suffixes");
$suffixes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Update
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = $_POST['suffix_id'];
    $suffix_name = trim($_POST['suffix_name']);
    $errors = [];

    // Validate input
    if (empty($suffix_name)) {
        $errors[] = "Suffix name is required.";
    } elseif (strlen($suffix_name) > 50) {
        $errors[] = "Suffix name must not exceed 50 characters.";
    } elseif (!preg_match("/^[a-zA-Z\s]*$/", $suffix_name)) {
        $_SESSION['message'] = 'invalid'; // Set message for invalid input
    }

    if (empty($errors) && !isset($_SESSION['message'])) {
        $stmt = $pdo->prepare("UPDATE suffixes SET suffix_name = :suffix_name WHERE id = :id");
        $stmt->bindParam(':suffix_name', $suffix_name);
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'updated'; // Set message for update success
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to refresh the page
            exit;
        } else {
            $_SESSION['message'] = 'error'; // Set message for error
        }
    }
}

// Handle Delete
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['suffix_id'];

    // Validate input
    if (empty($id)) {
        $_SESSION['message'] = 'error'; // Set message for error
    } else {
        $stmt = $pdo->prepare("DELETE FROM suffixes WHERE id = :id");
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'deleted'; // Set message for delete success
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to refresh the page
            exit;
        } else {
            $_SESSION['message'] = 'error'; // Set message for error
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Suffixes</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="container mx-auto max-w-4xl">
        <h2 class="text-2xl font-semibold text-red-800 mb-4">Manage Suffixes</h2>


    <!-- HTML for Message Display -->
<div class="mt-4">
    <?php if ($message == 'invalid'): ?>
        <div class="message bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Invalid Input</h2>
            <p>Please use only letters and spaces.</p>
        </div>
    <?php elseif ($message == 'exists'): ?>
        <div class="message bg-yellow-200 text-yellow-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Already Exists</h2>
            <p>The suffix '<?php echo htmlspecialchars($suffix_name); ?>' already exists.</p>
        </div>
    <?php elseif ($message == 'success'): ?>
        <div class="message bg-green-200 text-green-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Success</h2>
            <p>The suffix '<?php echo htmlspecialchars($suffix_name); ?>' has been added successfully.</p>
        </div>
    <?php elseif ($message == 'updated'): ?>
        <div class="message bg-blue-200 text-blue-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Updated</h2>
            <p>The suffix has been updated successfully.</p>
        </div>
    <?php elseif ($message == 'deleted'): ?>
        <div class="message bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Deleted</h2>
            <p>The suffix has been deleted successfully.</p>
        </div>
    <?php elseif ($message == 'error'): ?>
        <div class="message bg-red-200 text-red-700 p-4 rounded">
            <h2 class="text-lg font-semibold">Error</h2>
            <p>An error occurred while processing your request. Please try again.</p>
        </div>
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
        <!-- Form to add new suffix -->
        <form method="POST" class="mb-6 flex space-x-4 mt-2">
            <input type="hidden" name="action" value="create">
            <input type="text" name="suffix_name" placeholder="New Suffix" class="border border-red-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring focus:border-red-300" required>
            <button type="submit" class="bg-red-700 text-white px-6 py-2 rounded-lg shadow-lg hover:bg-red-600 transition duration-300">Add</button>
        </form>

        <!-- Suffixes Table -->
        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-lg">
            <thead class="bg-red-700 text-white uppercase text-sm leading-normal">
                <tr>
                    <th class="py-3 px-6 text-left">ID</th>
                    <th class="py-3 px-6 text-left">Suffix</th>
                    <th class="py-3 px-6 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                <?php foreach ($suffixes as $suffix): ?>
                <tr class="border-b bg-red-50 hover:bg-red-200">
                    <form method="POST">
                        <input type="hidden" name="suffix_id" value="<?php echo htmlspecialchars($suffix['id']); ?>">
                        <td class="py-3 px-6 border-b"><?php echo htmlspecialchars($suffix['id']); ?></td>
                        <td class="py-3 px-6 border-b">
                            <input type="text" name="suffix_name" value="<?php echo htmlspecialchars($suffix['suffix_name']); ?>" class="border border-red-300 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring focus:border-red-300" required>
                        </td>
                        <td class="py-3 px-6 text-center">
                            <input type="hidden" name="action" value="update">
                            <div class="flex items-center justify-center space-x-2">
                                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg hover:bg-green-700 transition duration-300">Update</button>
                                <button type="submit" name="action" value="delete" onclick="return confirmDelete();" class="bg-red-600 text-white px-4 py-2 rounded-lg shadow-lg hover:bg-red-700 transition duration-300">Delete</button>
                                </div>
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
        return confirm("Are you sure you want to delete this suffix?");
    }
</script>

