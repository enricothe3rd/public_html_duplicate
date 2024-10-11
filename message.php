<!-- message.php -->
<?php
function displayMessage($type, $title, $message) {
    // Define message styles based on the type
    $styles = [
        'success' => 'bg-green-100 border border-green-400 text-green-700',
        'error'   => 'bg-red-100 border border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border border-yellow-400 text-yellow-700',
        'info'    => 'bg-blue-100 border border-blue-400 text-blue-700',
    ];

    // Use the appropriate style based on the type parameter
    $style = $styles[$type] ?? $styles['info']; // Default to 'info' style

    // Output the message HTML
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Message</title>
        <!-- Tailwind CSS CDN -->
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <style>
            /* Keyframes for slide-in animation */
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            /* Apply animation to the message box */
            .message-box {
                animation: slideIn 0.5s ease forwards;
            }
        </style>
        <script>
            function closeMessage() {
                const messageBox = document.getElementById("messageBox");
                messageBox.style.display = "none"; // Hide the message box
            }
        </script>
    </head>
    <body class="bg-gray-100">
        <div class="fixed top-4 right-4 z-50" id="messageBox">
            <div class="' . $style . ' message-box px-4 py-3 rounded relative max-w-lg shadow-lg" role="alert">
                <strong class="font-bold">' . htmlspecialchars($title) . ': </strong>
                <span class="block sm:inline">' . htmlspecialchars($message) . '</span>
                <button onclick="closeMessage()" class="absolute top-0 right-0 mt-1 mr-2 text-xl font-bold text-gray-500 hover:text-gray-700">&times;</button>
            </div>
        </div>
    </body>
    </html>';
}
?>
