
<?php
require '../../db/db_connection3.php';

class Course {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }
// Method to handle the course creation request
public function handleCreateCourseRequest() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $course_name = $_POST['course_name'];
        $department_id = $_POST['department_id'];

        // Validate course name to allow only alphanumeric characters and spaces
        if (!preg_match('/^[a-zA-Z0-9\s]+$/', $course_name)) {
            // Redirect with an error message if validation fails
            header('Location: create_course.php?message=invalid_name');
            exit();
        }

        // Check if the course already exists
        if ($this->courseExists($course_name, $department_id)) {
            // Redirect with an error message if the course already exists
            header('Location: create_course.php?message=exists');
            exit();
        }

        // If it doesn't exist, create the course
        if ($this->createCourse($course_name, $department_id)) {
            // Redirect to a success page
            header('Location: create_course.php?message=success');
            exit();
        } else {
            // Redirect with an error message
            header('Location: create_course.php?message=failure');
            exit();
        }
    }
}

// Method to check if a course already exists
public function courseExists($name, $department_id) {
    try {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM courses WHERE course_name = :name AND department_id = :department_id');
        $stmt->execute([':name' => $name, ':department_id' => $department_id]);
        $count = $stmt->fetchColumn();
        return $count > 0; // Returns true if the course exists
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

// Method to create a new course
public function createCourse($name, $department_id) {
    try {
        $sql = "INSERT INTO courses (course_name, department_id) VALUES (:name, :department_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':name' => $name, ':department_id' => $department_id]);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}
















// Method to handle the course update request
public function handleUpdateCourseRequest($id) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $course_name = $_POST['course_name'];
        $department_id = filter_input(INPUT_POST, 'department_id', FILTER_SANITIZE_NUMBER_INT);

        // Validate course name
        if (!$this->isValidCourseName($course_name)) {
            header('Location: update_course.php?id=' . $id . '&message=invalid_name'); // Redirect with error message
            exit();
        }

        // Check if the course already exists
        if ($this->updateCourseExists($course_name, $department_id, $id)) {
            header('Location: update_course.php?id=' . $id . '&message=exists'); // Redirect with exists message
            exit();
        }

        // Update the course
        if ($this->updateCourse($id, $course_name, $department_id)) {
            header('Location: update_course.php?id=' . $id . '&message=success'); // Redirect to success page
            exit(); // Ensure the script exits after redirect
        } else {
            header('Location: update_course.php?message=failure'); // Redirect to failure page
            exit();
        }
    }
}

// Method to update course details
public function updateCourse($id, $name, $department_id) {
    $sql = "UPDATE courses SET course_name = :name, department_id = :department_id WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([':name' => $name, ':department_id' => $department_id, ':id' => $id]);
}

// Method to check if a course already exists
private function updateCourseExists($name, $department_id, $id) {
    $stmt = $this->db->prepare('SELECT * FROM courses WHERE course_name = :name AND department_id = :department_id AND id != :id');
    $stmt->execute([':name' => $name, ':department_id' => $department_id, ':id' => $id]);
    return $stmt->rowCount() > 0; // Return true if a course exists
}

// Method to validate the course name
private function isValidCourseName($course_name) {
    // Allow only alphanumeric characters and spaces
    return preg_match('/^[a-zA-Z0-9\s]+$/', $course_name); 
}

// // Method to retrieve all courses
// public function getCourses() {
//     $sql = "SELECT courses.id, courses.course_name, departments.name AS department_name
//             FROM courses
//             JOIN departments ON courses.department_id = departments.id";
//     $stmt = $this->db->query($sql);
//     return $stmt->fetchAll(PDO::FETCH_ASSOC);
// }

// Method to retrieve all courses
public function getCourses() {
    $sql = "SELECT courses.id, courses.course_name, departments.name AS department_name
            FROM courses
            JOIN departments ON courses.department_id = departments.id
            ORDER BY courses.course_name ASC"; // Sort by course_name
    $stmt = $this->db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Method to get a course by its ID
public function getCourseById($id) {
    $sql = "SELECT * FROM courses WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(); // Return the course details
}






    // New method to get departments
    public function getDepartments() {
        try {
            $sql = "SELECT id, name FROM departments";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }









    public function delete($id) {
        try {
            // Check if the course has associated sections
            if ($this->courseHasSections($id)) {
                // Redirect with a message that the course cannot be deleted
                header('Location: read_courses.php?id=' . $id . '&message=exists');
                exit();
            }
    
            // Proceed to delete the course if no associations are present
            $stmt = $this->db->prepare('DELETE FROM courses WHERE id = :id');
            $stmt->execute([':id' => $id]);
    
            // Redirect with a success message if deletion is successful
            header('Location: read_courses.php?id=' . $id . '&message=deleted');
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Method to check if a course has associated sections
    private function courseHasSections($id) {
        try {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM sections WHERE course_id = :id');
            $stmt->execute([':id' => $id]);
            $count = $stmt->fetchColumn();
            return $count > 0; // Returns true if there are associated sections
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    
}
?>
