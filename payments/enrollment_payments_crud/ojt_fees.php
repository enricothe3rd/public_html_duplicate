<?php
require '../../db/db_connection3.php'; // Ensure you have your DB connection
session_start(); // Start the session

// Get the PDO instance from your Database class
$pdo = Database::connect();

// Create the subject_ojt_fees table if it doesn't exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS subject_ojt_fees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject_id INT NOT NULL,
        ojt_fee DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (subject_id) REFERENCES subjects(id)
    )");
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}

// Fetch subjects and their corresponding section names from the database
$subjects = [];
try {
    $stmt = $pdo->query("
        SELECT s.id AS subject_id, s.title AS subject_title, sec.name AS section_name 
        FROM subjects s
        JOIN sections sec ON s.section_id = sec.id
    ");
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching subjects: " . $e->getMessage();
}

// Handle form submission for adding/updating OJT fee
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject_id = $_POST['subject_id'];
    $ojt_fee = $_POST['ojt_fee'];

    // Check if updating an existing fee
    if (!empty($_POST['ojt_fee_id'])) {
        $ojt_fee_id = $_POST['ojt_fee_id'];
        try {
            $stmt = $pdo->prepare("UPDATE subject_ojt_fees SET subject_id = :subject_id, ojt_fee = :ojt_fee WHERE id = :id");
            $stmt->execute(['subject_id' => $subject_id, 'ojt_fee' => $ojt_fee, 'id' => $ojt_fee_id]);
            $_SESSION['message'] = "OJT fee updated successfully.";
            $_SESSION['message_type'] = "success"; // Set message type
            header("Location: ojt_fees.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['message'] = "Error updating OJT fee: " . $e->getMessage();
            $_SESSION['message_type'] = "error"; // Set message type
        }
    } else {
        // Check if the subject_id and course_id combination already exists
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM subject_ojt_fees WHERE subject_id = :subject_id");
            $stmt->execute(['subject_id' => $subject_id]);
            $exists = $stmt->fetchColumn();

            if ($exists > 0) {
                // Subject payment already exists
                $_SESSION['message'] = "Payment for this subject already exists.";
                $_SESSION['message_type'] = "warning"; // Set message type
                header("Location: ojt_fees.php");
                exit;
            } else {
                // Insert the new OJT fee
                $stmt = $pdo->prepare("INSERT INTO subject_ojt_fees (subject_id, ojt_fee) VALUES (:subject_id, :ojt_fee)");
                $stmt->execute(['subject_id' => $subject_id, 'ojt_fee' => $ojt_fee]);
                $_SESSION['message'] = "OJT fee added successfully.";
                $_SESSION['message_type'] = "success"; // Set message type
                header("Location: ojt_fees.php");
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['message'] = "Error checking for existing payment: " . $e->getMessage();
            $_SESSION['message_type'] = "error"; // Set message type
        }
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM subject_ojt_fees WHERE id = :id");
        $stmt->execute(['id' => $delete_id]);
        $_SESSION['message'] = "OJT fee deleted successfully.";
        $_SESSION['message_type'] = "success"; // Set message type
        header("Location: ojt_fees.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error deleting OJT fee: " . $e->getMessage();
        $_SESSION['message_type'] = "error"; // Set message type
    }
}

// Fetch existing OJT fees with subject titles
$ojt_fees = [];
try {
    $stmt = $pdo->query("
        SELECT sof.id AS fee_id, s.title AS subject_title, sof.ojt_fee 
        FROM subject_ojt_fees sof
        JOIN subjects s ON sof.subject_id = s.id
    ");
    $ojt_fees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching OJT fees: " . $e->getMessage();
}

// Populate data for editing
$selected_fee = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM subject_ojt_fees WHERE id = :id");
        $stmt->execute(['id' => $edit_id]);
        $selected_fee = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error fetching OJT fee for edit: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OJT Fee Entry</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .fade-out {
            animation: fadeOut 1s forwards;
        }
        
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-100 p-6">

    <div class="max-w-lg mx-auto p-8 bg-white rounded-lg shadow-lg">
        <!-- Display message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div id="message" class="bg-<?= $_SESSION['message_type'] === 'success' ? 'green' : ($_SESSION['message_type'] === 'warning' ? 'yellow' : 'red') ?>-100 border border-<?= $_SESSION['message_type'] === 'success' ? 'green' : ($_SESSION['message_type'] === 'warning' ? 'yellow' : 'red') ?>-400 text-<?= $_SESSION['message_type'] === 'success' ? 'green' : ($_SESSION['message_type'] === 'warning' ? 'yellow' : 'red') ?>-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold"><?= ucfirst($_SESSION['message_type']) ?>!</strong>
                <span class="block sm:inline"><?php echo $_SESSION['message']; ?></span>
                <span onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer">
                    <i class="fas fa-times"></i>
                </span>
            </div>
            <?php unset($_SESSION['message']); // Clear message after displaying ?>
            <script>
                // Automatically remove the message after 5 seconds
                setTimeout(() => {
                    const messageElement = document.getElementById('message');
                    if (messageElement) {
                        messageElement.classList.add('fade-out');
                        setTimeout(() => {
                            messageElement.style.display = 'none';
                        }, 1000); // Match this duration with the fade-out animation duration
                    }
                }, 3000); // Message duration in milliseconds
            </script>
        <?php endif; ?>

        <!-- OJT Fee Form -->
        <h1 class="text-3xl font-bold text-red-800 mb-6">
            <i class="fas fa-money-bill-wave"></i> <?= $selected_fee ? 'Edit OJT Fee' : 'Enter OJT Fee' ?>
        </h1>
        
        <form method="POST" action="" class="space-y-6">
            <!-- Hidden field for OJT Fee ID -->
            <input type="hidden" name="ojt_fee_id" value="<?= $selected_fee ? $selected_fee['id'] : '' ?>">

            <!-- Subject Selection -->
            <div>
                <label for="subject" class="block text-sm font-medium text-red-700">
                    <i class="fas fa-book"></i> Select Subject:
                </label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <select name="subject_id" id="subject" class="w-full h-10 px-3 border-none focus:outline-none" required>
                        <option value="" disabled <?= !$selected_fee ? 'selected' : '' ?>>-- Select a Subject --</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= $subject['subject_id'] ?>" <?= $selected_fee && $selected_fee['subject_id'] == $subject['subject_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($subject['subject_title']) . ' - ' . htmlspecialchars($subject['section_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- OJT Fee Input -->
            <div>
                <label for="ojt_fee" class="block text-sm font-medium text-red-700">
                    <i class="fas fa-dollar-sign"></i> OJT Fee:
                </label>
                <input type="number" name="ojt_fee" id="ojt_fee" value="<?= $selected_fee ? htmlspecialchars($selected_fee['ojt_fee']) : '' ?>" class="w-full h-10 px-3 py-2 border border-red-300 rounded-md focus:outline-none" step="0.01" required>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full h-10 bg-red-600 text-white font-semibold rounded-md hover:bg-red-700 focus:outline-none">
                <i class="fas fa-save"></i> <?= $selected_fee ? 'Update OJT Fee' : 'Add OJT Fee' ?>
            </button>
        </form>

        <!-- Existing OJT Fees Table -->
        <h2 class="text-2xl font-bold text-red-800 mt-8 mb-4">Existing OJT Fees</h2>
        <table class="min-w-full bg-white border border-red-300">
            <thead>
                <tr class="bg-red-700">
                    <th class="py-2 border text-white">Subject Title</th>
                    <th class="py-2 border text-white">OJT Fee</th>
                    <th class="py-2 border text-white">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ojt_fees as $fee): ?>
                    <tr class="bg-red-50">
                        <td class="py-2 border text-center"><?= htmlspecialchars($fee['subject_title']) ?></td>
                        <td class="py-2 border text-center"><?= htmlspecialchars($fee['ojt_fee']) ?></td>
                        <td class="py-2 border text-center">
                            <a href="?edit_id=<?= $fee['fee_id'] ?>" class="text-blue-600 hover:underline">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            |
                            <a href="?delete_id=<?= $fee['fee_id'] ?>" onclick="return confirm('Are you sure you want to delete this fee?');" class="text-red-600 hover:underline">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
</body>
</html>
