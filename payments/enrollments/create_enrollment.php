<?php
session_start();
require 'Enrollment.php'; // Adjust the path as needed

$enrollment = new Enrollment();

$status_options = $enrollment->getStatusOptions();
$sex_options = $enrollment->getSexOptions();




$user_email = $_SESSION['user_email'] ?? '';
if (empty($user_email)) {
    echo "User email is not set in the session.";
    exit;
}

// Fetch existing enrollment data if applicable
$existingEnrollment = $enrollment->getEnrollmentByEmail($user_email);
$latest_student_number = $enrollment->getLatestStudentNumberByEmail($user_email);

// Store student number in session
if ($latest_student_number) {
    $_SESSION['student_number'] = $latest_student_number;
} else {
    $_SESSION['student_number'] = $enrollment->generateStudentNumber();
}

// Initialize all variables to an empty string
$lastname = $firstname = $middlename = $dob = $address = $contact_no = $sex = $suffix = $school_year = $status = $year = '';

// Check if there's an existing enrollment record
if ($existingEnrollment) {
    $lastname = $existingEnrollment['lastname'];
    $firstname = $existingEnrollment['firstname'];
    $middlename = $existingEnrollment['middlename'];
    $dob = $existingEnrollment['dob'];
    $address = $existingEnrollment['address'];
    $contact_no = $existingEnrollment['contact_no'];
    $sex = $existingEnrollment['sex'];
    $suffix = $existingEnrollment['suffix'];

    $status = $existingEnrollment['status'];
    $year = $existingEnrollment['year'];
}


?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- FontAwesome -->
    <title>Enrollment Form</title>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-3xl">
        <h2 class="text-2xl font-bold mb-6 text-center">Enrollment Form</h2>
            <!-- Display error message if set -->
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="bg-red-500 text-white p-4 rounded-md mb-4">
        <?php 
            echo $_SESSION['error_message']; 
            unset($_SESSION['error_message']); // Clear message after displaying it
        ?>
    </div>
<?php endif; ?>

<!-- Display success message if set -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="bg-green-500 text-white p-4 rounded-md mb-4">
        <?php 
            echo $_SESSION['success_message']; 
            unset($_SESSION['success_message']); // Clear message after displaying it
        ?>
    </div>
<?php endif; ?>

