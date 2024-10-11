<?php
// Include your database connection
require '../../db/db_connection3.php';

class User {
    private $db;

    // Constructor to initialize the PDO connection
    public function __construct($db) {
        $this->db = $db;
    }

    // Function to retrieve user details by email
    public function getUserByEmail($email) {
        // SQL query to select user details by email
        $sql = "SELECT email, role, status, created_at, updated_at, profile_photo FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Function to change the password
    public function changePassword($email, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // SQL query to update the user's password
        $sql = "UPDATE users SET password = :password WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':email', $email);
        return $stmt->execute();
    }

    // Function to upload profile photo
    public function uploadProfilePhoto($email, $photo) {
        $targetDir = "../uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Extract the username from the email
        $username = explode('@', $email)[0];
        $fileName = $username . '_' . time() . '_' . basename($photo["name"]); // Include timestamp for uniqueness
        $targetFile = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if the file is an image
        $check = getimagesize($photo["tmp_name"]);
        if ($check === false) {
            return false; // Not an image
        }

        // Allow certain file formats
        $allowedTypes = ['jpg', 'png', 'jpeg', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            return false; // Invalid file type
        }

        // Get existing photo from the database
        // SQL query to select the current profile photo
        $sql = "SELECT profile_photo FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $existingPhoto = $stmt->fetchColumn();

        // If the user already has a profile photo, delete it
        if ($existingPhoto) {
            $existingFilePath = $targetDir . $existingPhoto;
            if (file_exists($existingFilePath)) {
                unlink($existingFilePath); // Delete the existing photo
            }
        }

        // Attempt to move the uploaded file
        if (move_uploaded_file($photo["tmp_name"], $targetFile)) {
            // Store the new file name in the database
            // SQL query to update the profile photo
            $sql = "UPDATE users SET profile_photo = :photo WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':photo', $fileName);
            $stmt->bindParam(':email', $email);
            if ($stmt->execute()) {
                // Redirect after successful upload
                header("Location: student_profile.php");
                exit(); // Stop further execution after redirection
            }
        }

        return false; // Upload failed
    }
}

// Initialize the PDO connection using the Database class
$pdo = Database::connect();

// Start session and get user email (assuming user email is stored in session)
session_start();
$email = $_SESSION['user_email']; // Make sure the session variable is set properly

// Create an instance of the User class
$userClass = new User($pdo);
$userData = $userClass->getUserByEmail($email);

// Store profile photo in session
if ($userData) {
    $_SESSION['profile_photo'] = $userData['profile_photo'];
}

// If the form is submitted to change the password or upload photo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_password'])) {
        // Change password logic
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password']; // Get confirm password

        if ($newPassword === $confirmPassword) { // Check if passwords match
            if ($userClass->changePassword($email, $newPassword)) {
                $successMessage = "Password changed successfully!";
            } else {
                $errorMessage = "Error changing password. Please try again.";
            }
        } else {
            $errorMessage = "Passwords do not match.";
        }
    } elseif (isset($_FILES['profile_photo'])) {
        // Upload photo logic
        if ($userClass->uploadProfilePhoto($email, $_FILES['profile_photo'])) {
            $successMessage = "Profile photo uploaded successfully!";
        } else {
            $errorMessage = "Error uploading profile photo. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <script>
        // Function to toggle password visibility
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }
    </script>
    <style>
        .photo {
            height: auto;
            width: 100%;
            max-height: 25rem; /* Maintain aspect ratio while limiting height */
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-gray-100">

<div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-lg mt-10">
    <h1 class="text-2xl font-bold mb-4 text-red-700 text-center">User Profile</h1>

    <!-- Display success or error messages -->
    <?php if (isset($successMessage)): ?>
        <div class="bg-green-500 text-white p-3 rounded mb-4"><?= $successMessage ?></div>
    <?php elseif (isset($errorMessage)): ?>
        <div class="bg-red-500 text-white p-3 rounded mb-4"><?= $errorMessage ?></div>
    <?php endif; ?>

    <!-- Display user details in responsive grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Profile Photo Column -->
        <div class="flex flex-col items-center">
            <?php if (!empty($userData['profile_photo'])): ?>
                <img src="<?= '../uploads/' . htmlspecialchars($userData['profile_photo']) ?>" alt="Profile Photo" class="photo mb-2">
            <?php else: ?>
                <p>No profile photo uploaded.</p>
            <?php endif; ?>

            <!-- Upload Photo Form -->
            <form action="" method="POST" enctype="multipart/form-data" class="mt-4 w-full">
                <h2 class="text-xl font-bold text-red-700 mb-2 text-center">Upload Profile Photo</h2>
                <div class="mb-4">
                    <input type="file" name="profile_photo" class="w-full p-2 border border-red-500 rounded" accept="image/*" required>
                </div>
                <button type="submit" class="w-full bg-red-700 text-white p-2 rounded">Upload Photo</button>
            </form>
        </div>

        <!-- User Details Column -->
        <div>
            <div class="mb-4">
                <label class="block text-red-700">Email:</label>
                <input type="email" class="w-full p-2 border border-red-200 rounded" value="<?= $userData['email'] ?>" disabled>
            </div>

            <div class="mb-4">
                <label class="block text-red-700">Role:</label>
                <input type="text" class="w-full p-2 border border-red-200 rounded" value="<?= $userData['role'] ?>" disabled>
            </div>

            <div class="mb-4">
                <label class="block text-red-700">Status:</label>
                <input type="text" class="w-full p-2 border border-red-200 rounded" value="<?= $userData['status'] ?>" disabled>
            </div>

            <form action="" method="POST" class="mt-4">
                <h2 class="text-xl font-bold text-red-700 mb-2 text-center">Change Password</h2>
                <div class="mb-4">
                    <label class="block text-red-700" for="new_password">New Password:</label>
                    <div class="relative">
                        <input type="password" name="new_password" id="new_password" class="w-full p-2 border border-red-200 rounded" required>
                        <i id="togglePassword" class="fas fa-eye-slash absolute right-2 top-2 cursor-pointer" onclick="togglePasswordVisibility('new_password', 'togglePassword')"></i>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-red-700" for="confirm_password">Confirm Password:</label>
                    <div class="relative">
                        <input type="password" name="confirm_password" id="confirm_password" class="w-full p-2 border border-red-200 rounded" required>
                        <i id="toggleConfirmPassword" class="fas fa-eye-slash absolute right-2 top-2 cursor-pointer" onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassword')"></i>
                    </div>
                </div>

                <button type="submit" class="w-full bg-red-700 text-white p-2 rounded">Change Password</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
