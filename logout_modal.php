<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="fixed inset-0 flex items-center justify-center hidden">
  <div class="bg-white rounded-lg shadow-xl p-6 md:p-8 max-w-sm w-full transform transition-all duration-300 scale-95 opacity-0">
    <h2 class="text-lg md:text-xl font-semibold text-gray-800 mb-4 text-center">Are you sure you want to log out?</h2>
    
    <div class="flex flex-col md:flex-row justify-between">
      <!-- Cancel Button -->
      <button onclick="toggleModal(false)" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-200 mb-2 md:mb-0 md:mr-2 w-full md:w-auto">
        Cancel
      </button>
      
      <!-- Confirm Logout Button -->
      <button onclick="confirmLogout()" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 w-full md:w-auto">
        Yes, Log Out
      </button>
    </div>
  </div>
</div>

<!-- JavaScript for Modal Control -->
<script>
  function toggleModal(show) {
    const modal = document.getElementById("logoutModal");
    modal.classList.toggle("hidden", !show);
    
    // Animate modal appearance
    const modalContent = modal.querySelector('div');
    if (show) {
      // Use requestAnimationFrame for smoother animations
      requestAnimationFrame(() => {
        modalContent.classList.remove("scale-95", "opacity-0");
        modalContent.classList.add("scale-100", "opacity-100");
      });
    } else {
      // Delay to allow the modal to be visible before scaling down
      modalContent.classList.remove("scale-100", "opacity-100");
      modalContent.classList.add("scale-95", "opacity-0");
    }
  }

  function confirmLogout() {
    window.location.href = "logout.php"; // Redirect to logout script
  }
</script>