<form action="send_enrollment.php" method="POST" class="grid grid-cols-1 gap-4 sm:grid-cols-1 md:grid-cols-2">
       

            <!-- Student Number -->
            <div class="mb-4 col-span-1 sm:col-span-2 md:col-span-2 relative">
                <label for="student_number" class="block text-sm font-medium text-red-700">Student Number</label>
                <input type="text" name="student_number" id="student_number" value="<?= htmlspecialchars($latest_student_number ?: $enrollment->generateStudentNumber()) ?>" readonly class="mt-1 block w-full h-12 pl-10 border border-red-500 text-red-700 rounded shadow-sm bg-gray-100 focus:border-red-500 focus:ring-2 focus:ring-red-500">
                <i class="fas fa-id-card absolute top-10 left-3 text-red-500"></i>
            </div>
      
            <!-- Last Name -->
                       <div class="mb-4 col-span-1 relative">
                <label for="lastname" class="block text-sm font-medium text-red-700">Last Name</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <label for="lastname" class="px-3 text-red-700 font-medium"><i class="fas fa-user"></i></label>
                    <input type="text" name="lastname" id="lastname" value="<?= htmlspecialchars($lastname) ?>" required class="block w-full px-3 py-2 bg-red-50 text-red-800 border-none focus:outline-none focus:bg-red-100 focus:border-red-500 rounded-r-md capitalize">
                </div>
            </div>

            <!-- First Name -->
                       <div class="mb-4 col-span-1 relative">
                <label for="firstname" class="block text-sm font-medium text-red-700">First Name</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <label for="firstname" class="px-3 text-red-700 font-medium"><i class="fas fa-user"></i></label>
                    <input type="text" name="firstname" id="firstname" value="<?= htmlspecialchars($firstname) ?>" required class="block w-full px-3 py-2 bg-red-50 text-red-800 border-none focus:outline-none focus:bg-red-100 focus:border-red-500 rounded-r-md capitalize">
                </div>
            </div>

            <!-- Middle Name -->
                       <div class="mb-4 col-span-1 relative">
                <label for="middlename" class="block text-sm font-medium text-red-700">Middle Name</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <label for="middlename" class="px-3 text-red-700 font-medium"><i class="fas fa-user"></i></label>
                    <input type="text" name="middlename" id="middlename" value="<?= htmlspecialchars($middlename) ?>" class="block w-full px-3 py-2 bg-red-50 text-red-800 border-none focus:outline-none focus:bg-red-100 focus:border-red-500 rounded-r-md capitalize">
                </div>
            </div>

            <!-- Suffix -->
                       <div class="mb-4 col-span-1 relative">
                <label for="suffix" class="block text-sm font-medium text-red-700">Suffix (Optional)</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <label for="suffix" class="px-3 text-red-700 font-medium"><i class="fas fa-user-tag"></i></label>
                    <input type="text" name="suffix" id="suffix" value="<?= htmlspecialchars($suffix) ?>" class="block w-full px-3 py-2 bg-red-50 text-red-800 border-none focus:outline-none focus:bg-red-100 focus:border-red-500 rounded-r-md capitalize">
                </div>
            </div>

            <!-- Email -->
                       <div class="mb-4 col-span-1 relative">
                <label for="email" class="block text-sm font-medium text-red-700">Email Address</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <label for="email" class="px-3 text-red-700 font-medium"><i class="fas fa-envelope"></i></label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($user_email) ?>" readonly class="block w-full px-3 py-2 bg-red-50 text-red-800 border-none focus:outline-none focus:bg-red-100 focus:border-red-500 rounded-r-md cursor-not-allowed">
                </div>
            </div>

            <!-- Date of Birth -->
                       <div class="mb-4 col-span-1 relative">
                <label for="dob" class="block text-sm font-medium text-red-700">Date of Birth</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <label for="dob" class="px-3 text-red-700 font-medium"><i class="fas fa-calendar-alt"></i></label>
                    <input type="date" name="dob" id="dob" value="<?= htmlspecialchars($dob) ?>" required class="block w-full px-3 py-2 bg-red-50 text-red-800 border-none focus:outline-none focus:bg-red-100 focus:border-red-500 rounded-r-md">
                </div>
            </div>

            <!-- Address -->
                       <div class="mb-4 col-span-1 relative">
                <label for="address" class="block text-sm font-medium text-red-700">Address</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <label for="address" class="px-3 text-red-700 font-medium"><i class="fas fa-map-marker-alt"></i></label>
                    <input type="text" name="address" id="address" value="<?= htmlspecialchars($address) ?>" required class="block w-full px-3 py-2 bg-red-50 text-red-800 border-none focus:outline-none focus:bg-red-100 focus:border-red-500 rounded-r-md capitalize">
                </div>
            </div>

            <!-- Contact No -->
                       <div class="mb-4 col-span-1 relative">
                <label for="contact_no" class="block text-sm font-medium text-red-700">Contact No</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <label for="contact_no" class="px-3 text-red-700 font-medium"><i class="fas fa-phone"></i></label>
                    <input type="tel" name="contact_no" id="contact_no" value="<?= htmlspecialchars($contact_no) ?>" required class="block w-full px-3 py-2 bg-red-50 text-red-800 border-none focus:outline-none focus:bg-red-100 focus:border-red-500 rounded-r-md">
                </div>
            </div>

            <!-- Sex -->
                       <div class="mb-4 col-span-1 relative">
                <label for="sex" class="block text-sm font-medium text-red-700">Sex</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <label for="sex" class="px-3 text-red-700 font-medium"><i class="fas fa-venus-mars"></i></label>
                    <select name="sex" id="sex" required class="block w-full px-3 py-2 bg-red-50 text-red-800 border-none focus:outline-none focus:bg-red-100 focus:border-red-500 rounded-r-md">
                        <option value="" disabled>Select your sex</option>
                        <?php foreach ($sex_options as $option): ?>
                            <option value="<?= htmlspecialchars($option['sex_name']) ?>" <?= ($option['sex_name'] === $sex) ? 'selected' : '' ?>><?= htmlspecialchars($option['sex_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Status -->
                       <div class="mb-4 col-span-1 relative">
                <label for="status" class="block text-sm font-medium text-red-700">Status</label>
                <div class="flex items-center border border-red-300 rounded-md shadow-sm">
                    <label for="status" class="px-3 text-red-700 font-medium"><i class="fas fa-info-circle"></i></label>
                    <select name="status" id="status" required class="block w-full px-3 py-2 bg-red-50 text-red-800 border-none focus:outline-none focus:bg-red-100 focus:border-red-500 rounded-r-md">
                        <option value="" disabled selected>Select status</option>
                        <?php foreach ($status_options as $option): ?>
                            <option value="<?= htmlspecialchars($option['status_name']) ?>" <?= ($option['status_name'] === $status) ? 'selected' : '' ?>><?= htmlspecialchars($option['status_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

                               <div class="mb-4 col-span-1 relative">

            <label for="year" class="block text-sm font-medium text-red-700">Select Academic Year</label>
            <div class="flex items-center border border-red-300 rounded-md shadow-sm">   
            <label for="status" class="px-3 text-red-700 font-medium"><i class="fas fa-info-circle"></i></label>
            <select id="year" name="year" required class="block w-full px-3 py-2 bg-red-50 text-red-800 border-none focus:outline-none focus:bg-red-100 focus:border-red-500 rounded-r-md">
                    <option value="1">1st Year (Freshman)</option>
                    <option value="2">2nd Year (Sophomore)</option>
                    <option value="3">3rd Year (Junior)</option>
                    <option value="4">4th Year (Senior)</option>
                </select>
                </div>
                </div>


            <!-- Submit Button -->
            <div class="col-span-1 sm:col-span-2">
                <button type="submit" class="w-full bg-red-700 hover:bg-red-600 text-white font-bold py-2 px-4 rounded shadow-lg transition duration-200">
                    Submit Enrollment
                </button>
            </div>
        </form>
    </div>
</body>
</html>
