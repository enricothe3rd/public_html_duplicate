<?php
require 'db_connection1.php';
session_start();

// Check if the user is logged in and has a valid session
if (!isset($_SESSION['user_id'])) {
    die('You must be logged in to enroll in subjects.');
}

// Initialize variables
$selected_course_id = null;
$sections = [];
$subjects = [];
$selected_subjects = [];

// Handle course selection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['select_course'])) {
    $selected_course_id = $_POST['course_id'] ?? null;
    
    if ($selected_course_id) {
        try {
            // Fetch the selected course details
            $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
            $stmt->execute([$selected_course_id]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($course) {
                echo '<p class="text-blue-500">Selected Course: ' . htmlspecialchars($course['course_name']) . '</p>';

                // Fetch sections and subjects related to the selected course
                $stmt = $pdo->prepare("
                    SELECT s.id AS subject_id, s.subject_title, s.code, s.units, s.room, s.day, s.start_time, s.end_time,
                           sec.id AS section_id, sec.section_name,
                           cl.id AS class_id, cl.name AS class_name, cl.course_id
                    FROM subjects s
                    JOIN sections sec ON s.section_id = sec.id
                    JOIN classes cl ON sec.class_id = cl.id
                    WHERE cl.course_id = ?
                ");
                $stmt->execute([$selected_course_id]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Organize sections and subjects
                foreach ($results as $row) {
                    $section_id = $row['section_id'];
                    $subject_id = $row['subject_id'];

                    if (!isset($sections[$section_id])) {
                        $sections[$section_id] = [
                            'section_name' => $row['section_name'],
                            'subjects' => []
                        ];
                    }

                    $sections[$section_id]['subjects'][] = [
                        'id' => $subject_id,
                        'subject_title' => $row['subject_title'],
                        'code' => $row['code'],
                        'units' => $row['units'],
                        'room' => $row['room'],
                        'day' => $row['day'],
                        'start_time' => $row['start_time'],
                        'end_time' => $row['end_time']
                    ];
                }
            } else {
                echo '<p class="text-red-500">Course not found.</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="text-red-500">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }
}

// Handle subject selection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enroll'])) {
    $selected_subjects = $_POST['subjects'] ?? [];
    
    if (!empty($selected_subjects)) {
        try {
            $user_id = $_SESSION['user_id'];
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO subject_enrollments (student_id, subject_id) VALUES (?, ?)");
            foreach ($selected_subjects as $subject_id) {
                $stmt->execute([$user_id, $subject_id]);
            }
            
            $pdo->commit();
            echo '<p class="text-green-500">You have successfully enrolled in the selected subjects.</p>';
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo '<p class="text-red-500">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    } else {
        echo '<p class="text-red-500">No subjects selected.</p>';
    }
}

// Fetch all courses for selection
$stmt = $pdo->query("SELECT * FROM courses");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Course</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900">
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Select a Course</h1>

        <!-- Course Selection Form -->
        <form method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="course_id">Choose Course</label>
                <select name="course_id" id="course_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Select a course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?php echo htmlspecialchars($course['id']); ?>" <?php echo $selected_course_id == $course['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['course_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" name="select_course" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Select Course</button>
            </div>
        </form>

        <?php if ($selected_course_id && $sections): ?>
            <!-- Display Sections and Subjects -->
            <form method="POST" class="bg-white shadow-md rounded p-4 mt-4">
                <h2 class="text-xl font-semibold mb-4">Sections and Subjects for Selected Course</h2>
                <?php foreach ($sections as $section_id => $section): ?>
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold">Section: <?php echo htmlspecialchars($section['section_name']); ?></h3>
                        <table class="min-w-full bg-white mt-2">
                            <thead>
                                <tr>
                                    <th class="py-2">Select</th>
                                    <th class="py-2">Subject Title</th>
                                    <th class="py-2">Code</th>
                                    <th class="py-2">Units</th>
                                    <th class="py-2">Room</th>
                                    <th class="py-2">Day</th>
                                    <th class="py-2">Start Time</th>
                                    <th class="py-2">End Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($section['subjects'] as $subject): ?>
                                <tr>
                                    <td class="border px-4 py-2">
                                        <input type="checkbox" name="subjects[]" value="<?php echo htmlspecialchars($subject['id']); ?>">
                                    </td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($subject['subject_title']); ?></td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($subject['code']); ?></td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($subject['units']); ?></td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($subject['room']); ?></td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($subject['day']); ?></td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($subject['start_time']); ?></td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($subject['end_time']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
                <div class="flex items-center justify-between">
                    <button type="submit" name="enroll" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Enroll</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
