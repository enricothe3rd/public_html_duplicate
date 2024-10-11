<?php
require '../../db/db_connection3.php';

class Semester {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

// Method to create a new semester
public function handleCreateSemesterRequest() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $semester_name = $_POST['semester_name'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

 // Validation for allowed characters (only letters, numbers, hyphens, and one space between words)
if (!preg_match('/^[a-zA-Z0-9-]+(?: [a-zA-Z0-9-]+)*$/', $semester_name)) {
    $_SESSION['last_semester_name'] = $semester_name; // Store the invalid input
    header('Location: create_semester.php?message=invalid'); // Redirect with error message
    exit();
}


        // Check if the semester name already exists
        if ($this->semesterExists($semester_name)) {
            $_SESSION['last_semester_name'] = $semester_name; // Store the existing input
            header('Location: create_semester.php?message=exists'); // Redirect with error message
            exit();
        }

        // If all validations pass, try to create the semester
        if ($this->createSemester($semester_name, $start_date, $end_date)) {
            // Clear session values after successful creation
            unset($_SESSION['last_semester_name']);
            header('Location: create_semester.php?message=success'); // Redirect with error message
            exit();
        } else {
            $_SESSION['last_semester_name'] = $semester_name; // Store the input if creation fails
            header('Location: create_semester.php?message=creation_failed'); // Redirect with error message
            exit();
        }
    }
}

// Method to create a new semester in the database
public function createSemester($semester_name, $start_date, $end_date) {
    try {
        $sql = "INSERT INTO semesters (semester_name, start_date, end_date) VALUES (:semester_name, :start_date, :end_date)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':semester_name' => $semester_name, ':start_date' => $start_date, ':end_date' => $end_date]);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

// Method to check if the semester name already exists
public function semesterExists($semester_name) {
    $sql = "SELECT COUNT(*) FROM semesters WHERE semester_name = :semester_name";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':semester_name' => $semester_name]);
    return $stmt->fetchColumn() > 0; // Returns true if exists, false otherwise
}

// Method to update an existing semester
public function handleUpdateSemesterRequest($id) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $semester_name = trim($_POST['semester_name']);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        // Check if semester exists
        if (!$this->getSemesterById($id)) {
            header('Location: edit_semester.php?id=' . urlencode($id) . '&message=not_found'); // Redirect with error message
            exit();
        }

        // Validate input
        if (!$this->isValidSemesterName($semester_name)) {
            header('Location: edit_semester.php?id=' . urlencode($id) . '&message=invalid_name'); // Redirect with error message
            exit();
        }

        // Check if the semester name already exists
        if ($this->semesterNameExists($semester_name)) {
            header('Location: edit_semester.php?id=' . urlencode($id) . '&message=exists'); // Redirect with error message
            exit();
        }

        // Proceed to update
        if ($this->updateSemester($id, $semester_name, $start_date, $end_date)) {
            header('Location: edit_semester.php?id=' . urlencode($id) . '&message=updated_successfully'); // Redirect after update
            exit();
        } else {
            header('Location: edit_semester.php?id=' . urlencode($id) . '&message=update_failed'); // Redirect with error message
            exit();
        }
    }
}

// Method to check if a semester name already exists
private function semesterNameExists($semester_name) {
    $sql = "SELECT COUNT(*) FROM semesters WHERE semester_name = :semester_name";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':semester_name' => $semester_name]);
    return $stmt->fetchColumn() > 0; // Returns true if any record is found
}

// Method to validate semester name
private function isValidSemesterName($name) {
    // Allow letters, numbers, hyphens, and single spaces between words
    return preg_match('/^[A-Za-z0-9]+(?:[-\s][A-Za-z0-9]+)*$/', $name);
}

public function updateSemester($id, $semester_name, $start_date, $end_date) {
    try {
        $sql = "UPDATE semesters SET semester_name = :semester_name, start_date = :start_date, end_date = :end_date WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':semester_name' => $semester_name,
            ':start_date' => $start_date,
            ':end_date' => $end_date,
            ':id' => $id
        ]);
    } catch (PDOException $e) {
        // Log the error for debugging
        error_log("Error updating semester: " . $e->getMessage());
        return false;
    }
}

// Method to read all semesters
public function getSemesters() {
    $sql = "SELECT * FROM semesters";
    $stmt = $this->db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Method to get a semester by ID
public function getSemesterById($id) {
    $sql = "SELECT * FROM semesters WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

    // Method to delete a semester
    public function deleteSemester($id) {
        try {
            $sql = "DELETE FROM semesters WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}
?>
