<?php
require '../../db/db_connection3.php'; // Ensure you have a database connection file

class Enrollment {
    private $db;

    public function __construct() {
        $this->db = Database::connect(); // Assuming Database class handles the connection
    }
    
    public function generateStudentNumber() {
        $currentYear = date("Y");
        $latestNumber = $this->getLatestStudentNumber(); // A method to get the latest student number
    
        // Extract the numeric part from the latest number (if exists)
        if ($latestNumber) {
            $numericPart = (int)substr($latestNumber, -4); // Get the last 4 digits
        } else {
            $numericPart = 0; // Start from 0 if no previous numbers
        }
    
        // Increment the numeric part
        $numericPart++;
    
        // Format the new number to ensure it's 4 digits
        $formattedNumber = str_pad($numericPart, 4, '0', STR_PAD_LEFT);
    
        return 'STU' . $currentYear . $formattedNumber; // Concatenate 'STU', year, and the formatted number
    }
    
    public function getEnrollmentByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM enrollments WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getLatestStudentNumber() {
        $stmt = $this->db->query("SELECT student_number FROM enrollments ORDER BY student_number DESC LIMIT 1");
        $latest = $stmt->fetch(PDO::FETCH_ASSOC);
        return $latest ? $latest['student_number'] : null;
    }
    
    public function getLatestStudentNumberByEmail($email) {
        $query = "SELECT student_number FROM enrollments WHERE email = :email ORDER BY created_at DESC LIMIT 1"; // Adjust the table name and fields as necessary
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetchColumn(); // Returns the latest student_number
    }
    
    public function updateEnrollment($data) {
        $sql = "
            UPDATE enrollments SET 
                lastname = :lastname, 
                firstname = :firstname, 
                middlename = :middlename, 
                dob = :dob, 
                address = :address, 
                contact_no = :contact_no, 
                sex = :sex, 
                suffix = :suffix, 

                status = :status,
                year = :year
            WHERE email = :email"; // Adjust the condition as necessary
        
        $stmt = $this->db->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':lastname', $data['lastname']);
        $stmt->bindParam(':firstname', $data['firstname']);
        $stmt->bindParam(':middlename', $data['middlename']);
        $stmt->bindParam(':dob', $data['dob']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':contact_no', $data['contact_no']);
        $stmt->bindParam(':sex', $data['sex']);
        $stmt->bindParam(':suffix', $data['suffix']);
   
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':year', $data['year']);
        $stmt->bindParam(':email', $data['email']); // Use email as the condition for the update
        
        return $stmt->execute();
    }

    public function createEnrollment($data) {
        $sql = "
            INSERT INTO enrollments (student_number, lastname, firstname, middlename, email, dob, address, contact_no, sex, suffix, status, year)
            VALUES (:student_number, :lastname, :firstname, :middlename, :email, :dob, :address, :contact_no, :sex, :suffix, :status, :year)";

        $stmt = $this->db->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':student_number', $data['student_number']);
        $stmt->bindParam(':lastname', $data['lastname']);
        $stmt->bindParam(':firstname', $data['firstname']);
        $stmt->bindParam(':middlename', $data['middlename']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':dob', $data['dob']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':contact_no', $data['contact_no']);
        $stmt->bindParam(':sex', $data['sex']);
        $stmt->bindParam(':suffix', $data['suffix']);
   
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':year', $data['year']);
        return $stmt->execute();
    }

    public function getEnrollments() {
        $sql = "SELECT * FROM enrollments";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSchoolYears() {
        $stmt = $this->db->prepare("SELECT year FROM school_years ORDER BY year DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatusOptions() {
        $stmt = $this->db->prepare("SELECT status_name FROM status_options");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSexOptions() {
        $stmt = $this->db->prepare("SELECT sex_name FROM sex_options");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
