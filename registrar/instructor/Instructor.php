<?php
require '../../db/db_connection3.php';

class Instructor {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }



    // public function readAll() {
    //     $sql = 'SELECT i.id, i.first_name, i.middle_name, i.last_name, i.suffix, i.email, i.department_id, i.course_id, i.section_id,
    //                     d.name AS department_name, c.course_name, s.name AS section_name, i.created_at, i.updated_at
    //              FROM instructors i
    //              LEFT JOIN departments d ON i.department_id = d.id
    //              LEFT JOIN courses c ON i.course_id = c.id
    //              LEFT JOIN sections s ON i.section_id = s.id';
    //     $stmt = $this->pdo->prepare($sql);
    //     $stmt->execute();
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }

    public function readAll() {
        $sql = 'SELECT i.id, i.first_name, i.middle_name, i.last_name, i.suffix, i.email, i.department_id, i.course_id, i.section_id,
                        d.name AS department_name, c.course_name, s.name AS section_name, i.created_at, i.updated_at
                 FROM instructors i
                 LEFT JOIN departments d ON i.department_id = d.id
                 LEFT JOIN courses c ON i.course_id = c.id
                 LEFT JOIN sections s ON i.section_id = s.id
                 ORDER BY i.last_name ASC'; // Alphabetize by last_name
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    


// Create instructor
public function handleInstructorCreation() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log("Form submitted"); // Check your PHP error log for this message

        // Retrieve and sanitize input data
        $firstName = trim($_POST['first_name']);
        $middleName = trim($_POST['middle_name']);
        $lastName = trim($_POST['last_name']);
        $suffix = trim($_POST['suffix']);
        $email = trim($_POST['email']);
        $departmentId = $_POST['department_id'];
        $courseId = $_POST['course_id'];
        $sectionId = $_POST['section_id'];

        // Validate names (allowing only letters and spaces)
        if (!preg_match('/^[a-zA-Z\s]+$/', $firstName) || 
            !preg_match('/^[a-zA-Z\s]*$/', $middleName) || 
            !preg_match('/^[a-zA-Z\s]+$/', $lastName) || 
            !preg_match('/^[a-zA-Z\s]*$/', $suffix)) {
            // Redirect with an error message if validation fails
            header('Location: create_instructor.php?message=invalid_name');
            exit();
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: create_instructor.php?message=invalid_email');
            exit();
        }

        // Store inputs in session for retrieval after redirect
        $_SESSION['last_first_name'] = $firstName;
        $_SESSION['last_middle_name'] = $middleName;
        $_SESSION['last_last_name'] = $lastName;
        $_SESSION['last_suffix'] = $suffix;
        $_SESSION['last_email'] = $email;
        $_SESSION['last_department_id'] = $departmentId;
        $_SESSION['last_course_id'] = $courseId;
        $_SESSION['last_section_id'] = $sectionId;

        // Check if the instructor already exists
        if ($this->instructorExists($email)) {
            header('Location: create_instructor.php?message=exists');
            exit();
        }

        // Attempt to create the instructor
        if ($this->createInstructor($firstName, $middleName, $lastName, $suffix, $email, $departmentId, $courseId, $sectionId)) {
            header('Location: create_instructor.php?message=success');
            exit();
        } else {
            header('Location: create_instructor.php?message=failure');
            exit();
        }
    }
}

// Method to check if the instructor already exists
public function instructorExists($email) {
    try {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM instructors WHERE email = :email');
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn() > 0; // Returns true if the instructor exists
    } catch (PDOException $e) {
        echo "Error checking instructor existence: " . $e->getMessage();
        return false;
    }
}

// Method to create a new instructor
public function createInstructor($firstName, $middleName, $lastName, $suffix, $email, $departmentId, $courseId, $sectionId) {
    try {
        $sql = "INSERT INTO instructors (first_name, middle_name, last_name, suffix, email, department_id, course_id, section_id)
                VALUES (:first_name, :middle_name, :last_name, :suffix, :email, :department_id, :course_id, :section_id)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':first_name' => $firstName,
            ':middle_name' => $middleName,
            ':last_name' => $lastName,
            ':suffix' => $suffix,
            ':email' => $email,
            ':department_id' => $departmentId,
            ':course_id' => $courseId,
            ':section_id' => $sectionId,
        ]);
    } catch (PDOException $e) {
        echo "Error creating instructor: " . $e->getMessage();
        return false;
    }
}










