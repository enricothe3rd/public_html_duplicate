<?php
session_start();
require '../db/db_connection3.php'; // Adjust the filename as needed





if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get POST parameters
    $courseId = $_POST['course_id'];
    $schoolYearId = $_POST['school_year_id'] ?? null; // Get school year ID
    $semesterId = $_POST['semester_id'] ?? null; // Get semester ID

    if (empty($courseId) || empty($schoolYearId) || empty($semesterId)) {
        echo json_encode(['error' => 'Required parameters are missing.']);
        exit;
    }

    try {
        $db = Database::connect();
        
        // // Get user email from session
        // $user_email = $_SESSION['user_email'] ?? '';
        // if (empty($user_email)) {
        //     echo json_encode(['error' => 'User email is not set in the session.']);
        //     exit;
        // }
        
        // Check if student_number is set in the session
        $student_number = $_SESSION['student_number'] ?? null; 
        if (is_null($student_number)) {
            echo json_encode(['error' => 'Student number is not set in the session.']);
            exit;
        }
        
        // Step 1: Fetch the year from the enrollments table using student_number
        $stmt = $db->prepare("SELECT year FROM enrollments WHERE student_number = :student_number");
        $stmt->bindParam(':student_number', $student_number, PDO::PARAM_STR);
        $stmt->execute();
        $yearData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($yearData) {
            $year = $yearData['year'];
        } else {
            echo json_encode(['error' => 'Year not found for the given student number.']);
            exit;
        }
        
        // Step 2: Fetch unique sections for the selected course and school year/semester,
        // but only those sections whose names start with the year
        $yearPrefix = $year . '%'; // Create a prefix for comparison
        $stmt = $db->prepare("
            SELECT sec.id, sec.name 
            FROM sections sec 
            JOIN subjects s ON s.section_id = sec.id 
            WHERE sec.course_id = :course_id
            AND s.school_year_id = :school_year_id 
            AND s.semester_id = :semester_id
            AND sec.name LIKE :year_prefix
            GROUP BY sec.id, sec.name
        ");
        $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
        $stmt->bindParam(':school_year_id', $schoolYearId, PDO::PARAM_INT);
        $stmt->bindParam(':semester_id', $semesterId, PDO::PARAM_INT);
        $stmt->bindParam(':year_prefix', $yearPrefix, PDO::PARAM_STR);
        $stmt->execute();

        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Step 3: Fetch subjects for the retrieved sections
        $subjectData = [];
        if (!empty($sections)) {
            $sectionIds = implode(',', array_column($sections, 'id')); // Create a list of section IDs
            $stmt = $db->prepare("
                SELECT s.id as subject_id, s.title, s.code, s.units, s.section_id
                FROM subjects s
                WHERE s.section_id IN ($sectionIds) 
                AND s.school_year_id = :school_year_id 
                AND s.semester_id = :semester_id
            ");
            $stmt->bindParam(':school_year_id', $schoolYearId, PDO::PARAM_INT);
            $stmt->bindParam(':semester_id', $semesterId, PDO::PARAM_INT);
            $stmt->execute();
            $subjectData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Step 4: Combine sections and their subjects for output
        foreach ($sections as &$section) {
            $section['subjects'] = array_filter($subjectData, function($subject) use ($section) {
                return $subject['section_id'] == $section['id'];
            });
        }

        // Return the combined data as JSON
        echo json_encode($sections);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error fetching sections: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
