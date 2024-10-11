<?php
session_start();
session_regenerate_id();

require '../../db/db_connection.php'; // Database connection

// Include PHPMailer files
require '../../phpmailer/src/PHPMailer.php';
require '../../phpmailer/src/SMTP.php';
require '../../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$msg = ""; // Initialize message variable
$msgType = ''; // Initialize message type variable

// Initialize variables for form inputs
$email = "";
$password = "";
$role = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = isset($_POST['role']) ? $_POST['role'] : null;

    // Define allowed email domains
    $allowed_domains = ['gmail.com', 'outlook.com', 'mail.com', 'hotmail.com', 'yahoo.com'];

    // Extract the domain from the email
    $email_domain = substr(strrchr($email, "@"), 1);

    if (empty($email)) {
        $msg = "Please enter your email address.";
        $msgType = 'error';
    } elseif (!in_array($email_domain, $allowed_domains)) {
        $msg = "Please enter a valid email address with one of the following domains: @gmail.com, @outlook.com, @mail.com, @hotmail.com, @yahoo.com.";
        $msgType = 'error';
    } elseif (empty($password)) {
        $msg = "Please enter your password.";
        $msgType = 'error';
    } elseif (empty($role)) {
        $msg = "Please select a role.";
        $msgType = 'error';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[\W_]/', $password) || strlen($password) < 8) {
        $msg = "Password must be at least 8 characters long and include letters, numbers, and special characters.";
        $msgType = 'error';
    } else {
        $passwordHashed = password_hash($password, PASSWORD_BCRYPT);

        // Check if email already exists
        $sql_check_email = "SELECT COUNT(*) FROM users WHERE email = :email";
        $stmt_check_email = $conn->prepare($sql_check_email);
        $stmt_check_email->bindParam(':email', $email);
        $stmt_check_email->execute();

        if ($stmt_check_email->fetchColumn() > 0) {
            $msg = "This email is already registered.";
            $msgType = 'error';
        } else {
            // Generate a random registration token
            $registration_token = bin2hex(random_bytes(20));

            // Insert new user record
            $sql_insert_user = "INSERT INTO users (email, password, role) VALUES (:email, :password, :role)";
            $stmt_insert_user = $conn->prepare($sql_insert_user);

            if ($stmt_insert_user) {
                $stmt_insert_user->bindParam(':email', $email);
                $stmt_insert_user->bindParam(':password', $passwordHashed);
                $stmt_insert_user->bindParam(':role', $role);

                if ($stmt_insert_user->execute()) {
                    $user_id = $conn->lastInsertId();

                    // Insert registration token into user_registration table
                    $sql_insert_token = "INSERT INTO user_registration (user_id, token, type) VALUES (:user_id, :token, 'registration')";
                    $stmt_insert_token = $conn->prepare($sql_insert_token);

                    if ($stmt_insert_token) {
                        $stmt_insert_token->bindParam(':user_id', $user_id);
                        $stmt_insert_token->bindParam(':token', $registration_token);

                        if ($stmt_insert_token->execute()) {
                            // Send registration email
                            $mail = new PHPMailer();
                            $mail->isSMTP();
                            $mail->Host = 'smtp.gmail.com';
                            $mail->SMTPAuth = true;
                            $mail->Username = 'pedrajetajr22@gmail.com'; // SMTP username
                            $mail->Password = 'fstvwntidussfhvc'; // SMTP password
                            $mail->SMTPSecure = 'tls';
                            $mail->Port = 587;

                            $mail->setFrom('bcc@gmail.com', 'Binangonan Catholic College Email Confirmation');
                            $mail->addAddress($email);

                            $mail->isHTML(true);
                            $mail->Subject = 'Welcome! Confirm Your Email';
                            $mail->Body = '
                                <html>
                                <head>
                                    <style>
                                        /* Your email template styling here */
                                    </style>
                                </head>
                                <body>
                                    <div class="email-container">
                                        <div class="header">
                                            <img src="https://bccenrollment.xyz/bcc-banner.png" alt="Header Image">
                                        </div>
                                        <div class="content">
                                            <h1>Welcome to our platform!</h1>
                                            <p>Click the following link to confirm your email and set your password:</p>
                                            <a href="http://localhost/Enrollment-System/views/login/confirm_email1.php?token=' . $registration_token . '" class="btn">Confirm Email</a>
                                        </div>
                                        <div class="footer">
                                           <p>Binangonan Catholic College<br>123 Street Address<br>City, State, ZIP<br>© ' . date('Y') . ' BCC. All rights reserved.</p>
                                        </div>
                                    </div>
                                </body>
                                </html>';

                            if (!$mail->send()) {
                                $msg = "Error sending email: " . $mail->ErrorInfo;
                                $msgType = 'error';
                            } else {
                                $_SESSION['emailSent'] = true;
                                header("Location: " . $_SERVER['PHP_SELF']);
                                exit();
                            }
                        } else {
                            $msg = "Error inserting registration token: " . $stmt_insert_token->errorInfo()[2];
                            $msgType = 'error';
                        }
                        $stmt_insert_token->closeCursor();
                    } else {
                        $msg = "Error preparing registration token statement: " . $conn->errorInfo()[2];
                        $msgType = 'error';
                    }
                } else {
                    $msg = "Error registering user: " . $stmt_insert_user->errorInfo()[2];
                    $msgType = 'error';
                }
                $stmt_insert_user->closeCursor();
            } else {
                $msg = "Error preparing user registration statement: " . $conn->errorInfo()[2];
                $msgType = 'error';
            }
        }
        $stmt_check_email->closeCursor();
    }
}

