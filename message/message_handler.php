<?php

// Initialize messages
$success_message = '';
$message1 = '';

// Check if there are any messages in the session
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear message after displaying
}

if (isset($_SESSION['error_message'])) {
    $message1 = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear message after displaying
}
?>

<!-- Success Message -->
<?php if (!empty($success_message)): ?>
    <div id="success-message" class="bg-green-500 text-white p-4 rounded mb-4 animate__animated animate__fadeIn">
        <?php echo htmlspecialchars($success_message); ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var successMessageDiv = document.getElementById('success-message');
                if (successMessageDiv) {
                    // Apply fadeOut animation before hiding
                    successMessageDiv.classList.add('animate__fadeOut');
                    // Remove the element after the animation completes (1 second)
                    setTimeout(function() {
                        successMessageDiv.style.display = 'none';
                    }, 1000);
                }
            }, 3000);
        });
    </script>
<?php endif; ?>

<!-- Error Message -->
<?php if (!empty($message1)): ?>
    <div id="message1" class="bg-red-100 mb-2 text-red-700 border border-red-400 rounded px-4 py-3 mt-4 animate__animated animate__fadeIn">
        <?= htmlspecialchars($message1) ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var messageDiv = document.getElementById('message1');
                if (messageDiv) {
                    // Apply fadeOut animation before hiding
                    messageDiv.classList.add('animate__fadeOut');
                    // Remove the element after the animation completes (1 second)
                    setTimeout(function() {
                        messageDiv.style.display = 'none';
                    }, 1000);
                }
            }, 3000);
        });
    </script>
<?php endif; ?>
