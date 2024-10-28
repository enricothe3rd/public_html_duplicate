<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading Spinner</title>
    <style>
        /* Overlay for spinner to cover the whole page */
        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8); /* Slight transparent white background */
            z-index: 9999; /* High z-index to stay on top */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Spinner animation */
        .spinner {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <!-- Spinner Overlay -->
    <div class="spinner-overlay" id="loading-spinner">
        <div class="spinner"></div>
    </div>

</body>
</html>
