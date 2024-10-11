<?php
session_start(); // Start the session to store messages

// db_research_fee.php
require '../../db/db_connection3.php'; // Ensure you have your DB connection

$pdo = Database::connect();

// Create the subject_research_fees table if it doesn't exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS subject_research_fees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject_id INT NOT NULL,
        research_fee DECIMAL(10, 2) NOT NULL,
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

// Function to check if subject_id exists in the subjects table
function subjectExists($pdo, $subject_id) {
    $stmt = $pdo->prepare("SELECT id FROM subjects WHERE id = :subject_id");
    $stmt->execute(['subject_id' => $subject_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false; // Return true if exists, false if not
}

// Handle form submissions for creating, updating, and deleting
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject_id = $_POST['subject_id'];
    $research_fee = $_POST['research_fee'];
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create') {
            // Check if a research fee for the subject already exists
            $stmt = $pdo->prepare("SELECT * FROM subject_research_fees WHERE subject_id = :subject_id");
            $stmt->execute(['subject_id' => $subject_id]);
            $existingFee = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingFee) {
                // If a record already exists, prevent insertion and show a message
                $_SESSION['message'] = "Research fee for this subject already exists.";
                $_SESSION['message_type'] = "warning";
            } else {
                // Insert new record
                $stmt = $pdo->prepare("INSERT INTO subject_research_fees (subject_id, research_fee) VALUES (:subject_id, :research_fee)");
                $stmt->execute(['subject_id' => $subject_id, 'research_fee' => $research_fee]);
                $_SESSION['message'] = "Research fee created successfully.";
                $_SESSION['message_type'] = "success";
            }
            header("Location: research_fees.php");
            exit();

        } elseif ($action === 'update') {
            // Update existing record
            $fee_id = $_POST['fee_id'];
            $stmt = $pdo->prepare("UPDATE subject_research_fees SET subject_id = :subject_id, research_fee = :research_fee WHERE id = :fee_id");
            $stmt->execute(['subject_id' => $subject_id, 'research_fee' => $research_fee, 'fee_id' => $fee_id]);
            $_SESSION['message'] = "Research fee updated successfully.";
            $_SESSION['message_type'] = "success";
            header("Location: research_fees.php");
            exit();

        } elseif ($action === 'delete') {
            // Delete record
            $fee_id = $_POST['fee_id'];
            $stmt = $pdo->prepare("DELETE FROM subject_research_fees WHERE id = :fee_id");
            $stmt->execute(['fee_id' => $fee_id]);
            $_SESSION['message'] = "Research fee deleted successfully.";
            $_SESSION['message_type'] = "success";
            header("Location: research_fees.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        header("Location: research_fees.php");
        exit();
    }
}


// Fetch existing research fees for displaying in a table
$research_fees = [];
try {
    $stmt = $pdo->query("
        SELECT rf.id, rf.research_fee, rf.subject_id, s.title AS subject_title, sec.name AS section_name 
        FROM subject_research_fees rf
        JOIN subjects s ON rf.subject_id = s.id
        JOIN sections sec ON s.section_id = sec.id
    ");
    $research_fees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching research fees: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Fee Entry</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow-lg">
        <!-- Page Title -->
        <h1 class="text-3xl font-bold text-red-800 mb-6">
            <i class="fas fa-book"></i> Manage Research Fees
        </h1>

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

        
        <!-- Form for creating/updating research fee -->
        <form method="POST" action="" class="space-y-4">
            <!-- Hidden fields for fee ID and action -->
            <input type="hidden" name="fee_id" id="fee_id" value="">
            <input type="hidden" name="action" id="form_action" value="create">

            <!-- Subject Selection -->
            <div>
    <label for="subject" class="block text-sm font-medium text-red-700">
        <i class="fas fa-graduation-cap"></i> Select Subject:
    </label>
    <div class="flex items-center border border-red-300 rounded-md shadow-sm">
        <i class="fas fa-book-open px-3 text-red-500"></i>
        <!-- Apply max-height and overflow-y-auto for scrollable dropdown -->
        <select id="subject" name="subject_id" class="w-full h-10 px-3 py-2 focus:outline-none max-h-48 overflow-y-auto" required>
            <option value="">--Select a Subject--</option>
            <?php foreach ($subjects as $subject): ?>
                <option value="<?= htmlspecialchars($subject['subject_id']); ?>">
                    <?= htmlspecialchars($subject['subject_title']) . ' (Section: ' . htmlspecialchars($subject['section_name']) . ')'; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

            <!-- Research Fee Input -->
            <div>
                <label for="research_fee" class="block text-sm font-medium text-red-700">
                    <i class="fas fa-dollar-sign"></i> Research Fee (₱)
                </label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <span class="px-3 text-red-500">₱</span>
                    <input type="number" id="research_fee" name="research_fee" step="0.01"
                           class="w-full h-10 px-3 py-2 focus:outline-none" required>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    class="w-full bg-red-700 hover:bg-red-800 text-white font-bold py-3 px-4 rounded transition duration-200">
                <i class="fas fa-check-circle"></i> Submit
            </button>
        </form>

        <!-- Existing Research Fees Table -->
        <div class="mt-8">
            <h2 class="text-xl font-bold text-red-700"><i class="fas fa-list"></i> Existing Research Fees</h2>
            <table class="min-w-full mt-4 border border-red-300 rounded-md shadow-sm">
                <thead class="bg-red-800">
                    <tr>
                        <th class="border px-4 py-2 text-white">Subject</th>
                        <th class="border px-4 py-2 text-white">Section</th>
                        <th class="border px-4 py-2 text-white">Research Fee (₱)</th>
                        <th class="border px-4 py-2 text-white">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($research_fees as $fee): ?>
                        <tr class="bg-red-50">
                            <td class="border px-4 py-2"><?= htmlspecialchars($fee['subject_title']); ?></td>
                            <td class="border px-4 py-2"><?= htmlspecialchars($fee['section_name']); ?></td>
                            <td class="border px-4 py-2">₱<?= htmlspecialchars($fee['research_fee']); ?></td>
                            <td class="border px-4 py-2">
                                <form method="POST" action="" class="inline">
                                    <input type="hidden" name="fee_id" value="<?= htmlspecialchars($fee['id']); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="text-red-500 hover:underline">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </form>
                                <button type="button" class="text-blue-500 hover:underline ml-4"
                                        onclick="editFee(<?= htmlspecialchars($fee['id']); ?>, <?= htmlspecialchars($fee['subject_id']); ?>, '<?= htmlspecialchars($fee['research_fee']); ?>')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function editFee(id, subjectId, fee) {
            document.getElementById('fee_id').value = id;
            document.getElementById('subject').value = subjectId; // Use subjectId for the select value
            document.getElementById('research_fee').value = fee;
            document.getElementById('form_action').value = 'update';
        }
    </script>
</body>
</html>