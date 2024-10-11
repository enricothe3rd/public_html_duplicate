<?php
require '../../db/db_connection3.php';

session_start();
$user_email = $_SESSION['user_email'] ?? 'Guest';
$user_role = $_SESSION['user_role'] ?? 'User';

// Connect to the database
$db = Database::connect();

// Fetch instructors based on user email and role
$query = "SELECT * FROM instructors WHERE email = :email"; // Assuming email is unique
$stmt = $db->prepare($query);
$stmt->bindParam(':email', $user_email);
$stmt->execute();
$instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add the instructor_id to the session if an instructor is found
if (count($instructors) > 0) {
    // Assuming you want the first instructor's ID
    $_SESSION['instructor_id'] = $instructors[0]['id'];
}

// For testing: echo the instructor_id
$instructor_id = $_SESSION['instructor_id'] ?? 'Not set';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructors List</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mx-auto my-4">
        <h1 class="text-2xl font-bold">Instructors List</h1>
        <p>Welcome, <?php echo htmlspecialchars($user_email); ?></p>
        <p>Your Role: <?php echo htmlspecialchars($user_role); ?></p>
        
        <!-- Echoing the instructor_id for testing -->
        <p>Instructor ID: <?php echo htmlspecialchars($instructor_id); ?></p>

        <table class="min-w-full bg-white border border-gray-300 mt-4">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b">ID</th>
                    <th class="py-2 px-4 border-b">First Name</th>
                    <th class="py-2 px-4 border-b">Middle Name</th>
                    <th class="py-2 px-4 border-b">Suffix</th>
                    <th class="py-2 px-4 border-b">Last Name</th>
                    <th class="py-2 px-4 border-b">Email</th>
                    <th class="py-2 px-4 border-b">Department ID</th>
                    <th class="py-2 px-4 border-b">Created At</th>
                    <th class="py-2 px-4 border-b">Updated At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($instructors) > 0): ?>
                    <?php foreach ($instructors as $instructor): ?>
                        <tr>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($instructor['id']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($instructor['first_name']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($instructor['middle_name']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($instructor['suffix']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($instructor['last_name']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($instructor['email']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($instructor['department_id']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($instructor['created_at']); ?></td>
                            <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($instructor['updated_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="py-2 px-4 border-b text-center">No instructors found for this user.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
