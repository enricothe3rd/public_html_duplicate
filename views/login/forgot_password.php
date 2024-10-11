<?php
require '../../db/db_connection.php';
require '../../phpmailer/src/PHPMailer.php';
require '../../phpmailer/src/SMTP.php';
require '../../phpmailer/src/Exception.php';

use phpmailer\phpmailer\PHPMailer;
use phpmailer\phpmailer\Exception;

$msg = "";
$msgType = 'error'; // Default message type

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if the email exists
    $sql_check_email = "SELECT * FROM users WHERE email = :email";
    $stmt_check_email = $conn->prepare($sql_check_email);
    $stmt_check_email->bindParam(':email', $email);
    $stmt_check_email->execute();

    if ($stmt_check_email->rowCount() > 0) {
        // Generate a unique password reset token
        $reset_token = bin2hex(random_bytes(20));
        
        // Create DateTime object with Philippine Time
        $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $created_at = $now->format('Y-m-d H:i:s'); // Format the current time
        
        // Create a new DateTime object for expiration
        $expires_at = (clone $now)->add(new DateInterval('PT1H')); // Clone and add 1 hour
        
        // Format the datetime for expiration
        $expires_at = $expires_at->format('Y-m-d H:i:s');

        // Insert token into password_resets table
        $sql_insert_token = "INSERT INTO password_resets (email, token, created_at, expires_at) VALUES (:email, :token, :created_at, :expires_at)";
        $stmt_insert_token = $conn->prepare($sql_insert_token);
        $stmt_insert_token->bindParam(':email', $email);
        $stmt_insert_token->bindParam(':token', $reset_token);
        $stmt_insert_token->bindParam(':created_at', $created_at);
        $stmt_insert_token->bindParam(':expires_at', $expires_at);

        if ($stmt_insert_token->execute()) {
            // Send reset email
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'pedrajetajr22@gmail.com'; // SMTP username
            $mail->Password = 'fstvwntidussfhvc'; // SMTP password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('your-email@example.com', 'Your Name');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';

            // Modern email body
            $mail->Body = '
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f2f2f2;
                        margin: 0;
                        padding: 0;
                    }
                    .email-container {
                        width: 100%;
                        max-width: 600px;
                        margin: 0 auto;
                        background-color: #ffffff;
                        border-radius: 8px;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                        overflow: hidden;
                    }
                    .header {
                        background-color: #B80000;
                        color: #ffffff;
                        text-align: center;
                        padding: 20px;
                    }
                    .header img {
                        max-width: 100%;
                        height: auto;
                    }
                    .content {
                        padding: 30px;
                        text-align: center;
                    }
                    .content h1 {
                        color: #333333;
                        font-size: 24px;
                        margin-bottom: 20px;
                    }
                    .content p {
                        color: #666666;
                        font-size: 16px;
                        line-height: 1.5;
                    }
                    a {
                        color: #FFFFFF;
                    }
                    .btn {
                        display: inline-block;
                        padding: 12px 24px;
                        font-size: 16px;
                        font-weight: bold;
                        color: #FFFFFF;
                        background-color: #B80000;
                        text-decoration: none;
                        border-radius: 4px;
                        margin-top: 20px;
                    }
                    .footer {
                        background-color: #f9f9f9;
                        padding: 20px;
                        text-align: center;
                        color: #999999;
                        font-size: 14px;
                    }
            
                    @media only screen and (max-width: 600px) {
                        .email-container {
                            width: 90%;
                        }
                        .content {
                            padding: 20px;
                        }
                        .content h1 {
                            font-size: 20px;
                        }
                        .content p {
                            font-size: 14px;
                        }
                        .btn {
                            padding: 10px 20px;
                            font-size: 14px;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="email-container">
                    <div class="header">
                        <img src="https://bccenrollment.xyz/bcc-banner.png" alt="Company Logo">
                    </div>
                    <div class="content">
                        <h1>Password Reset Request</h1>
                        <p>You have requested a password reset. Click the button below to reset your password:</p>
                        <a href="http://localhost/Enrollment-System/views/login/reset_password.php?token=' . $reset_token . '" class="btn">Reset Password</a>
                    </div>
                    <div class="footer">
                        <p>Binangonan Catholic College<br>123 Street Address<br>City, State, ZIP<br>© ' . date('Y') . ' BCC. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>';

            if (!$mail->send()) {
                $msg = "Error sending email: " . $mail->ErrorInfo;
            } else {
                $msg = "A password reset link has been sent to your email address.";
                $msgType = 'success'; // Set to success when email is sent successfully
            }
        } else {
            $msg = "Error inserting reset token: " . $stmt_insert_token->errorInfo()[2];
        }
    } else {
        $msg = "No account found with that email address.";
    }

    $stmt_check_email->closeCursor();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="../../assets/css/output.css" rel="stylesheet">
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
    <form id="forgotPasswordForm" class="space-y-6" action="forgot_password.php" method="post">
        <?php if (!empty($msg)): ?>
            <p id="statusMessage" class="message <?php echo ($msgType === 'success') ? 'message-success' : ''; ?>">
                <?php echo htmlspecialchars($msg); ?>
            </p>
        <?php endif; ?>
        <h4 class="font-body text-[1.8rem] text-custom-red font-semibold">Forgot Password</h4>
        <h5 class="font-body text-[1.2rem] text-custom-red font-medium tracking-wider">
            Already have an account? 
            <a href="../../index.php" class="inline-block underline text-custom-darkRed font-bold" id="signInLink">Sign In</a>
        </h5>

        <div>
            <label for="email" class="block font-body text-[1.2rem] font-semibold leading-6 text-custom-black">Email Address</label>
            <div class="mt-2">
                <input id="email" name="email" type="email" autocomplete="email" placeholder="you@example.com" style="outline:none;"
                       class="block w-full rounded-md border-0 p-[1rem] text-gray-900 shadow-md ring-2 ring-inset ring-gray-300 placeholder:text-gray-300">          
                <div id="emailError" class="text-red-600 text-sm mt-1"></div>
            </div>
        </div>
        <button type="submit" class="flex w-full justify-center rounded-md bg-custom-red px-3 py-5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-red-600">
            <span>Send Reset Link</span>
        </button>
       
    </form>
    <div class="spinner" id="spinner"></div> <!-- Spinner element -->
</div>

<script>
document.addEventListener('DOMContentLoaded', (event) => {
    const messageElement = document.getElementById('statusMessage');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const spinner = document.getElementById('spinner');
    const signInLink = document.getElementById('signInLink')

    if (messageElement) {
        // Set the timeout to fade out the message after 5 seconds
        setTimeout(() => {
            messageElement.style.opacity = '0';
            setTimeout(() => {
                messageElement.style.display = 'none';
            }, 500); // Match duration of transition
        }, 5000); // 5000 milliseconds = 5 seconds
    }

       // Handle form submission
       forgotPasswordForm.addEventListener('submit', (event) => {
            event.preventDefault();
            spinner.style.display = 'block'; // Show spinner

            // Submit form after delay
            setTimeout(() => {
                forgotPasswordForm.submit();
            }, 2000); // 2000 milliseconds = 2 seconds
        });

           // Handle forgot sign in link click
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
