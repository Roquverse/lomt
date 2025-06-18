document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("registrationForm");
  let isSubmitting = false; // Flag to prevent double submission

  // Create popup element
  const popup = document.createElement("div");
  popup.style.cssText = `
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.2);
    z-index: 1000;
    text-align: center;
    display: none;
  `;
  document.body.appendChild(popup);

  // Create overlay
  const overlay = document.createElement("div");
  overlay.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 999;
    display: none;
  `;
  document.body.appendChild(overlay);

  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault(); // Always prevent default form submission

      // Prevent double submission
      if (isSubmitting) {
        return;
      }

      isSubmitting = true;
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalBtnText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = "Processing...";

      // Collect form data
      const formData = new FormData(form);

      // Send AJAX request
      fetch("lomt5-register-process.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Show success popup
            popup.innerHTML = `
              <div style="font-size: 48px; color: #28a745; margin-bottom: 15px;">✓</div>
              <h3 style="color: #28a745; margin-bottom: 15px;">Registration Successful!</h3>
              <p style="margin-bottom: 20px;">${data.message}</p>
            `;
            popup.style.display = "block";
            overlay.style.display = "block";

            // Reset form
            form.reset();

            // Refresh page after 3 seconds
            setTimeout(() => {
              window.location.reload();
            }, 3000);
          } else {
            // Show error popup
            popup.innerHTML = `
              <h3 style="color: #dc3545; margin-bottom: 15px;">Registration Failed</h3>
              <p style="margin-bottom: 20px;">${
                data.message || "Please try again."
              }</p>
            `;
            popup.style.display = "block";
            overlay.style.display = "block";

            // Hide popup after 3 seconds
            setTimeout(() => {
              popup.style.display = "none";
              overlay.style.display = "none";
            }, 3000);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          // Show error popup
          popup.innerHTML = `
            <h3 style="color: #dc3545; margin-bottom: 15px;">Error</h3>
            <p style="margin-bottom: 20px;">An error occurred. Please try again later.</p>
          `;
          popup.style.display = "block";
          overlay.style.display = "block";

          // Hide popup after 3 seconds
          setTimeout(() => {
            popup.style.display = "none";
            overlay.style.display = "none";
          }, 3000);
        })
        .finally(() => {
          // Re-enable submit button
          isSubmitting = false;
          submitBtn.disabled = false;
          submitBtn.textContent = originalBtnText;
        });
    });
  }
});
