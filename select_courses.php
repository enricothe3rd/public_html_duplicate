<?php
// Include the database connection file
include 'db/db_connection1.php'; // Make sure this path is correct

function select_course($selected_course_id = null, $department_id = null) {
    global $pdo; // Access the global $pdo variable

    // Check if $pdo is a valid PDO object
    if (!($pdo instanceof PDO)) {
        die('Database connection is not valid.');
    }

    // Initialize the select tag with Tailwind classes
    $html = '<div class="relative inline-block w-full">';
    $html .= '<select name="course_id" id="course_id" class="block w-full bg-white border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">';
    
    // Prepare the SQL query
    $query = "SELECT id, course_name FROM courses";
    if ($department_id) {
        $query .= " WHERE department_id = ?";
    }
    $query .= " ORDER BY course_name ASC";
    
    // Prepare and execute the statement
    $stmt = $pdo->prepare($query);
    if ($department_id) {
        $stmt->bindParam(1, $department_id, PDO::PARAM_INT);
    }
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    // Fetch results and populate the dropdown
    foreach ($results as $row) {
        $selected = ($row['id'] == $selected_course_id) ? 'selected' : '';
        $html .= '<option value="' . htmlspecialchars($row['id']) . '" ' . $selected . '>' . htmlspecialchars($row['course_name']) . '</option>';
    }
    
    // Close the select tag
    $html .= '</select>';
    
    // Close the container div
    $html .= '</div>';
    
    // Return the generated HTML
    return $html;
}

function select_subjects($course_id = null) {
    global $pdo; // Access the global $pdo variable

    // Check if $pdo is a valid PDO object
    if (!($pdo instanceof PDO)) {
        die('Database connection is not valid.');
    }

    // Initialize the container for subjects
    $html = '<div id="subjects-container">';
    
    // If a course is selected, fetch the subjects
    if ($course_id) {
        $query = "SELECT id, title FROM subjects WHERE course_id = ? ORDER BY title ASC";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(1, $course_id, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        // Fetch results and populate the subjects
        foreach ($results as $row) {
            $html .= '<p>' . htmlspecialchars($row['title']) . '</p>';
        }
    }
    
    // Close the container div
    $html .= '</div>';
    
    // Return the generated HTML
    return $html;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Selection</title>
    <!-- Include Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
    <div class="p-6 max-w-lg mx-auto">
        <h2 class="text-2xl font-bold mb-4">Select a Course</h2>
        <?php echo select_course(); ?>
        
        <h2 class="text-xl font-bold mt-6">Subjects</h2>
        <div id="subjects-container">
            <!-- Subjects will be displayed here -->
        </div>
    </div>
    
    <script>
        document.getElementById('course_id').addEventListener('change', function() {
            var courseId = this.value;
            axios.get('fetch_subjects.php', {
                params: {
                    course_id: courseId
                }
            })
            .then(function(response) {
                document.getElementById('subjects-container').innerHTML = response.data;
            })
            .catch(function(error) {
                console.error('Error fetching subjects:', error);
            });
        });
    </script>
</body>
</html>
