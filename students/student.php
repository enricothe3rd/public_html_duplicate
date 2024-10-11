<?php
require '../db/db_connection3.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Student {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    // Method to handle the student creation request
    public function handleCreateStudentRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $student_number = $_POST['student_number'];
            $first_name = $_POST['first_name'];
            $middle_name = $_POST['middle_name'] ?? null; // Handle optional field
            $last_name = $_POST['last_name'];
            $suffix = $_POST['suffix'] ?? null; // Handle optional field
            $student_type = $_POST['student_type'];
            $sex = $_POST['sex'];
            $dob = $_POST['dob'];
            $email = $_POST['email'];
            $contact_no = $_POST['contact_no'] ?? null; // Handle optional field

            if ($this->createStudent($student_number, $first_name, $middle_name, $last_name, $suffix, $student_type, $sex, $dob, $email, $contact_no)) {
                header('Location: read_students.php'); // Redirect to a success page
                exit();
            } else {
                echo 'Failed to create student.';
            }
        }
    }

    public function createStudent($student_number, $first_name, $middle_name, $last_name, $suffix, $student_type, $sex, $dob, $email, $contact_no) {
        try {
            $sql = "INSERT INTO students (student_number, first_name, middle_name, last_name, suffix, student_type, sex, dob, email, contact_no) 
                    VALUES (:student_number, :first_name, :middle_name, :last_name, :suffix, :student_type, :sex, :dob, :email, :contact_no)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':student_number' => $student_number,
                ':first_name' => $first_name,
                ':middle_name' => $middle_name,
                ':last_name' => $last_name,
                ':suffix' => $suffix,
                ':student_type' => $student_type,
                ':sex' => $sex,
                ':dob' => $dob,
                ':email' => $email,
                ':contact_no' => $contact_no
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    // Method to handle the student update request
    public function handleUpdateStudentRequest($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $student_number = $_POST['student_number'];
            $first_name = $_POST['first_name'];
            $middle_name = $_POST['middle_name'] ?? null;
            $last_name = $_POST['last_name'];
            $suffix = $_POST['suffix'] ?? null;
            $student_type = $_POST['student_type'];
            $sex = $_POST['sex'];
            $dob = $_POST['dob'];
            $email = $_POST['email'];
            $contact_no = $_POST['contact_no'] ?? null;

            if ($this->updateStudent($id, $student_number, $first_name, $middle_name, $last_name, $suffix, $student_type, $sex, $dob, $email, $contact_no)) {
                header('Location: read_students.php');
                exit();
            } else {
                echo 'Failed to update student.';
            }
        }
    }

    public function updateStudent($id, $student_number, $first_name, $middle_name, $last_name, $suffix, $student_type, $sex, $dob, $email, $contact_no) {
        $sql = "UPDATE students SET student_number = :student_number, first_name = :first_name, middle_name = :middle_name, last_name = :last_name, suffix = :suffix, student_type = :student_type, sex = :sex, dob = :dob, email = :email, contact_no = :contact_no WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':student_number' => $student_number,
            ':first_name' => $first_name,
            ':middle_name' => $middle_name,
            ':last_name' => $last_name,
            ':suffix' => $suffix,
            ':student_type' => $student_type,
            ':sex' => $sex,
            ':dob' => $dob,
            ':email' => $email,
            ':contact_no' => $contact_no,
            ':id' => $id
        ]);
    }

    public function getStudents() {
        $sql = "SELECT * FROM students";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentById($id) {
        $sql = "SELECT * FROM students WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function deleteStudent($id) {
        $sql = "DELETE FROM students WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
?>
