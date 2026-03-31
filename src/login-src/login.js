//url
const loginUrl = `/app/api.php?path=login`;

import { updateHeader } from '../main.js';

// ====== NOTIFICATION MODAL ======
function showNotification(title, message, callback = null) {
    const modal = document.getElementById('notification-modal');
    const titleEl = document.getElementById('notification-title');
    const msgEl = document.getElementById('notification-message');
    const btn = document.getElementById('notification-btn');

    if (!modal) return;

    titleEl.textContent = title;
    msgEl.textContent = message;
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    const closeHandler = () => {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        if (callback) callback();
        btn.removeEventListener('click', closeHandler);
        document.getElementById('close-notification-btn').removeEventListener('click', closeHandler);
    };

    btn.addEventListener('click', closeHandler);
    document.getElementById('close-notification-btn').addEventListener('click', closeHandler);
}

// ====== CODE INPUT MODAL ======
function showCodeInputModal() {
    return new Promise((resolve) => {
        const modal = document.getElementById('code-input-modal');
        const inputField = document.getElementById('code-input-field');
        const submitBtn = document.getElementById('code-input-submit-btn');
        const cancelBtn = document.getElementById('code-input-cancel-btn');
        const closeBtn = document.getElementById('close-code-input-btn');
        const errorEl = document.getElementById('code-input-error');

        if (!modal) {
            resolve(null);
            return;
        }

        // Clear previous values
        inputField.value = '';
        errorEl.textContent = '';

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        inputField.focus();

        const closeModal = (value = null) => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            resolve(value);
            // Remove event listeners
            submitBtn.removeEventListener('click', handleSubmit);
            cancelBtn.removeEventListener('click', handleCancel);
            closeBtn.removeEventListener('click', handleCancel);
            inputField.removeEventListener('keypress', handleKeypress);
        };

        const handleSubmit = () => {
            const code = inputField.value.trim();
            if (!code) {
                errorEl.textContent = 'Please enter the verification code.';
                return;
            }
            if (code.length !== 6 || !/^\d+$/.test(code)) {
                errorEl.textContent = 'Code must be exactly 6 digits.';
                return;
            }
            closeModal(code);
        };

        const handleCancel = () => {
            closeModal(null);
        };

        const handleKeypress = (e) => {
            if (e.key === 'Enter') {
                handleSubmit();
            }
        };

        submitBtn.addEventListener('click', handleSubmit);
        cancelBtn.addEventListener('click', handleCancel);
        closeBtn.addEventListener('click', handleCancel);
        inputField.addEventListener('keypress', handleKeypress);
    });
}

// ====== FORCE PASSWORD CHANGE MODAL ======
function showForcePasswordChangeModal(userId, username, tempPassword) {
    return new Promise((resolve) => {
        const modal = document.getElementById('force-password-change-modal');
        const newPasswordField = document.getElementById('force-new-password');
        const confirmPasswordField = document.getElementById('force-confirm-password');
        const submitBtn = document.getElementById('force-pw-submit-btn');
        const cancelBtn = document.getElementById('force-pw-cancel-btn');
        const closeBtn = document.getElementById('close-force-pw-btn');
        const errorEl = document.getElementById('force-pw-error');

        if (!modal) {
            resolve(null);
            return;
        }

        // Clear previous values
        newPasswordField.value = '';
        confirmPasswordField.value = '';
        errorEl.textContent = '';

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        newPasswordField.focus();

        const closeModal = (value = null) => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            resolve(value);
            // Remove event listeners
            submitBtn.removeEventListener('click', handleSubmit);
            cancelBtn.removeEventListener('click', handleCancel);
            closeBtn.removeEventListener('click', handleCancel);
        };

        const handleSubmit = async () => {
            const newPassword = newPasswordField.value.trim();
            const confirmPassword = confirmPasswordField.value.trim();

            // Validation
            if (!newPassword || !confirmPassword) {
                errorEl.textContent = 'Please enter both passwords.';
                return;
            }
            if (newPassword.length < 8) {
                errorEl.textContent = 'Password must be at least 8 characters long.';
                return;
            }
            if (newPassword !== confirmPassword) {
                errorEl.textContent = 'Passwords do not match.';
                return;
            }

            errorEl.textContent = 'Updating...';
            errorEl.classList.add('text-yellow-600');

            try {
                const response = await fetch(loginUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'force_password_change',
                        user_id: userId,
                        old_password: tempPassword,
                        new_password: newPassword,
                        confirm_password: confirmPassword
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    errorEl.classList.remove('text-yellow-600');
                    closeModal({ success: true });
                } else {
                    errorEl.classList.remove('text-yellow-600');
                    errorEl.classList.add('text-red-600');
                    errorEl.textContent = result.message || 'Failed to update password.';
                }
            } catch (error) {
                console.error('Error:', error);
                errorEl.classList.remove('text-yellow-600');
                errorEl.classList.add('text-red-600');
                errorEl.textContent = 'Server connection error.';
            }
        };

        const handleCancel = () => {
            closeModal(null);
        };

        submitBtn.addEventListener('click', handleSubmit);
        cancelBtn.addEventListener('click', handleCancel);
        closeBtn.addEventListener('click', handleCancel);
    });
}

