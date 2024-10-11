<?php
// Start the session
session_start();

// Include the database connection file
include 'db/db_connection1.php'; // Use your actual file path if different

// Database Class to handle the connection and queries
class Database {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Method to get student information
    public function getStudentByNumber($student_number) {
        $stmt = $this->pdo->prepare("SELECT * FROM enrollment WHERE student_number = ?");
        $stmt->execute([$student_number]);
        return $stmt->fetch();
    }

    // Method to get subject enrollments by student number
    public function getSubjectsByStudentNumber($student_number) {
        $stmt = $this->pdo->prepare("
            SELECT se.code, se.title, se.units, se.room, se.day, se.start_time, se.end_time 
            FROM subject_enrollments se
            INNER JOIN enrollment e ON e.student_id = se.student_id
            WHERE e.student_number = ?
        ");
        $stmt->execute([$student_number]);
        return $stmt->fetchAll();
    }
}

// Student Class to manage student details
class Student {
    private $db;
    private $student_data;

    public function __construct(Database $db, $student_number) {
        $this->db = $db;
        $this->student_data = $this->db->getStudentByNumber($student_number);
    }

    public function exists() {
        return $this->student_data !== false;
    }

    public function getStudentData() {
        return $this->student_data;
    }

    public function getSubjects() {
        return $this->db->getSubjectsByStudentNumber($this->student_data['student_number']);
    }
}


// Check if student_number is set in the session
if (isset($_SESSION['student_number'])) {
    $student_number = $_SESSION['student_number'];

    // Initialize the Database object
    $database = new Database($pdo);

    // Initialize the Student object
    $student = new Student($database, $student_number);

    // Check if student exists
    if ($student->exists()) {
        $student_data = $student->getStudentData();
        $subjects = $student->getSubjects();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Student Details</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-gray-100 flex justify-center items-center h-screen">
            <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-4">Student Information</h1>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Student Number</p>
                        <p class="text-lg font-semibold text-gray-800"><?php echo $student_data['student_number']; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Full Name</p>
                        <p class="text-lg font-semibold text-gray-800"><?php echo $student_data['firstname'] . " " . $student_data['middlename'] . " " . $student_data['lastname']; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Enrollment Status</p>
                        <p class="text-lg font-semibold text-gray-800"><?php echo $student_data['statusofenrollment']; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">School Year</p>
                        <p class="text-lg font-semibold text-gray-800"><?php echo $student_data['school_year']; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Semester</p>
                        <p class="text-lg font-semibold text-gray-800"><?php echo $student_data['semester']; ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Contact Number</p>
                        <p class="text-lg font-semibold text-gray-800"><?php echo $student_data['contact_no']; ?></p>
                    </div>
                </div>

                <h2 class="text-xl font-bold text-gray-800 mt-6 mb-4">Enrolled Subjects</h2>
                <?php if ($subjects) { ?>
                    <table class="w-full table-auto">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">Code</th>
                                <th class="px-4 py-2">Title</th>
                                <th class="px-4 py-2">Units</th>
                                <th class="px-4 py-2">Room</th>
                                <th class="px-4 py-2">Day</th>
                                <th class="px-4 py-2">Start Time</th>
                                <th class="px-4 py-2">End Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjects as $subject) { ?>
                                <tr>
                                    <td class="border px-4 py-2"><?php echo $subject['code']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $subject['title']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $subject['units']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $subject['room']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $subject['day']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $subject['start_time']; ?></td>
                                    <td class="border px-4 py-2"><?php echo $subject['end_time']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p class="text-red-500 font-semibold">No subjects found for this student.</p>
                <?php } ?>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "<div class='text-red-500 font-semibold'>No student found with the given student number.</div>";
    }
} else {
    echo "<div class='text-red-500 font-semibold'>No student number found in the session.</div>";
}
?>
