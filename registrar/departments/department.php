<?php
require '../../db/db_connection3.php';

class Department {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect(); // Use the static connect method
    }
// Create department
public function handleCreateDepartmentRequest() { 
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $established = $_POST['established'];
        $dean = $_POST['dean'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $location = $_POST['location'];
// Validate department name
if (empty($name)) {
    header('Location: create_department.php?message=empty_name');
    exit();
} elseif (!preg_match('/^[a-zA-Z0-9\s]+$/', $name)) {
    header('Location: create_department.php?message=invalid_name');
    exit();
}

// Validate dean
if (empty($dean)) {
    header('Location: create_department.php?message=empty_dean');
    exit();
} elseif (!preg_match('/^[a-zA-Z\s]+$/', $dean)) {
    header('Location: create_department.php?message=invalid_dean');
    exit();
}

// Validate email
if (empty($email)) {
    header('Location: create_department.php?message=empty_email');
    exit();
}elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@(gmail\.com|hotmail\.com|yahoo\.com|outlook\.com|icloud\.com)$/', $email)) {
    header('Location: create_department.php?message=invalid_email');
    exit();
}


// Validate phone number
if (empty($phone)) {
    header('Location: create_department.php?message=empty_phone');
    exit();
} elseif (!preg_match('/^(\\+63|09)[0-9]{9}$/', $phone)) {
    header('Location: create_department.php?message=invalid_phone');
    exit();
}

// Validate location
if (empty($location)) {
    header('Location: create_department.php?message=empty_location');
    exit();
} elseif (!preg_match('/^[a-zA-Z0-9\s,.-]+$/', $location)) {
    header('Location: create_department.php?message=invalid_location');
    exit();
}


        // Store inputs in session for retrieval after redirect
        session_start(); // Ensure session is started
        $_SESSION['last_department_name'] = $name;
        $_SESSION['last_established'] = $established;
        $_SESSION['last_dean'] = $dean;
        $_SESSION['last_email'] = $email;
        $_SESSION['last_phone'] = $phone;
        $_SESSION['last_location'] = $location;

        // Check if the department already exists
        if ($this->departmentExists($name)) {
            // Redirect with an error message
            header('Location: create_department.php?message=exists');
            exit();
        }

        // If it doesn't exist, create the department
        if ($this->create($name, $established, $dean, $email, $phone, $location)) {
            header('Location: create_department.php?message=success');
            exit();
        } else {
            header('Location: create_department.php?message=failure');
            exit();
        }
    }
}

