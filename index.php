<?php
session_start();
require './db/db_connection.php';
require 'session_timeout.php'; 

$msg = ''; 
$email = ''; 
$password = ''; 

// Set the timezone to Philippines (Asia/Manila)
date_default_timezone_set('Asia/Manila'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email)) {
        $msg = "Please enter your email.";
    } elseif (empty($password)) {
        $msg = "Please enter your password.";
    } else {
        $sql = "SELECT id, email, password, role, email_confirmed, failed_attempts, account_locked, lock_time FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Check if account is locked
                if ($user['account_locked'] == 1) {
                    $msg = "Your account is locked. Please contact the admin.";
                } elseif ($user['email_confirmed'] == 0) {
                    $msg = "Please confirm your email first.";
                } else {
                    if (password_verify($password, $user['password'])) {

                        // Query to select the student_number based on the session email
                        $stmt = $conn->prepare("SELECT student_number FROM enrollments WHERE email = :email LIMIT 1");
                        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                        $stmt->execute();
                        // Fetch the result
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);

                          // Store the student_number in a session variable
                         $_SESSION['student_number'] = $row['student_number'];
                        // Password is correct
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['role'];

                        // Reset failed attempts on successful login
                        $sql = "UPDATE users SET failed_attempts = 0 WHERE email = :email";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':email', $email);
                        $stmt->execute();

                        // Redirect based on role
                        switch ($user['role']) {
                            case 'admin':
                                header("Location: admin_dashboard.php");
                                break;
                            case 'college_department':
                                header("Location: instructor_dashboard.php");
                                break;
                            case 'student':
                                header("Location: student_dashboard.php");
                                break;
                            case 'registrar':
                                header("Location: registrar_dashboard.php");
                                break;
                            case 'cashier':
                                header("Location: cashier_dashboard.php");
                                break;
                            default:
                                header("Location: default_dashboard.php");
                                break;
                        }
                        exit();
                    } else {
                        // Increment failed attempts
                        $failedAttempts = $user['failed_attempts'] + 1;
                        $remainingAttempts = 3 - $failedAttempts;

                        if ($failedAttempts >= 3) {
                            // Lock account after 3 failed attempts
                            $sql = "UPDATE users SET failed_attempts = :failed_attempts, account_locked = 1, lock_time = :lock_time WHERE email = :email";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':failed_attempts', $failedAttempts);

                            $lock_time = (new DateTime())->format('Y-m-d h:i:s A'); // 12-hour format with AM/PM
                            $stmt->bindParam(':lock_time', $lock_time);
                            $stmt->bindParam(':email', $email);
                            $stmt->execute();

                            $msg = "Your account is locked. Please contact the admin.";
                        } else {
                            // Update failed attempts
                            $sql = "UPDATE users SET failed_attempts = :failed_attempts WHERE email = :email";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':failed_attempts', $failedAttempts);
                            $stmt->bindParam(':email', $email);
                            $stmt->execute();

                            $msg = "Invalid email or password. You have $remainingAttempts attempt(s) left.";
                        }
                    }
                }
            } else {
                $msg = "Invalid email or password.";
            }
        } else {
            $msg = "Error preparing statement: " . $conn->errorInfo()[2];
        }
    }

    $_SESSION['msg'] = $msg;
    $_SESSION['form_values'] = ['email' => $email, 'password' => $password];
    header("Location: index.php");
    exit();
}

if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

