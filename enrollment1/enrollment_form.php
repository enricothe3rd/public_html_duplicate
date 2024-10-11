<?php
// Include the Database class
require_once '../db/db_connection3.php';

// Get the PDO instance from your Database class
$pdo = Database::connect();

// Fetch all courses
$coursesQuery = $pdo->query('SELECT id, course_name FROM courses');
$courses = $coursesQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch all semesters
$semestersQuery = $pdo->query('SELECT id, semester_name FROM semesters');
$semesters = $semestersQuery->fetchAll(PDO::FETCH_ASSOC);

$selectedCourse = isset($_POST['course_id']) ? (int)$_POST['course_id'] : null;
$selectedSemester = isset($_POST['semester_id']) ? (int)$_POST['semester_id'] : null;

// Fetch sections, subjects, and schedules based on selected course and semester
$sections = [];
if ($selectedCourse && $selectedSemester) {
    $sectionsQuery = $pdo->prepare(
        'SELECT s.id AS section_id, s.name AS section_name, subj.code AS subject_code, subj.title AS subject_title, 
                sch.day_of_week, sch.start_time, sch.end_time, sch.room
         FROM sections s
         INNER JOIN subjects subj ON s.id = subj.section_id
         INNER JOIN schedules sch ON subj.id = sch.subject_id
         WHERE s.course_id = :course_id AND subj.semester_id = :semester_id
         ORDER BY s.name, subj.title'
    );
    $sectionsQuery->execute(['course_id' => $selectedCourse, 'semester_id' => $selectedSemester]);
    $sections = $sectionsQuery->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course and Semester Selection</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

<div class="container mx-auto mt-10 p-6 bg-white rounded-lg shadow-lg">
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">Select Course and Semester</h1>

    <form method="POST" action="" class="mb-6">
        <!-- Course Selection -->
        <div class="mb-4">
            <label for="course_id" class="block text-gray-700 font-medium mb-2">Select Course:</label>
            <select name="course_id" id="course_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring focus:ring-blue-500 focus:border-blue-500" required>
                <option value="">--Select Course--</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= htmlspecialchars($course['id']) ?>" <?= ($selectedCourse == $course['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($course['course_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Semester Selection -->
        <div class="mb-4">
            <label for="semester_id" class="block text-gray-700 font-medium mb-2">Select Semester:</label>
            <select name="semester_id" id="semester_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring focus:ring-blue-500 focus:border-blue-500" required>
                <option value="">--Select Semester--</option>
                <?php foreach ($semesters as $semester): ?>
                    <option value="<?= htmlspecialchars($semester['id']) ?>" <?= ($selectedSemester == $semester['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($semester['semester_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring focus:ring-blue-500">Submit</button>
    </form>

    <?php if ($selectedCourse && $selectedSemester && $sections): ?>
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Sections, Subjects, and Schedules</h2>
        <?php
        $currentSection = null;
        foreach ($sections as $section): 
            if ($currentSection !== $section['section_id']):
                if ($currentSection !== null): ?>
                    </tbody></table>
                <?php endif;
                $currentSection = $section['section_id']; ?>
                <h3 class="text-lg font-semibold text-gray-700 mt-6 mb-2">Section: <?= htmlspecialchars($section['section_name']) ?></h3>
                <table class="w-full border-collapse bg-white shadow-md rounded-lg mb-6">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-4 py-2 border-b text-left text-gray-600">Subject Code</th>
                            <th class="px-4 py-2 border-b text-left text-gray-600">Subject Title</th>
                            <th class="px-4 py-2 border-b text-left text-gray-600">Day</th>
                            <th class="px-4 py-2 border-b text-left text-gray-600">Start Time</th>
                            <th class="px-4 py-2 border-b text-left text-gray-600">End Time</th>
                            <th class="px-4 py-2 border-b text-left text-gray-600">Room</th>
                        </tr>
                    </thead>
                    <tbody>
            <?php endif; ?>
            <tr class="border-b hover:bg-gray-100">
                <td class="px-4 py-2"><?= htmlspecialchars($section['subject_code']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($section['subject_title']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($section['day_of_week']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($section['start_time']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($section['end_time']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($section['room']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table>
    <?php elseif ($selectedCourse && $selectedSemester): ?>
        <p class="mt-4 text-gray-600">No sections or schedules found for the selected course and semester.</p>
    <?php endif; ?>
</div>

</body>
</html>
