//URL
const registerUrl = `/app/api.php?path=registration`;

// Show notification modal
function showNotification(title, message, callback = null) {
    const modal = document.getElementById('notification-modal');
    const titleEl = document.getElementById('notification-title');
    const msgEl = document.getElementById('notification-message');
    const btn = document.getElementById('notification-btn');
    
    titleEl.textContent = title;
    msgEl.textContent = message;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    const closeHandler = () => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        if (callback) callback();
    };
    
    btn.addEventListener('click', closeHandler);
    document.getElementById('close-notification-btn').addEventListener('click', closeHandler);
}

// ====== MODAL (Terms and Conditions) Logic ======
document.addEventListener('click', (event) => {
    const termsModal = document.getElementById('terms-modal');
    if (!termsModal) return;

    // 1. Open: When the terms button is clicked
    if (event.target.closest('.register-terms-btn')) {
        event.preventDefault(); // Prevent accidentally checking the checkbox
        termsModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Disable page scrolling
    }

    // 2. Close: X button, "Understood" button, OR click on dark background
    if (event.target.closest('#close-terms-btn') || 
        event.target.closest('#accept-terms-btn') || 
        event.target === termsModal) {
        
        termsModal.classList.add('hidden');
        document.body.style.overflow = 'auto'; // Re-enable scrolling
    }
});

// ====== FORM SUBMISSION Logic ======
document.addEventListener('submit', async (event) => {

    if (event.target.id === 'register-form') {
        event.preventDefault(); 

        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Front-end validation (to alert before sending)
        if (data.username.length > 16 || !/^[a-zA-Z0-9]+$/.test(data.username)) {
            showNotification("Error", "Username must be alphanumeric and max 16 characters!");
            return;
        }
        if (data.password.length < 8) {
            showNotification("Error", "Password must be at least 8 characters long!");
            return;
        }

        try {
            const response = await fetch(registerUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                // Registration successful!
                showNotification("Success", "Please confirm your email address before logging in.", () => {
                    window.location.href = '/login';
                });
            } else {
                // Error occurred (e.g., username taken)
                showNotification("Error", result.message);
            }

        } catch (error) {
            console.error('Error during registration:', error);
            showNotification("Error", "Unexpected error occurred while communicating with the server.");
        }
    }
});