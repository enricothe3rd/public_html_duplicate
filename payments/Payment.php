<?php
require '../db/db_connection3.php'; // Include your database connection

class Payment {
    private $conn;

    public function __construct() {
        $this->conn = Database::connect(); // Assuming you have a Database class
    }

    public function create($data) {
        try {
            // Set values to null if payment method is cash
            if ($data['payment_method'] === 'cash') {
                $data['number_of_months_payment'] = null; // or ''
                $data['monthly_payment'] = null; // or ''
                $data['next_payment_due_date'] = null; // or ''
            }

                  // Set values to null if payment method is cash
                  if ($data['payment_method'] === 'installment') {
                    $data['total_payment'] = 0; // or ''
                }
    
            $sql = "INSERT INTO payments (student_number, number_of_units, amount_per_unit, miscellaneous_fee, total_payment, payment_method, transaction_id, number_of_months_payment, monthly_payment, next_payment_due_date, installment_down_payment)
                    VALUES (:student_number, :number_of_units, :amount_per_unit, :miscellaneous_fee, :total_payment, :payment_method, :transaction_id, :number_of_months_payment, :monthly_payment, :next_payment_due_date, :installment_down_payment)";
            $stmt = $this->conn->prepare($sql);
    
            // Bind parameters
            $stmt->bindParam(':student_number', $data['student_number']);
            $stmt->bindParam(':number_of_units', $data['number_of_units']);
            $stmt->bindParam(':amount_per_unit', $data['amount_per_unit']);
            $stmt->bindParam(':miscellaneous_fee', $data['miscellaneous_fee']);
            $stmt->bindParam(':total_payment', $data['total_payment']);
            $stmt->bindParam(':payment_method', $data['payment_method']);
            $stmt->bindParam(':transaction_id', $data['transaction_id']);
            $stmt->bindParam(':number_of_months_payment', $data['number_of_months_payment']);
            $stmt->bindParam(':monthly_payment', $data['monthly_payment']);
            $stmt->bindParam(':next_payment_due_date', $data['next_payment_due_date']);
            $stmt->bindParam(':installment_down_payment', $data['installment_down_payment']);
    
            // Log payment data
            error_log("Payment Data: " . print_r($data, true));
    
            if (!$stmt->execute()) {
                error_log("SQL Error: " . implode(", ", $stmt->errorInfo()));
                return false;
            }
    
            // Update the payment status to completed
            return $this->updatePaymentStatus($data['student_number']);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
    

    private function updatePaymentStatus($student_number) {
        try {
            $sql = "UPDATE payments SET payment_status = 'completed' WHERE student_number = :student_number";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':student_number', $student_number);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error while updating payment status: " . $e->getMessage());
            return false; // Return false on error
        }
    }

    public function read() {
        try {
            $sql = "SELECT * FROM payments";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return []; // Return empty array on error
        }
    }

    public function update($data) {
        try {
            $sql = "UPDATE payments SET
                    number_of_units = :number_of_units,
                    amount_per_unit = :amount_per_unit,
                    miscellaneous_fee = :miscellaneous_fee,
                    total_payment = :total_payment,
                    payment_method = :payment_method
                    WHERE student_number = :student_number"; // Update where student_number
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false; // Return false on error
        }
    }

    public function delete($id) {
        try {
            $sql = "DELETE FROM payments WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false; // Return false on error
        }
    }

    public function getEnrollmentDetails() {
        try {
            $sql = "SELECT units_price, miscellaneous_fee, months_of_payments FROM enrollment_payments LIMIT 1"; // Modify as needed
            $stmt = $this->conn->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return []; // Return empty array on error
        }
    }

    public function getNumberOfSubjects($student_number) {
        try {
            $sql = "SELECT COUNT(subject_id) AS number_of_subjects FROM subject_enrollments WHERE student_number = :student_number"; // Using the correct column name
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':student_number' => $student_number]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['number_of_subjects'];
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return 0; // Return 0 on error
        }
    }

    public function getTotalUnitsBySubject($subject_id) {
        try {
            $sql = "SELECT units AS total_units 
                    FROM subjects 
                    WHERE id = :subject_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':subject_id' => $subject_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_units'] ?? 0; // Return 0 if no units found
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return 0; // Return 0 on error
        }
    }

    public function getSubjectIdsByStudentNumber($student_number) {
        try {
            $sql = "SELECT subject_id 
                    FROM subject_enrollments 
                    WHERE student_number = :student_number";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':student_number' => $student_number]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Return all subject IDs
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return []; // Return empty array on error
        }
    }

    public function getTotalUnitsForStudent($student_number) {
        // Get all subject IDs for the student
        $subjectIds = $this->getSubjectIdsByStudentNumber($student_number);
        $totalUnits = 0;

        // Iterate through each subject ID and accumulate the total units
        foreach ($subjectIds as $subject) {
            $totalUnits += $this->getTotalUnitsBySubject($subject['subject_id']);
        }

        return $totalUnits; // Return the total units
    }
    
    // Method to get the school year based on student number
    public function getSchoolYear($student_number) {
        try {
            $stmt = $this->conn->prepare("SELECT school_year FROM enrollments WHERE student_number = ?");
            $stmt->execute([$student_number]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null; // Return null on error
        }
    }
    
    public function getMonthsOfPayments() {
        try {
            $pdo = Database::connect(); // Assuming you have a Database class for connection
            $query = "SELECT months_of_payments FROM enrollment_payments";
            $stmt = $pdo->query($query);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result; // Return all months of payments
        } catch (PDOException $e) {
            error_log("Error fetching months of payments: " . $e->getMessage());
            return []; // Return empty array on error
        }
    }

    
    // Method to check if a payment already exists for a student
    private function getPaymentByStudentNumber($student_number) {
        try {
            $sql = "SELECT * FROM payments WHERE student_number = :student_number";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':student_number' => $student_number]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null; // Return null on error
        }
    }
}
?>
