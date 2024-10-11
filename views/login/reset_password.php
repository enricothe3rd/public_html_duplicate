<?php
require '../../db/db_connection.php';

$error_message = '';
$success_message = '';
$redirect = false;

function validate_password($password) {
    return preg_match('/[A-Za-z]/', $password) &&
           preg_match('/[0-9]/', $password) &&
           preg_match('/[\W_]/', $password) &&
           strlen($password) >= 8;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $token = $_GET['token'] ?? '';

    $sql_check_token = "SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW()";
    $stmt_check_token = $conn->prepare($sql_check_token);
    $stmt_check_token->bindParam(':token', $token);
    $stmt_check_token->execute();

    if ($stmt_check_token->rowCount() > 0) {
        $reset_data = $stmt_check_token->fetch(PDO::FETCH_ASSOC);
        $email = $reset_data['email'];
    } else {
        $error_message = "It seems your password reset token is expired or invalid. Please request a new reset link.";
        $redirect = true;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (!validate_password($password)) {
        $error_message = 'Password must be at least 8 characters long, and include a letter, a number, and a special character.';
    } elseif ($password !== $password_confirm) {
        $error_message = 'Passwords do not match.';
    } else {
        $new_password = password_hash($password, PASSWORD_BCRYPT);

        $sql_check_token = "SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW()";
        $stmt_check_token = $conn->prepare($sql_check_token);
        $stmt_check_token->bindParam(':token', $token);
        $stmt_check_token->execute();

        if ($stmt_check_token->rowCount() > 0) {
            $reset_data = $stmt_check_token->fetch(PDO::FETCH_ASSOC);
            $email = $reset_data['email'];

            $sql_update_password = "UPDATE users SET password = :password WHERE email = :email";
            $stmt_update_password = $conn->prepare($sql_update_password);
            $stmt_update_password->bindParam(':password', $new_password);
            $stmt_update_password->bindParam(':email', $email);

            if ($stmt_update_password->execute()) {
                $sql_delete_token = "DELETE FROM password_resets WHERE token = :token";
                $stmt_delete_token = $conn->prepare($sql_delete_token);
                $stmt_delete_token->bindParam(':token', $token);
                $stmt_delete_token->execute();

                $success_message = "Password reset successful!";
                $redirect = true;
            } else {
                $error_message = "Error updating password.";
            }
        } else {
            $error_message = "It seems your password reset token is expired or invalid. Please request a new reset link.";
            $redirect = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="../../assets/css/output.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .error-message {
            background-color: #FEE2E2;
            color: #B91C1C;
            border: 1px solid #FECACA;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            opacity: 1;
            transition: opacity 0.5s ease-in-out;
        }
        .success-message {
            background-color: #D1FAE5;
            color: #10B981;
            border: 1px solid #10B981;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            opacity: 1;
            transition: opacity 0.5s ease-in-out;
        }
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .hidden {
            display: none;
        }
        .password-container {
            position: relative;
        }
        .password-container .toggle-password {
            position: absolute;
            top: 50%;
            right: 1rem;
            transform: translateY(-50%);
            cursor: pointer;
        }
        .password-container input {
            padding-right: 2.5rem;
        }
    </style>
</head>
<body class="h-full">
<div class="flex min-h-screen flex-col justify-center max-w-md mx-auto md:max-w-1xl p-4">
    <?php if (!empty($error_message)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($redirect): ?>
        <script>
            setTimeout(function() {
                window.location.href = '../../index.php';
            }, 5000); // Redirect after 5 seconds
        </script>
    <?php endif; ?>

    <form id="resetPasswordForm" class="space-y-6" action="reset_password.php" method="post">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        
        <div class="password-container">
            <label for="password" class="block font-body text-[1.2rem] font-semibold leading-6 text-black">New Password</label>
            <div class="mt-2 relative">
                <input id="password" name="password" type="password" autocomplete="new-password" placeholder="Enter a new password" style="outline:none;"
                       class="block w-full rounded-md border-0 p-[1rem] text-gray-900 shadow-md ring-2 ring-inset ring-gray-300 placeholder:text-gray-300">
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            </div>
        </div>

        <div class="password-container">
            <label for="password_confirm" class="block font-body text-[1.2rem] font-semibold leading-6 text-custom-black">Confirm Password</label>
            <div class="mt-2 relative">
                <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" placeholder="Confirm your new password" style="outline:none;"
                       class="block w-full rounded-md border-0 p-[1rem] text-gray-900 shadow-md ring-2 ring-inset ring-gray-300 placeholder:text-gray-300">
                <i class="fas fa-eye toggle-password" id="togglePasswordConfirm"></i>
            </div>
        </div>

        <button type="submit" class="flex w-full justify-center rounded-md bg-red-700 px-3 py-5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-red-600">
            <span>Reset Password</span>
        </button>
    </form>
</div>

<div id="spinner" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="loader"></div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    function setupTogglePassword(toggleButtonId, inputId) {
        var toggleButton = document.getElementById(toggleButtonId);
        var input = document.getElementById(inputId);

        toggleButton.addEventListener('click', function() {
            var type = input.type === 'password' ? 'text' : 'password';
            input.type = type;
            // Toggle the icon
            toggleButton.classList.toggle('fa-eye');
            toggleButton.classList.toggle('fa-eye-slash');
        });
    }

    setupTogglePassword('togglePassword', 'password');
    setupTogglePassword('togglePasswordConfirm', 'password_confirm');

    var form = document.getElementById('resetPasswordForm');
    var spinner = document.getElementById('spinner');

    form.addEventListener('submit', function() {
        spinner.classList.remove('hidden'); // Show spinner on form submission
    });
});
</script>

</body>
</html>