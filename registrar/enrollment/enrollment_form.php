<?php
session_start();

require '../../db/db_connection1.php';

class User {
    private $pdo;
    private $user_id;
    public $email;
    public $student_id;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->user_id = $_SESSION['user_id'];
    }

    public function validateSession() {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'student' && $_SESSION['user_role'] !== 'admin')) {
            header("Location: index.php");
            exit();
            
        }
    }

    public function fetchUserDetails() {
        $sql = "SELECT u.email, u.id AS student_id FROM users u WHERE u.id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            echo "Error: User not found.";
            exit();
        }

        $this->email = htmlspecialchars($result['email']);
        $this->student_id = $result['student_id'];
    }
}

class Enrollment {
    private $pdo;
    private $student_id;
    public $student_number;
    public $enrollment_data;

    public function __construct($pdo, $student_id) {
        $this->pdo = $pdo;
        $this->student_id = $student_id;
    }

    public function fetchEnrollmentData() {
        $sql = "SELECT * FROM enrollment WHERE student_id = :student_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':student_id', $this->student_id);
        $stmt->execute();
        $this->enrollment_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($this->enrollment_data) {
            $this->student_number = $this->enrollment_data['student_number'];
        } else {
            $this->student_number = 'SN-' . str_pad($this->student_id, 6, '0', STR_PAD_LEFT); // Generate a new student number
        }

        // Store the student_number in the session
        $_SESSION['student_number'] = $this->student_number;
    }

    

    public function saveEnrollmentData($post_data, $email) {
        // Basic server-side validation
        $errors = [
            'lastname' => '',
            'firstname' => '',
            'dob' => '',
            'address' => '',
            'contact_no' => '',
            'sex' => '',
            'suffix' => '', 
            'semester' => '',
            'school_year' => '',
            'middlename' => '',
            'status' => ''
        ];
    
        if (empty($post_data['lastname'])) {
            $errors['lastname'] = 'Last name is required.';
        } elseif (!preg_match("/^[a-zA-Z\s]+$/", $post_data['lastname'])) {
            $errors['lastname'] = 'Last name must contain only letters and spaces.';
        }
        
        if (empty($post_data['firstname'])) {
            $errors['firstname'] = 'First name is required.';
        } elseif (!preg_match("/^[a-zA-Z\s]+$/", $post_data['firstname'])) {
            $errors['firstname'] = 'First name must contain only letters and spaces.';
        }
        
        if (empty($post_data['middlename'])) {
            $errors['middlename'] = 'Middle name is required or enter N/A.';
        } elseif (!preg_match("/^[a-zA-Z\s]+$/", $post_data['middlename']) && strtolower($post_data['middlename']) !== 'n/a') {
            $errors['middlename'] = 'Middle name must contain only letters or be "N/A".';
        }
        
        if (empty($post_data['dob'])) {
            $errors['dob'] = 'Date of birth is required.';
        } elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $post_data['dob'])) {
            $errors['dob'] = 'Date of birth must be in the format YYYY-MM-DD.';
        }
        
        if (empty($post_data['address'])) {
            $errors['address'] = 'Address is required.';
        } elseif (strlen($post_data['address']) < 5) {
            $errors['address'] = 'Address must be at least 5 characters long.';
        }
        
        if (!preg_match('/^\d{11,12}$/', $post_data['contact_no'])) {
            $errors['contact_no'] = 'Contact number must be between 11 and 12 digits.';
        }
        
        // Check if sex is selected
        if (empty($post_data['sex']) || $post_data['sex'] === '') {
            $errors['sex'] = 'Select sex is required.';
        }
        
        // Check if suffix is selected
        if (empty($post_data['suffix']) || $post_data['suffix'] === '') {
            $errors['suffix'] = 'Select suffix is required or select N/A.';
        }
        
        // Check if school year is selected
        if (empty($post_data['school_year']) || $post_data['school_year'] === '') {
            $errors['school_year'] = 'Select school year is required.';
        }
        
        // Check if semester is selected
        if (empty($post_data['semester']) || $post_data['semester'] === '') {
            $errors['semester'] = 'Select semester is required.';
        }

        // Check if status is selected
        if (empty($post_data['status']) || $post_data['status'] === '') {
            $errors['status'] = 'Select status is required.';
        }
        
        if (array_filter($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['post_data'] = $post_data; // Save post data to session
            return false;
        }
    
        // Clear previous errors and post data
        unset($_SESSION['errors']);
        unset($_SESSION['post_data']);
    
        if ($this->enrollment_data) {
            // Update the existing record
            $sql = "UPDATE enrollment 
                    SET lastname = :lastname, firstname = :firstname, middlename = :middlename, suffix = :suffix, 
                        school_year = :school_year, semester = :semester, sex = :sex, dob = :dob, 
                        address = :address, contact_no = :contact_no, status = :status, student_number = :student_number,
                        email = :email
                    WHERE student_id = :student_id";
        } else {
            // Insert a new record
            $sql = "INSERT INTO enrollment (student_id, lastname, firstname, middlename, suffix, school_year, 
                                            semester, sex, dob, address, contact_no, status, student_number, email) 
                    VALUES (:student_id, :lastname, :firstname, :middlename, :suffix, :school_year, :semester, 
                            :sex, :dob, :address, :contact_no, :status, :student_number, :email)";
        }
    
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':student_id', $this->student_id);
        $stmt->bindValue(':lastname', $post_data['lastname']);
        $stmt->bindValue(':firstname', $post_data['firstname']);
        $stmt->bindValue(':middlename', $post_data['middlename'] === 'N/A' ? null : $post_data['middlename']); // Exclude 'N/A'
        $stmt->bindValue(':suffix', $post_data['suffix']);
        $stmt->bindValue(':school_year', $post_data['school_year']);
        $stmt->bindValue(':semester', $post_data['semester']);
        $stmt->bindValue(':sex', $post_data['sex']);
        $stmt->bindValue(':dob', $post_data['dob']);
        $stmt->bindValue(':address', $post_data['address']);
        $stmt->bindValue(':contact_no', $post_data['contact_no']);
        $stmt->bindValue(':status', $post_data['status']);
        $stmt->bindValue(':student_number', $this->student_number);
        $stmt->bindValue(':email', $email);
    
        if (!$stmt->execute()) {
            // Print any error information
            print_r($stmt->errorInfo());
            return false;
        }
        
        return true;
    }
}

