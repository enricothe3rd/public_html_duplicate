<?php
require '../../db/db_connection3.php';

class Classroom {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

// Handle Create Request
public function handleCreateClassroomRequest() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $room_number = trim($_POST['room_number']);
        $capacity = trim($_POST['capacity']);
        $building = trim($_POST['building']);

        // Validate inputs to ensure they are not empty
        if (empty($room_number) || empty($capacity) || empty($building)) {
            header('Location: create_classroom.php?message=empty_fields');
            exit();
        }

            // Check if building contains at least one letter
    if (!preg_match('/[a-zA-Z]/', $building)) {
        header('Location: create_classroom.php?message=invalid_building');
        exit();
    }
        // Store inputs in session for retrieval after redirect
        $_SESSION['last_room_number'] = $room_number;
        $_SESSION['last_capacity'] = $capacity;
        $_SESSION['last_building'] = $building;

        // Attempt to create the classroom
        if ($this->createClassroom($room_number, $capacity, $building)) {
            // Redirect with a success message
            header('Location: create_classroom.php?message=success');
            exit();
        } else {
            // Redirect with a failure message
            header('Location: create_classroom.php?message=failure');
            exit();
        }
    }
}

// Method to create a new classroom
public function createClassroom($room_number, $capacity, $building) {
    try {
        $sql = "INSERT INTO classrooms (room_number, capacity, building) VALUES (:room_number, :capacity, :building)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':room_number' => $room_number,
            ':capacity' => $capacity,
            ':building' => $building
        ]);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}



// Handle Update Request
public function handleUpdateClassroomRequest($id) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'];  // Retrieve the section ID from POST data
        $room_number = $_POST['room_number'];
        $capacity = $_POST['capacity'];
        $building = $_POST['building'];

        // Validation
        if (empty($room_number)) {
            header('Location: edit_classroom.php?id=' . $id . '&message=invalid_room_number');
            exit();
        }

        if (empty($capacity) || !is_numeric($capacity) || $capacity <= 0) {
            header('Location: edit_classroom.php?id=' . $id . '&message=invalid_capacity');
            exit();
        }

        // Validate building: must be alphanumeric, spaces, or hyphens
        if (empty($building) || !preg_match('/^[A-Za-z0-9\s-]+$/', $building)) {
            header('Location: edit_classroom.php?id=' . $id . '&message=invalid_building');
            exit();
        }

        // If validation passes, proceed with the update
        if ($this->updateClassroom($id, $room_number, $capacity, $building)) {
            // Redirect to the list of classrooms with a success message and id
            header('Location: edit_classroom.php?id=' . $id . '&message=update_successful');
            exit();
        } else {
            echo 'Failed to update classroom.';
        }
    }
}


public function updateClassroom($id, $room_number, $capacity, $building) {
    try {
        $sql = "UPDATE classrooms SET room_number = :room_number, capacity = :capacity, building = :building WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':room_number' => $room_number,
            ':capacity' => $capacity,
            ':building' => $building,
            ':id' => $id
        ]);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}


 // Retrieve All Classrooms
public function getClassrooms() {
    $sql = "SELECT * FROM classrooms ORDER BY room_number ASC"; // Change classroom_name to your actual column name
    $stmt = $this->db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // Retrieve a Classroom by ID
    public function getClassroomById($id) {
        $sql = "SELECT * FROM classrooms WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }


// Delete a Classroom
public function deleteClassroom($id) {
    try {
        // Prepare the SQL statement
        $sql = "DELETE FROM classrooms WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        // Execute the statement
        if ($stmt->execute([':id' => $id])) {
            // Check if any rows were affected (i.e., a classroom was deleted)
            if ($stmt->rowCount() > 0) {
                // Redirect to the edit classroom page with a success message
                header('Location: read_classrooms.php?id=' . $id . '&message=delete_successful');
                exit();
            } else {
                // No rows affected, classroom might not exist
                header('Location: read_classrooms.php?id=' . $id . '&message=no_classroom_found');
                exit();
            }
        }
    } catch (PDOException $e) {
        // Redirect to the edit classroom page with an error message
        header('Location: read_classrooms.php?id=' . $id . '&message=delete_failed');
        exit();
    }
    
    // Fallback return in case of unexpected failure
    return false;  
}

}
?>