$form_values = isset($_SESSION['form_values']) ? $_SESSION['form_values'] : ['email' => '', 'password' => ''];
unset($_SESSION['form_values']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="./assets/css/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* General message styling */
        .message {
            background-color: #FEE2E2; /* Default background for error messages */
            color: #B91C1C; /* Default text color for error messages */
            border: 1px solid #FECACA; /* Default border for error messages */
            border-radius: 0.5rem; /* Rounded corners */
            padding: 1rem; /* Padding around text */
            margin-top: 1rem; /* Margin at the top */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Soft shadow */
            opacity: 1;
            transition: opacity 0.5s ease-in-out; /* Fade-out effect */
        }

        /* Success message styling */
        .message-success {
            background-color: #D1FAE5; /* Light green background */
            color: #065F46; /* Dark green text */
            border: 1px solid #A7F3D0; /* Light green border */
        }


        .password-container {
            position: relative;
        }

        .password-container input {
            padding-right: 2.5rem;
        }

        .password-container .toggle-password {
            position: absolute;
            top: 50%;
            right: 0.75rem;
            transform: translateY(-50%);
            cursor: pointer;
        }

        /* Spinner styling */
        .spinner {
            position: absolute; /* Position relative to parent container */
            top: 50%;
            left: 43%; /* Default for small devices */
            transform: translate(-45%, -50%);
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left-color: red;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            z-index: 1000;
            display: none; /* Hidden by default */
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (min-width: 764px) { /* For large screens */
            .spinner {
                left: 49%;
                transform: translate(-50%, -50%);
            }
        }

        @media (min-width: 1024px) { /* For large screens */
            .spinner {
                left: 48%;
                transform: translate(-50%, -50%);
            }
        }
    </style>
</head>
<body class="h-full">

<div class="flex min-h-screen flex-col justify-center max-w-md mx-auto md:max-w-1xl p-4">

    <form id="loginForm" class="space-y-6" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <?php if (!empty($msg)): ?>
            <p id="statusMessage" class="message">
                <?php echo htmlspecialchars($msg); ?>
            </p>
        <?php endif; ?>

    
        <h4 class="font-body text-[1.8rem] text-custom-red font-semibold">Login</h4>
        <h5 class="font-body text-[1.2rem] text-custom-red font-medium tracking-wider">
            Don't have an account? 
            <a href="./views/login/register.php" class="inline-block underline text-custom-darkRed font-bold" id="signUpLink">Sign Up</a>
        </h5>

        <div>
            <label for="email" class="block font-body text-[1.2rem] font-semibold leading-6 text-custom-black">Email Address</label>
            <div class="mt-2">
                <input id="email" name="email" type="email" autocomplete="email" placeholder="you@example.com" style="outline:none;"
                class="block w-full rounded-md border-0 p-[1rem] text-gray-900 shadow-md ring-2 ring-inset ring-gray-300 placeholder:text-gray-300" 
                value="<?php echo htmlspecialchars($form_values['email']); ?>">          
                <div id="emailError" class="text-red-600 text-sm mt-1 hidden"></div>
            </div>
        </div>

        <div class="password-container">
            <div class="flex justify-between items-center">
                <label for="password" class="font-body text-[1.2rem] font-semibold leading-6 text-custom-black">Password</label>
                <a href="views/login/forgot_password.php" class="underline text-custom-darkRed font-bold" id="forgotPasswordLink">Forgot Password?</a>
            </div>
            <div class="mt-2 relative">
                <input id="password" name="password" type="password" autocomplete="current-password" placeholder="Enter your password" style="outline:none;"
                class="block w-full rounded-md border-0 p-[1rem] text-gray-900 shadow-md ring-2 ring-inset ring-gray-300 placeholder:text-gray-300" 
                value="<?php echo htmlspecialchars($form_values['password']); ?>">
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                <div id="passwordError" class="text-red-600 text-sm mt-1 hidden"></div>
            </div>
        </div>

        <button type="submit" class="flex w-full justify-center rounded-md bg-custom-red px-3 py-5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-red-600">
            <span>Login</span>
        </button>

    </form>

    <!-- Spinner element -->
    <div id="spinner" class="spinner"></div>

</div>









<script>
document.addEventListener('DOMContentLoaded', (event) => {
    const messageElement = document.getElementById('statusMessage');
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const form = document.getElementById('loginForm');
    const spinner = document.getElementById('spinner');
    const forgotPasswordLink = document.getElementById('forgotPasswordLink');
    const signUpLink = document.getElementById('signUpLink');

    // Fade out the status message after 5 seconds
    if (messageElement) {
        setTimeout(() => {
            messageElement.style.opacity = '0';
            setTimeout(() => {
                messageElement.style.display = 'none';
            }, 500); // Match duration of transition
        }, 5000); // 5000 milliseconds = 5 seconds
    }

    // Toggle password visibility
    togglePassword.addEventListener('click', () => {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        togglePassword.classList.toggle('fa-eye');
        togglePassword.classList.toggle('fa-eye-slash');
    });

    // Handle form submission
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        spinner.style.display = 'block'; // Show spinner

        // Submit form after delay
        setTimeout(() => {
            form.submit();
        }, 2000); // 2000 milliseconds = 2 seconds
    });

    // Handle forgot password link click
    forgotPasswordLink.addEventListener('click', (event) => {
        event.preventDefault(); // Prevent default link action
        spinner.style.display = 'block'; // Show spinner

        // Redirect after delay (if needed)
        setTimeout(() => {
            window.location.href = forgotPasswordLink.href;
        }, 2000); // 2000 milliseconds = 2 seconds
    });

    // Handle forgot sign up link click
    signUpLink.addEventListener('click', (event) => {
        event.preventDefault(); // Prevent default link action
        spinner.style.display = 'block'; // Show spinner

        // Redirect after delay (if needed)
        setTimeout(() => {
            window.location.href = signUpLink.href;
        }, 2000); // 2000 milliseconds = 2 seconds
    });
});
</script>
</body>
</html>


</body>
</html>
