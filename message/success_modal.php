<?php
// Start the session at the top if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const successMessage = "<?php echo isset($_SESSION['success_message']) ? htmlspecialchars($_SESSION['success_message']) : ''; ?>";
        const showModal = "<?php echo isset($_SESSION['show_modal']) ? 'true' : 'false'; ?>";

        if (showModal === 'true') {
            // Show the modal if the flag is set
            const modal = document.getElementById('successModal');
            modal.classList.remove('hidden');
            modal.classList.add('animate__animated', 'animate__fadeIn'); // Add animation classes

            // Set a timeout to redirect after displaying the modal
            setTimeout(function() {
                closeModal(); // Call closeModal function to handle redirection
            }, 3000); // Adjust the timeout duration as needed (3000ms = 3 seconds)

            // Clear the session variables after showing the modal
            <?php unset($_SESSION['success_message']); ?>
            <?php unset($_SESSION['show_modal']); ?>
        }
    });

    function closeModal() {
        const modal = document.getElementById('successModal');
        modal.classList.remove('animate__fadeIn'); // Remove fadeIn animation class
        modal.classList.add('animate__fadeOut'); // Add fadeOut animation class

        // Use a timeout to ensure the modal fades out before redirecting
        setTimeout(function() {
            modal.classList.add('hidden'); // Hide the modal
            window.location.href = 'read_departments.php'; // Redirect immediately on close
        }, 500); // Adjust the duration to match the animation (500ms)
    }
</script>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm mx-4">
        <img src="../assets/images/modal-icons/checked.png" alt="Success Image" class="w-16 h-16 mx-auto mb-4 rounded-full border-2 border-green-500">
        <p class="text-green-600 font-semibold text-center text-2xl">
            Creation Success!
        </p>
    </div>
</div>
