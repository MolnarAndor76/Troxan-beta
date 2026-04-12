//URL
const registerUrl = `/app/api.php?path=registration`;

function lockBodyScroll() {
    document.body.classList.add('troxan-no-scroll');
}

function unlockBodyScroll() {
    document.body.classList.remove('troxan-no-scroll');
}

// Show notification modal
function showNotification(title, message, callback = null) {
    const modal = document.getElementById('notification-modal');
    const titleEl = document.getElementById('notification-title');
    const msgEl = document.getElementById('notification-message');
    const btn = document.getElementById('notification-btn');
    
    titleEl.textContent = title;
    msgEl.textContent = message;
    modal.classList.remove('register-hidden');
    lockBodyScroll();
    
    const closeHandler = () => {
        modal.classList.add('register-hidden');
        unlockBodyScroll();
        if (callback) callback();
        btn.removeEventListener('click', closeHandler);
        document.getElementById('close-notification-btn').removeEventListener('click', closeHandler);
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
        termsModal.classList.remove('register-hidden');
        lockBodyScroll();
    }

    // 2. Close: X button, "Understood" button, OR click on dark background
    if (event.target.closest('#close-terms-btn') || 
        event.target.closest('#accept-terms-btn') || 
        event.target === termsModal) {
        
        termsModal.classList.add('register-hidden');
        unlockBodyScroll();
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
        if (data.username.length < 4 || data.username.length > 12 || !/^[a-zA-Z0-9]+$/.test(data.username)) {
            showNotification("Error", "Username must be alphanumeric and 4-12 characters long!");
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