// ====== FORGOT PASSWORD MODAL OPEN/CLOSE ======
document.addEventListener('click', (event) => {
    const forgotModal = document.getElementById('forgot-pw-modal');
    if (!forgotModal) return;

    // 1. Open: When the forgot password button is clicked
    if (event.target.closest('[data-target="forgot-pw-view"]')) {
        event.preventDefault();
        forgotModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Disable background scrolling

    }

    // 2. Close: X button OR click on dark background
    if (event.target.closest('#close-forgot-btn') || event.target === forgotModal) {
        forgotModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('forgot-pw-error').innerHTML = ''; // Clear error message on close
        document.getElementById('forgot-pw-form').reset(); // Clear input on close
    }
});

// ====== FORM SUBMISSION LOGIC ======
document.addEventListener('submit', async (event) => {
    
    // --- 1. LOGIN LOGIC ---
    if (event.target.id === 'login-form') {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            
            const response = await fetch(loginUrl, {
                method: 'POST',
                credentials: 'include', 
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                localStorage.setItem('isLoggedIn', 'true');
                if (result.user && result.user.username) {
                    localStorage.setItem('username', result.user.username);
                }
                if (result.user && result.user.avatar) {
                    localStorage.setItem('userAvatar', result.user.avatar);
                }

                updateHeader();
                showNotification('Success', 'Welcome to Troxan!', () => {
                    window.location.href = '/profile';
                });

            } else if (result.code === 'not_verified') {
                const verificationCode = await showCodeInputModal();

                if (verificationCode) {
                    const verifyResponse = await fetch('/app/api.php?path=registration', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'verify_code',
                            email: data.email,
                            verification_code: verificationCode
                        })
                    });

                    const verifyResult = await verifyResponse.json();

                    if (verifyResponse.ok) {
                        showNotification('Verified', 'Email verified successfully! You can now log in.', () => {
                            form.dispatchEvent(new Event('submit', { cancelable: true }));
                        });
                    } else {
                        showNotification('Error', 'Invalid verification code: ' + verifyResult.message);
                    }
                }
            } else if (result.code === 'force_password_change') {
                // Store temporary password and user ID for the password change modal
                const tempPassword = data.password;
                sessionStorage.setItem('tempUserId', result.user_id);
                
                const changeResult = await showForcePasswordChangeModal(result.user_id, result.username, tempPassword);

                if (changeResult && changeResult.success) {
                    showNotification('Success', 'Password changed successfully! Logging in...', () => {
                        sessionStorage.removeItem('tempUserId');
                        // Automatically login with new password
                        form.dispatchEvent(new Event('submit', { cancelable: true }));
                    });
                } else if (changeResult === null) {
                    showNotification('Error', 'Password change cancelled.');
                }
            } else {
                showNotification('Error', result.message || 'Login failed. Please try again.');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error', 'Unexpected error occurred. Please try again.');
        }
    }

    // --- 2. FORGOT PASSWORD LOGIC ---
    if (event.target.id === 'forgot-pw-form') {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        data.action = 'forgot_password'; 
        
        const errorDiv = document.getElementById('forgot-pw-error');
        errorDiv.innerHTML = 'Loading...';
        errorDiv.classList.replace('text-red-600', 'text-gray-600');

        try {
            const response = await fetch(loginUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                errorDiv.classList.replace('text-gray-600', 'text-green-600');
                errorDiv.innerHTML = '✔ ' + result.message;
            } else {
                errorDiv.classList.replace('text-gray-600', 'text-red-600');
                errorDiv.innerHTML = result.message; 
            }
        } catch (error) {
            console.error('Error:', error);
            errorDiv.classList.replace('text-gray-600', 'text-red-600');
            errorDiv.innerHTML = "Server connection error!";
        }
    }
});