// Retrieve session message
$emailSent = isset($_SESSION['emailSent']) ? $_SESSION['emailSent'] : false;
unset($_SESSION['emailSent']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="../../assets/css/output.css" rel="stylesheet">
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
    <form id="registerForm" class="space-y-6" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
               <?php if ($emailSent): ?>
            <p id="statusMessage" class="message message-success">
                A confirmation email has been sent to your email address.
            </p>
        <?php elseif (!empty($msg)): ?>
            <p id="statusMessage" class="message <?php echo ($msgType === 'success') ? 'message-success' : ''; ?>">
                <?php echo htmlspecialchars($msg); ?>
            </p>
        <?php endif; ?>
        <h4 class="font-body text-[1.8rem] text-custom-red font-semibold">Register</h4>
        <h5 class="font-body text-[1.2rem] text-custom-red font-medium tracking-wider">
            Already have an account yet? 
            <a href="../../index.php" class="inline-block underline text-custom-darkRed font-bold" id="signInLink">Sign In</a>
        </h5>

        <div>
            <label for="email" class="block font-body text-[1.2rem] font-semibold leading-6 text-custom-black">Email Address</label>
            <div class="mt-2">
                <input id="email" name="email" type="email" autocomplete="email" placeholder="you@example.com" style="outline:none;"
                class="block w-full rounded-md border-0 p-[1rem] text-gray-900 shadow-md ring-2 ring-inset ring-gray-300 placeholder:text-gray-300" value="<?php echo htmlspecialchars($email); ?>">          
                <div id="emailError" class="text-red-600 text-sm mt-1 hidden"></div>
            </div>
        </div>

        <div class="password-container">
            <div class="flex justify-between items-center">
                <label for="password" class="font-body text-[1.2rem] font-semibold leading-6 text-custom-black">Password</label>
                <a href="forgot_password.php" class="underline text-custom-darkRed font-bold" id="forgotPasswordLink">Forgot Password?</a>
            </div>
            <div class="mt-2 relative">
                <input id="password" name="password" type="password" autocomplete="current-password" placeholder="Enter 8 characters or more" style="outline:none;"
                class="block w-full rounded-md border-0 p-[1rem] text-gray-900 shadow-md ring-2 ring-inset ring-gray-300 placeholder:text-gray-300" value="<?php echo htmlspecialchars($password); ?>">
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                <div id="passwordError" class="text-red-600 text-sm mt-1 hidden"></div>
            </div>
        </div>

        <div class="space-y-2">
            <span class="text-lg font-medium text-custom-black">Role:</span><br>
            <div class="flex items-center">
                <input type="radio" id="student" name="role" value="student" class="mr-2" <?php echo ($role == 'student') ? 'checked' : ''; ?>>
                <label for="student">Student</label>
            </div>
            <div class="flex items-center">
                <input type="radio" id="cashier" name="role" value="cashier" class="mr-2" <?php echo ($role == 'cashier') ? 'checked' : ''; ?>>
                <label for="cashier">Cashier</label>
            </div>
            <div class="flex items-center">
                <input type="radio" id="college_department" name="role" value="college_department" class="mr-2" <?php echo ($role == 'college_department') ? 'checked' : ''; ?>>
                <label for="college_department">College Department</label>
            </div>
            <div class="flex items-center">
                <input type="radio" id="registrar" name="role" value="registrar" class="mr-2" <?php echo ($role == 'registrar') ? 'checked' : ''; ?>>
                <label for="registrar">Registrar</label>
            </div>
            <div class="flex items-center">
                <input type="radio" id="admin" name="role" value="admin" class="mr-2" <?php echo ($role == 'admin') ? 'checked' : ''; ?>>
                <label for="admin">Admin</label>
            </div>
        </div>

        <button type="submit" class="flex w-full justify-center rounded-md bg-custom-red px-3 py-5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-red-600">
            <span>Sign Up</span>
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
    const registerForm = document.getElementById('registerForm');
    const spinner = document.getElementById('spinner');
    const forgotPasswordLink = document.getElementById('forgotPasswordLink');
    const signInLink = document.getElementById('signInLink');

    if (messageElement) {
        // Set the timeout to fade out the message after 5 seconds
        setTimeout(() => {
            messageElement.style.opacity = '0';
            setTimeout(() => {
                messageElement.style.display = 'none';
            }, 500); // Match duration of transition
        }, 5000); // 5000 milliseconds = 5 seconds
    }

  
        togglePassword.addEventListener('click', () => {
            // Toggle password visibility
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            // Toggle icon
            togglePassword.classList.toggle('fa-eye');
            togglePassword.classList.toggle('fa-eye-slash');
        });


        // Handle form submission
        registerForm.addEventListener('submit', (event) => {
            event.preventDefault();
            spinner.style.display = 'block'; // Show spinner

            // Submit form after delay
            setTimeout(() => {
                registerForm.submit();
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
    

        // Handle forgot sign in  link click
        signInLink.addEventListener('click', (event) => {
        event.preventDefault(); // Prevent default link action
        spinner.style.display = 'block'; // Show spinner

        // Redirect after delay (if needed)
        setTimeout(() => {
            window.location.href = signInLink.href;
        }, 2000); // 2000 milliseconds = 2 seconds
    });
});

</script>

</body>
</html>