class OptionsFetcher {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function fetchSchoolYears() {
        return $this->fetchOptions("SELECT DISTINCT year FROM school_years");
    }

    public function fetchSemesters() {
        return $this->fetchOptions("SELECT DISTINCT semester FROM semesters");
    }

    public function fetchSexOptions() {
        return $this->fetchOptions("SELECT DISTINCT sex_name FROM sex_options");
    }

    public function fetchStatusOptions() {
        return $this->fetchOptions("SELECT DISTINCT status_name FROM status_options");
    }

    public function fetchSuffixes() {
        return $this->fetchOptions("SELECT DISTINCT suffix_name FROM suffixes");
    }

    private function fetchOptions($sql) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Main logic
$user = new User($pdo);
$user->validateSession();
$user->fetchUserDetails();

$enrollment = new Enrollment($pdo, $user->student_id);
$enrollment->fetchEnrollmentData();

$optionsFetcher = new OptionsFetcher($pdo);
$school_years = $optionsFetcher->fetchSchoolYears();
$semesters = $optionsFetcher->fetchSemesters();
$sex_options = $optionsFetcher->fetchSexOptions();
$status_options = $optionsFetcher->fetchStatusOptions();
$suffixes = $optionsFetcher->fetchSuffixes();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($enrollment->saveEnrollmentData($_POST, $user->email)) {
     //   echo "Data successfully saved.";
        header("Location: select_course1.php?student_id=" . $user->student_id);
        exit();
    } else {
     //   echo "Error saving data. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration Form</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="bg-white p-6 max-w-7xl mx-auto">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800 mt-10">Student Registration Form</h2>
        <form method="POST" novalidate>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- Student Number (Readonly) -->
                <div class="mb-4">
                    <label for="student_number" class="block text-gray-700 mb-2">Student Number</label>
                    <input type="text" id="student_number" name="student_number" class="w-full border border-gray-300 rounded-lg px-3 py-2" 
                           value="<?php echo htmlspecialchars($enrollment->student_number); ?>" readonly>
                </div>
                
                <!-- Email (Readonly) -->
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" name="email" class="w-full border border-gray-300 rounded-lg px-3 py-2"
                           value="<?php echo htmlspecialchars($user->email); ?>" readonly>
                </div>

                <!-- Last Name -->
                <div class="mb-4">
                    <label for="lastname" class="block text-gray-700 mb-2">Last Name</label>
                    <input type="text" id="lastname" name="lastname" class="w-full border border-gray-300 rounded-lg px-3 py-2" 
                        value="<?php echo isset($_SESSION['post_data']['lastname']) ? htmlspecialchars($_SESSION['post_data']['lastname']) : ''; ?>" required>
                    <?php if (isset($_SESSION['errors']['lastname'])): ?>
                        <p class="text-red-500 text-sm mt-1" data-error="true"><?php echo htmlspecialchars($_SESSION['errors']['lastname']); ?></p>
                    <?php endif; ?>
                </div>

                <!-- First Name -->
                <div class="mb-4">
                    <label for="firstname" class="block text-gray-700 mb-2">First Name</label>
                    <input type="text" id="firstname" name="firstname" class="w-full border border-gray-300 rounded-lg px-3 py-2" 
                        value="<?php echo isset($_SESSION['post_data']['firstname']) ? htmlspecialchars($_SESSION['post_data']['firstname']) : ''; ?>" required>
                    <?php if (isset($_SESSION['errors']['firstname'])): ?>
                        <p class="text-red-500 text-sm mt-1" data-error="true"><?php echo htmlspecialchars($_SESSION['errors']['firstname']); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Middle Name -->
                <div class="mb-4">
                    <label for="middlename" class="block text-gray-700 mb-2">Middle Name</label>
                    <input type="text" id="middlename" name="middlename" class="w-full border border-gray-300 rounded-lg px-3 py-2"
                        value="<?php echo isset($_SESSION['post_data']['middlename']) ? htmlspecialchars($_SESSION['post_data']['middlename']) : ''; ?>">
                    <?php if (isset($_SESSION['errors']['middlename'])): ?>
                        <p class="text-red-500 text-sm mt-1" data-error="true"><?php echo htmlspecialchars($_SESSION['errors']['middlename']); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Suffix -->
                <div class="mb-4">
                    <label for="suffix" class="block text-gray-700 mb-2">Suffix</label>
                    <select id="suffix" name="suffix" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">Select Suffix</option>
                        <?php foreach ($suffixes as $suffix) : ?>
                            <option value="<?php echo htmlspecialchars($suffix['suffix_name']); ?>" 
                                <?php echo isset($_SESSION['post_data']['suffix']) && $_SESSION['post_data']['suffix'] === $suffix['suffix_name'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($suffix['suffix_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($_SESSION['errors']['suffix'])): ?>
                        <p class="text-red-500 text-sm mt-1" data-error="true"><?php echo htmlspecialchars($_SESSION['errors']['suffix']); ?></p>
                    <?php endif; ?>
                </div>


                <!-- School Year -->
                <div class="mb-4">
                    <label for="school_year" class="block text-gray-700 mb-2">School Year</label>
                    <select id="school_year" name="school_year" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">Select School Year</option>
                        <?php foreach ($school_years as $year) : ?>
                            <option value="<?php echo htmlspecialchars($year['year']); ?>" 
                                <?php echo isset($_SESSION['post_data']['school_year']) && $_SESSION['post_data']['school_year'] === $year['year'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($year['year']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($_SESSION['errors']['semester'])): ?>
                        <p class="text-red-500 text-sm mt-1" data-error="true"><?php echo htmlspecialchars($_SESSION['errors']['school_year']); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Semester -->
                <div class="mb-4">
                    <label for="semester" class="block text-gray-700 mb-2">Semester</label>
                    <select id="semester" name="semester" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">Select Semester</option>
                        <?php foreach ($semesters as $semester) : ?>
                            <option value="<?php echo htmlspecialchars($semester['semester']); ?>" 
                                <?php echo isset($_SESSION['post_data']['semester']) && $_SESSION['post_data']['semester'] === $semester['semester'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($semester['semester']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($_SESSION['errors']['semester'])): ?>
                        <p class="text-red-500 text-sm mt-1" data-error="true"><?php echo htmlspecialchars($_SESSION['errors']['semester']); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Sex -->
                <div class="mb-4">
                    <label for="sex" class="block text-gray-700 mb-2">Sex</label>
                    <select id="sex" name="sex" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">Select Sex</option>
                        <?php foreach ($sex_options as $sex) : ?>
                            <option value="<?php echo htmlspecialchars($sex['sex_name']); ?>" 
                                <?php 
                                // Retain selection if there are validation errors
                                if (isset($_SESSION['post_data']['sex']) && $_SESSION['post_data']['sex'] === $sex['sex_name']) {
                                    echo 'selected';
                                }
                                ?>
                            >
                                <?php echo htmlspecialchars($sex['sex_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($_SESSION['errors']['sex'])) : ?>
                        <p class="text-red-500" data-error="true"><?php echo htmlspecialchars($_SESSION['errors']['sex']); ?></p>
                    <?php endif; ?>
                </div>


                <!-- Date of Birth -->
                <div class="mb-4">
                    <label for="dob" class="block text-gray-700 mb-2">Date of Birth</label>
                    <input type="date" id="dob" name="dob" class="w-full border border-gray-300 rounded-lg px-3 py-2" 
                        value="<?php echo isset($_SESSION['post_data']['dob']) ? htmlspecialchars($_SESSION['post_data']['dob']) : ''; ?>" required>
                    <?php if (isset($_SESSION['errors']['dob'])): ?>
                        <p class="text-red-500 text-sm mt-1" data-error="true"><?php echo htmlspecialchars($_SESSION['errors']['dob']); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Address -->
                <div class="mb-4 col-span-2">
                    <label for="address" class="block text-gray-700 mb-2">Address</label>
                    <textarea id="address" name="address" class="w-full border border-gray-300 rounded-lg px-3 py-2" rows="3" required><?php echo isset($_SESSION['post_data']['address']) ? htmlspecialchars($_SESSION['post_data']['address']) : ''; ?></textarea>
                    <?php if (isset($_SESSION['errors']['address'])): ?>
                        <p class="text-red-500 text-sm mt-1" data-error="true"><?php echo htmlspecialchars($_SESSION['errors']['address']); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Contact Number -->
                <div class="mb-4">
                    <label for="contact_no" class="block text-gray-700 mb-2">Contact Number</label>
                    <input type="text" id="contact_no" name="contact_no" class="w-full border border-gray-300 rounded-lg px-3 py-2" 
                        value="<?php echo isset($_SESSION['post_data']['contact_no']) ? htmlspecialchars($_SESSION['post_data']['contact_no']) : ''; ?>" required>
                    <?php if (isset($_SESSION['errors']['contact_no'])): ?>
                        <p class="text-red-500 text-sm mt-1" data-error="true"><?php echo htmlspecialchars($_SESSION['errors']['contact_no']); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Status -->
                <div class="mb-4">
                    <label for="status" class="block text-gray-700 mb-2">Status</label>
                    <select id="status" name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">Select Status</option>
                        <?php foreach ($status_options as $option): ?>
                            <option value="<?php echo htmlspecialchars($option['status_name']); ?>" 
                                <?php echo isset($_SESSION['post_data']['status']) && $_SESSION['post_data']['status'] === $option['status_name'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($option['status_name'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($_SESSION['errors']['status'])): ?>
                        <p class="text-red-500 text-sm mt-1" data-error="true"><?php echo htmlspecialchars($_SESSION['errors']['status']); ?></p>
                    <?php endif; ?>
                </div>


            </div>

            <div class="mt-6 text-center">
                <button type="submit" class="bg-blue-500 text-white px-6 py-3 rounded-lg">Save</button>
            </div>
        </form>

        <?php
        // Clear session errors after displaying
        unset($_SESSION['errors']);
        ?>
    </div>
</body>
</html>
<script defer>
document.addEventListener("DOMContentLoaded", function() {
    // Select all error messages with the data-error attribute
    const errorMessages = document.querySelectorAll("[data-error]");

    // Set a timeout to remove error messages after 5 seconds (5000ms)
    setTimeout(function() {
        errorMessages.forEach(function(message) {
            message.style.display = 'none'; // Hide the error message
        });
    }, 10000); // Adjust the time (in milliseconds) as needed
});
</script>

