<?php
require '../../db/db_connection3.php';

class Section {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::connect();
    }

    //create section
    public function handleCreateSectionRequest() { 
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $course_id = $_POST['course_id'];
    
            // Validate the section name to allow only alphanumeric characters and hyphens
            if (!preg_match('/^[a-zA-Z0-9-]+$/', $name)) {
                // Redirect with an error message if validation fails
                header('Location: create_section.php?message=invalid_name');
                exit();
            }
    
            // Store inputs in session for retrieval after redirect
            $_SESSION['last_section_name'] = $name;
            $_SESSION['last_course_id'] = $course_id;
    
            // Check if the section already exists
            if ($this->sectionExists($name, $course_id)) {
                // Redirect with an error message
                header('Location: create_section.php?message=exists');
                exit();
            }
    
            // If it doesn't exist, create the section
            if ($this->create($name, $course_id)) {
                header('Location: create_section.php?message=success');
                exit();
            } else {
                header('Location: create_section.php?message=failure');
                exit();
            }
        }
    }

        //create section
    // Method to check if section already exists
    public function sectionExists($name, $course_id) {
        try {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM sections WHERE name = :name AND course_id = :course_id');
            $stmt->execute([':name' => $name, ':course_id' => $course_id]);
            $count = $stmt->fetchColumn();
            return $count > 0; // Returns true if the section exists
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
        //create section
    // Method to create a new section
    public function create($name, $course_id) {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO sections (name, course_id) VALUES (:name, :course_id)');
            return $stmt->execute([':name' => $name, ':course_id' => $course_id]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
        //create section
    // Method to fetch all courses
    public function getAllCourses() {
        try {
            $stmt = $this->pdo->query('SELECT * FROM courses');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
    




//Update sction
// Method to handle the section update request
public function handleUpdateSectionRequest() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'];  // Retrieve the section ID from POST data
        $name = $_POST['name'];
        $course_id = $_POST['course_id'];

        // Check if the section already exists
        if ($this->updatesectionExists($name, $course_id, $id)) {
            header('Location: update_section.php?id=' . $id . '&message=exists');
            exit();
        }

        if ($this->update($id, $name, $course_id)) {
            header('Location: update_section.php?id=' . $id . '&message=success'); // Redirect to a success page with section ID
            exit();
        } else {
            header('Location: create_section.php?message=failure');
            exit();
        }
    }
}

//Update sction

// Method to update section details
public function update($id, $name, $course_id) {
    try {
        $stmt = $this->pdo->prepare('UPDATE sections SET name = :name, course_id = :course_id WHERE id = :id');
        return $stmt->execute([':name' => $name, ':course_id' => $course_id, ':id' => $id]);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

//Update sction
// Method to check if a section already exists for the same course
private function updatesectionExists($name, $course_id, $id) {
    $stmt = $this->pdo->prepare('SELECT * FROM sections WHERE name = :name AND course_id = :course_id AND id != :id');
    $stmt->execute([':name' => $name, ':course_id' => $course_id, ':id' => $id]);
    return $stmt->rowCount() > 0; // Return true if a section exists
}

//Update sction
public function getSectionById($id) {
    $stmt = $this->pdo->prepare("SELECT * FROM sections WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC); // This will return false if no rows are found
}





        
        // read_sections.php
        public function getCourseName($courseId) {
            $query = "SELECT course_name FROM courses WHERE id = :course_id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':course_id', $courseId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['course_name'] ?? 'Unknown';
        }

        // read_sections.php
        public function getAllSections() {
            try {
                $stmt = $this->pdo->query('SELECT * FROM sections');
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
                return [];
            }
        }


 // delete_section.php
public function delete($id) {
    try {
        // Check if the section has associated subjects
        if ($this->sectionHasSubjects($id)) {
            // Redirect with a message that the section cannot be deleted
            header('Location: read_sections.php?id=' . $id . '&message=exists');
            exit();
        }

        // Proceed to delete the section if no subjects are associated
        $stmt = $this->pdo->prepare('DELETE FROM sections WHERE id = :id');
        $stmt->execute([':id' => $id]);

        // Redirect with a success message if deletion is successful
        header('Location: read_sections.php?id=' . $id . '&message=deleted');
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

 // delete_section.php
// Method to check if a section has associated subjects
private function sectionHasSubjects($id) {
    try {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM subjects WHERE section_id = :id');
        $stmt->execute([':id' => $id]);
        $count = $stmt->fetchColumn();
        return $count > 0; // Returns true if there are associated subjects
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}


}
?>