// Method to check if department already exists
public function departmentExists($name) {
    try {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM departments WHERE name = :name');
        $stmt->execute([':name' => $name]);
        $count = $stmt->fetchColumn();
        return $count > 0; // Returns true if the department exists
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

// Method to create a new department
public function create($name, $established, $dean, $email, $phone, $location) {
    $sql = "INSERT INTO departments (name, established, dean, email, phone, location) 
            VALUES (:name, :established, :dean, :email, :phone, :location)";
    $stmt = $this->pdo->prepare($sql);

    try {
        $stmt->execute([
            'name' => $name,
            'established' => $established,
            'dean' => $dean,
            'email' => $email,
            'phone' => $phone,
            'location' => $location
        ]);
        return true; // Indicate success
    } catch (PDOException $e) {
        // Log the error message
        error_log($e->getMessage());
        return false; // Indicate failure
    }
}










// Update department
public function handleUpdateDepartmentRequest() { 
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id']; // Assuming the department ID is passed for updates
        $name = $_POST['name'];
        $established = $_POST['established'];
        $dean = $_POST['dean'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $location = $_POST['location'];

        // Validate department name
        if (empty($name)) {
            header('Location: update_department.php?message=empty_name&id=' . $id);
            exit();
        } elseif (!preg_match('/^[a-zA-Z0-9\s]+$/', $name)) {
            header('Location: update_department.php?message=invalid_name&id=' . $id);
            exit();
        }

        // Validate dean
        if (empty($dean)) {
            header('Location: update_department.php?message=empty_dean&id=' . $id);
            exit();
        } elseif (!preg_match('/^[a-zA-Z\s]+$/', $dean)) {
            header('Location: update_department.php?message=invalid_dean&id=' . $id);
            exit();
        }

        // Validate email
        if (empty($email)) {
            header('Location: update_department.php?message=empty_email&id=' . $id);
            exit();
        } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@(gmail\.com|hotmail\.com|yahoo\.com|outlook\.com|icloud\.com)$/', $email)) {
            header('Location: update_department.php?message=invalid_email&id=' . $id);
            exit();
        }

        // Validate phone number
        if (empty($phone)) {
            header('Location: update_department.php?message=empty_phone&id=' . $id);
            exit();
        } elseif (!preg_match('/^(\\+63|09)[0-9]{9}$/', $phone)) {
            header('Location: update_department.php?message=invalid_phone&id=' . $id);
            exit();
        }

        // Validate location
        if (empty($location)) {
            header('Location: update_department.php?message=empty_location&id=' . $id);
            exit();
        } elseif (!preg_match('/^[a-zA-Z0-9\s,.-]+$/', $location)) {
            header('Location: update_department.php?message=invalid_location&id=' . $id);
            exit();
        }

        // Store inputs in session for retrieval after redirect
        session_start(); // Ensure session is started
        $_SESSION['last_department_name'] = $name;
        $_SESSION['last_established'] = $established;
        $_SESSION['last_dean'] = $dean;
        $_SESSION['last_email'] = $email;
        $_SESSION['last_phone'] = $phone;
        $_SESSION['last_location'] = $location;

        // Check if the department exists (but allow the same name if it's the same department being updated)
        if ($this->departmentExistsWithDifferentId($name, $id)) {
            // Redirect with an error message if the department name already exists
            header('Location: update_department.php?message=exists&id=' . $id);
            exit();
        }

        // If the department exists, update it
        if ($this->update($id, $name, $established, $dean, $email, $phone, $location)) {
            header('Location: update_department.php?message=success&id=' . $id);
            exit();
        } else {
            header('Location: update_department.php?message=failure&id=' . $id);
            exit();
        }
    }
}

// Method to check if department name exists for a different department
public function departmentExistsWithDifferentId($name, $id) {
    try {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM departments WHERE name = :name AND id != :id');
        $stmt->execute([':name' => $name, ':id' => $id]);
        $count = $stmt->fetchColumn();
        return $count > 0; // Returns true if a different department has the same name
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

// Method to update an existing department
public function update($id, $name, $established, $dean, $email, $phone, $location) {
    $sql = "UPDATE departments 
            SET name = :name, established = :established, dean = :dean, email = :email, phone = :phone, location = :location 
            WHERE id = :id";
    $stmt = $this->pdo->prepare($sql);

    try {
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'established' => $established,
            'dean' => $dean,
            'email' => $email,
            'phone' => $phone,
            'location' => $location
        ]);
        return true; // Indicate success
    } catch (PDOException $e) {
        // Log the error message
        error_log($e->getMessage());
        return false; // Indicate failure
    }
}


public function getSectionById($id) {
    $stmt = $this->pdo->prepare("SELECT * FROM departments WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}






















    
    public function read() {
        $sql = "SELECT *, (SELECT COUNT(*) FROM instructors WHERE department_id = departments.id) as faculty_count FROM departments";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function delete($id) {
        try {
            // Check if the department has associated courses
            if ($this->departmentHasCourses($id)) {
                // Redirect with a message that the department cannot be deleted
                header('Location: read_departments.php?message=exists');
                exit();
            }
    
            // Proceed to delete the department if no courses are associated
            $stmt = $this->pdo->prepare('DELETE FROM departments WHERE id = :id');
            $stmt->execute([':id' => $id]);
    
            // Redirect with a success message if deletion is successful
            header('Location: read_departments.php?message=deleted');
            exit();
        } catch (PDOException $e) {
            // Log the error message instead of echoing it
            error_log("Error deleting department: " . $e->getMessage());
            // Redirect with a generic error message if needed
            header('Location: read_departments.php?message=error');
            exit();
        }
    }
    
    // Method to check if a department has associated courses
    private function departmentHasCourses($id) {
        try {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM courses WHERE department_id = :id');
            $stmt->execute([':id' => $id]);
            $count = $stmt->fetchColumn();
            return $count > 0; // Returns true if there are associated courses
        } catch (PDOException $e) {
            // Log the error message
            error_log("Error checking courses for department: " . $e->getMessage());
            return false; // Default to false if there's an error
        }
    }
    
    
    

    public function find($id) {
        $sql = "SELECT * FROM departments WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFacultyCount($departmentId) {
        $sql = "SELECT COUNT(*) as faculty_count FROM instructors WHERE department_id = :department_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['department_id' => $departmentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['faculty_count'];
    }

    public function getFacultyCountByDepartment($departmentId) {
        $query = "SELECT COUNT(*) FROM instructors WHERE department_id = :department_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['department_id' => $departmentId]);
        return $stmt->fetchColumn(); // Returns the count
    }

    // public function departmentExists($name) {
    //     $pdo = Database::connect(); // Assume you have a method to connect to your database
    //     $stmt = $pdo->prepare("SELECT COUNT(*) FROM departments WHERE name = :name");
    //     $stmt->execute([':name' => $name]);
    //     return $stmt->fetchColumn() > 0; // Returns true if department exists, false otherwise
    // }
}
?>
