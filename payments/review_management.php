<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-8">
    <div class="text-center text-2xl">
        <h3>Review Your Enrollment</h3>
    </div>

    <div class="w-[50vw] mx-auto relative mt-8 bg-white shadow-md rounded-lg p-5" style="height:75vh;">
        <div class="flex justify-end mb-2 mr-4">
            <a href="enrollments/create_enrollment.php" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-700 transform translate-x-2">
                Edit
            </a>
        </div>
        <iframe src="review_enrollment.php" title="Enrollment Review" class="w-full h-full border-none" style="background-color: transparent;"></iframe>
    </div>

    <div class="w-[50vw] mx-auto relative mt-8 bg-white shadow-md rounded-lg p-5" style="height:60vh;">
        <div class="flex justify-end mb-2 mr-4">
            <a href="select_courses.php" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-700 transform translate-x-2">
                Edit
            </a>
        </div>
        <iframe src="review_subject_selected.php" title="Subject Review" class="w-full h-full border-none" style="background-color: transparent;"></iframe>
      
        <div class="flex justify-end pb-10">
            <a href="payment_form.php" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-700">
                Proceed to Payment
            </a>
        </div>

    </div>
</body>
</html>
