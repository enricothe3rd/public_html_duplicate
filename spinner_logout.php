<!-- Spinner and Text Container -->
<div class="flex-center">
  <!-- Spinner HTML -->
  <div id="spinner">
    <div class="loader"></div>
  </div>

  <!-- Animated Text -->
  <h1 class="animated-text">Logging out</h1>

</div>

<style>
  /* General Reset */
  body {
    margin: 0;
    padding: 0;
    font-family: 'Arial', sans-serif; /* Use a modern sans-serif font */
    background-color: #f0f4f8; /* Light background color */
  }

  /* Flex Center */
  .flex-center {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    flex-direction: column; /* Stack the spinner and text */
    text-align: center; /* Center text */
  }

  /* Spinner Styles */
  #spinner {
    margin-bottom: 20px; /* Space between the spinner and text */
  }

  .loader {
    border: 4px solid #e0e0e0; /* Light gray */
    border-top: 4px solid #3498db; /* Blue */
    border-radius: 50%;
    width: 50px; /* Increased size for better visibility */
    height: 50px; /* Increased size for better visibility */
    animation: spin 1.5s linear infinite; /* Faster spin for modern effect */
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }

  /* Animated Text Styles */
  .animated-text {
    font-size: 28px; /* Increased font size */
    color: #333; /* Dark text color for better contrast */
    margin-top: 20px; /* Space between the spinner and text */
    animation: moveUpDown 1s ease-in-out infinite; /* Apply the animation */
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
  }

  @keyframes moveUpDown {
    0%, 100% { transform: translateY(0); } /* Original position */
    50% { transform: translateY(-10px); } /* Move up */
  }
</style>

<script>
  // Show spinner for 3 seconds, then redirect
  function loadPage() {
    setTimeout(function() {
      window.location.href = 'index.php'; // Redirect to index.php
    }, 2000); // 3000ms = 3 seconds
  }

  // Call the loadPage function when the page loads
  window.onload = loadPage;
</script>