// Update instructor
public function handleInstructorUpdate($instructorId) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log("Update form submitted"); // Check your PHP error log for this message

        // Retrieve and sanitize input data
        $firstName = trim($_POST['first_name']);
        $middleName = trim($_POST['middle_name']);
        $lastName = trim($_POST['last_name']);
        $suffix = trim($_POST['suffix']);
        $email = trim($_POST['email']);
        $departmentId = $_POST['department_id'];
        $courseId = $_POST['course_id'];
        $sectionId = $_POST['section_id'];

        // Validate names (allowing only letters and spaces)
        if (!preg_match('/^[a-zA-Z\s]+$/', $firstName) || 
            !preg_match('/^[a-zA-Z\s]*$/', $middleName) || 
            !preg_match('/^[a-zA-Z\s]+$/', $lastName) || 
            !preg_match('/^[a-zA-Z\s]*$/', $suffix)) {
            // Redirect with an error message if validation fails
            header('Location: edit_instructor.php?id=' . $instructorId . '&message=invalid_name');
            exit();
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: edit_instructor.php?id=' . $instructorId . '&message=invalid_email');
            exit();
        }

        // Store inputs in session for retrieval after redirect
        $_SESSION['last_first_name'] = $firstName;
        $_SESSION['last_middle_name'] = $middleName;
        $_SESSION['last_last_name'] = $lastName;
        $_SESSION['last_suffix'] = $suffix;
        $_SESSION['last_email'] = $email;
        $_SESSION['last_department_id'] = $departmentId;
        $_SESSION['last_course_id'] = $courseId;
        $_SESSION['last_section_id'] = $sectionId;

        // Check if the instructor exists and not the same as the current one
        if ($this->instructorupdateExists($email, $instructorId)) {
            header('Location: edit_instructor.php?id=' . $instructorId . '&message=exists');
            exit();
        }

        // Attempt to update the instructor
        if ($this->updateInstructor($instructorId, $firstName, $middleName, $lastName, $suffix, $email, $departmentId, $courseId, $sectionId)) {
            header('Location: edit_instructor.php?id=' . $instructorId . '&message=success');
            exit();
        } else {
            header('Location: edit_instructor.php?id=' . $instructorId . '&message=failure');
            exit();
        }
    }
}

// Method to check if the instructor already exists (with the exception of the current instructor)
public function instructorupdateExists($email, $currentId) {
    try {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM instructors WHERE email = :email AND id != :current_id');
        $stmt->execute([':email' => $email, ':current_id' => $currentId]);
        return $stmt->fetchColumn() > 0; // Returns true if the instructor exists
    } catch (PDOException $e) {
        echo "Error checking instructor existence: " . $e->getMessage();
        return false;
    }
}

// Method to update an instructor's details
public function updateInstructor($instructorId, $firstName, $middleName, $lastName, $suffix, $email, $departmentId, $courseId, $sectionId) {
    try {
        $sql = "UPDATE instructors 
                SET first_name = :first_name, 
                    middle_name = :middle_name, 
                    last_name = :last_name, 
                    suffix = :suffix, 
                    email = :email, 
                    department_id = :department_id, 
                    course_id = :course_id, 
                    section_id = :section_id 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':first_name' => $firstName,
            ':middle_name' => $middleName,
            ':last_name' => $lastName,
            ':suffix' => $suffix,
            ':email' => $email,
            ':department_id' => $departmentId,
            ':course_id' => $courseId,
            ':section_id' => $sectionId,
            ':id' => $instructorId,
        ]);
    } catch (PDOException $e) {
        echo "Error updating instructor: " . $e->getMessage();
        return false;
    }
}
    // Read all instructors with department name
    public function read($id) {
        $sql = 'SELECT i.id, i.first_name, i.middle_name, i.last_name, i.suffix, i.email, i.department_id, i.course_id, i.section_id,
                        d.name AS department_name, c.course_name, s.name AS section_name
                 FROM instructors i
                 LEFT JOIN departments d ON i.department_id = d.id
                 LEFT JOIN courses c ON i.course_id = c.id
                 LEFT JOIN sections s ON i.section_id = s.id
                 WHERE i.id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }








// delete_instructor.php
public function delete($id) {
    try {
        // Check if the instructor has associated subjects
        if ($this->instructorHasSubjects($id)) {
            // Redirect with a message that the instructor cannot be deleted
            header('Location: read_instructors.php?id=' . $id . '&message=exists');
            exit();
        }

        // Proceed to delete the instructor if no subjects are associated
        $stmt = $this->pdo->prepare('DELETE FROM instructors WHERE id = :id');
        $stmt->execute([':id' => $id]);

        // Redirect with a success message if deletion is successful
        header('Location: read_instructors.php?id=' . $id . '&message=deleted');
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

// Method to check if an instructor has associated subjects
private function instructorHasSubjects($id) {
    try {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM instructor_subjects WHERE instructor_id = :id');
        $stmt->execute([':id' => $id]);
        $count = $stmt->fetchColumn();
        return $count > 0; // Returns true if there are associated subjects
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}



    // Fetch departments for dropdown
    public function getDepartments() {
        $query = "SELECT id, name FROM departments";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch courses for dropdown
    public function getCourses() {
        $query = "SELECT id, course_name FROM courses";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch sections for dropdown
    public function getSections() {
        $query = "SELECT id, name FROM sections";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch courses based on department
    public function getCoursesByDepartment($departmentId) {
        $query = "SELECT id, course_name FROM courses WHERE department_id = :department_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':department_id' => $departmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch sections based on course
    public function getSectionsByCourse($courseId) {
        $query = "SELECT id, name FROM sections WHERE course_id = :course_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':course_id' => $courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllEmails() {
        $stmt = $this->pdo->prepare("SELECT email FROM users");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
