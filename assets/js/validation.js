// ==========================================
// DOM READY - Initialize validation when page loads
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    // Check which form exists on the page and initialize its validation
    if (document.getElementById('registerForm')) {
        initRegisterValidation();
    }
    if (document.getElementById('editProfileForm')) {
        initEditProfileValidation();
    }
    if (document.getElementById('changePasswordForm')) {
        initChangePasswordValidation();
    }
});

// ==========================================
// VALIDATION FUNCTIONS
// ==========================================

// Validate Email Format
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Validate Phone (Only Numbers)
function validatePhone(phone) {
    const phoneRegex = /^\d+$/;
    return phoneRegex.test(phone);
}

// Validate Name (Letters and Spaces Only)
function validateName(name) {
    const nameRegex = /^[a-zA-ZÀ-ÿ\s]+$/;
    return nameRegex.test(name);
}

// Show Validation Message
function showValidationMessage(fieldId, message, isValid) {
    const messageElement = document.getElementById(fieldId + '_message');
    const inputElement = document.getElementById(fieldId);
    
    if (messageElement) {
        messageElement.textContent = message;
        messageElement.className = 'validation-message ' + (isValid ? 'success' : 'error');
    }
    
    if (inputElement) {
        inputElement.className = inputElement.className.replace(/\s*(error|success)/g, '');
        if (message !== '') {
            inputElement.className += ' ' + (isValid ? 'success' : 'error');
        }
    }
}

// Clear Validation Message
function clearValidationMessage(fieldId) {
    const messageElement = document.getElementById(fieldId + '_message');
    const inputElement = document.getElementById(fieldId);
    
    if (messageElement) {
        messageElement.textContent = '';
        messageElement.className = 'validation-message';
    }
    
    if (inputElement) {
        inputElement.className = inputElement.className.replace(/\s*(error|success)/g, '');
    }
}

// Validate File Size and Type
function validateFile(file) {
    if (!file) return { valid: true, message: '' };
    
    const maxSize = 2 * 1024 * 1024; // 2MB
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    
    if (file.size > maxSize) {
        return { valid: false, message: 'File size must be less than 2MB' };
    }
    
    if (!allowedTypes.includes(file.type)) {
        return { valid: false, message: 'Only JPG, PNG, and GIF files are allowed' };
    }
    
    return { valid: true, message: 'File is valid' };
}

// Preview Image
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('preview-image');
            const placeholder = document.getElementById('preview-placeholder');
            
            if (preview) {
                preview.src = e.target.result;
            } else if (placeholder) {
                placeholder.outerHTML = '<img src="' + e.target.result + '" alt="Profile Picture" id="preview-image" style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 4px solid #667eea;">';
            }
        }
        reader.readAsDataURL(file);
    }
}


// ==========================================
// REGISTER FORM VALIDATION
// ==========================================
function initRegisterValidation() {
    const form = document.getElementById('registerForm');
    if (!form) return;
    
    const userTypeField = document.getElementById('user_type');
    const firstNameField = document.getElementById('first_name');
    const lastNameField = document.getElementById('last_name');
    const emailField = document.getElementById('email');
    const phoneField = document.getElementById('phone');
    const profilePictureField = document.getElementById('profile_picture');
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    
    // User Type validation
    userTypeField.addEventListener('change', function() {
        if (this.value === '') {
            showValidationMessage('user_type', 'Please select a FrontOffice type', false);
        } else {
            showValidationMessage('user_type', 'User type selected', true);
        }
    });
    
    // First Name validation
    firstNameField.addEventListener('input', function() {
        const value = this.value.trim();
        
        if (value === '') {
            showValidationMessage('first_name', 'First name is required', false);
        } else if (!validateName(value)) {
            showValidationMessage('first_name', 'First name can only contain letters and spaces', false);
        } else if (value.length < 2) {
            showValidationMessage('first_name', 'First name must be at least 2 characters', false);
        } else {
            showValidationMessage('first_name', 'First name is valid', true);
        }
    });
    
    // Last Name validation
    lastNameField.addEventListener('input', function() {
        const selectedType = document.querySelector('input[name="account_type"]:checked').value;
        
        // Skip validation if association type
        if (selectedType === 'association') {
            clearValidationMessage('last_name');
            return;
        }
        
        const value = this.value.trim();

        if (value === '') {
            showValidationMessage('last_name', 'Le nom est requis', false);
        } else if (!validateName(value)) {
            showValidationMessage('last_name', 'Le nom ne peut contenir que des lettres et espaces', false);
        } else if (value.length < 2) {
            showValidationMessage('last_name', 'Le nom doit contenir au moins 2 caractères', false);
        } else {
            showValidationMessage('last_name', 'Nom valide', true);
        }
    });

    // Email validation
    emailField.addEventListener('input', function() {
        const value = this.value.trim();
        
        if (value === '') {
            showValidationMessage('email', 'Email is required', false);
        } else if (!validateEmail(value)) {
            showValidationMessage('email', 'Please enter a valid email address', false);
        } else {
            showValidationMessage('email', 'Email is valid', true);
        }
    });
    
    // Phone validation
    phoneField.addEventListener('input', function() {
        const value = this.value.trim();
        
        if (value === '') {
            clearValidationMessage('phone');
        } else if (!validatePhone(value)) {
            showValidationMessage('phone', 'Phone number can only contain numbers', false);
        } else if (value.length < 8) {
            showValidationMessage('phone', 'Phone number must be at least 8 digits', false);
        } else {
            showValidationMessage('phone', 'Phone number is valid', true);
        }
    });
    
    // Profile Picture validation
    profilePictureField.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) {
            clearValidationMessage('profile_picture');
            return;
        }
        
        const validation = validateFile(file);
        if (!validation.valid) {
            showValidationMessage('profile_picture', validation.message, false);
            this.value = '';
        } else {
            showValidationMessage('profile_picture', validation.message, true);
            previewImage({ target: this });
        }
    });
    
    // Password validation
    passwordField.addEventListener('input', function() {
        const value = this.value;
        
        if (value === '') {
            showValidationMessage('password', 'Password is required', false);
        } else if (value.length < 6) {
            showValidationMessage('password', 'Password must be at least 6 characters', false);
        } else {
            showValidationMessage('password', 'Password is strong enough', true);
        }
        
        // Re-validate confirm password when password changes
        if (confirmPasswordField.value !== '') {
            const confirmValue = confirmPasswordField.value;
            if (confirmValue !== value) {
                showValidationMessage('confirm_password', 'Passwords do not match', false);
            } else {
                showValidationMessage('confirm_password', 'Passwords match', true);
            }
        }
    });
    
    // Confirm Password validation
    confirmPasswordField.addEventListener('input', function() {
        const value = this.value;
        const passwordValue = passwordField.value;
        
        if (value === '') {
            showValidationMessage('confirm_password', 'Please confirm your password', false);
        } else if (value !== passwordValue) {
            showValidationMessage('confirm_password', 'Passwords do not match', false);
        } else {
            showValidationMessage('confirm_password', 'Passwords match', true);
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const selectedType = document.querySelector('input[name="account_type"]:checked').value;

        
        // Validate User Type
        if (userTypeField.value === '') {
            showValidationMessage('user_type', 'Please select a FrontOffice type', false);
            isValid = false;
        }
        
        // Validate First Name
        const firstName = firstNameField.value.trim();
        if (firstName === '') {
            showValidationMessage('first_name', 'First name is required', false);
            isValid = false;
        } else if (!validateName(firstName)) {
            showValidationMessage('first_name', 'First name can only contain letters and spaces', false);
            isValid = false;
        }

        // Validate Last Name only for citizens
        if (selectedType === 'citoyen') {
            const lastName = lastNameField.value.trim();
            if (lastName === '') {
                showValidationMessage('last_name', 'Le nom est requis', false);
                isValid = false;
            } else if (!validateName(lastName)) {
                showValidationMessage('last_name', 'Caractères invalides', false);
                isValid = false;
            }
        }
        
        // Validate Email
        const email = emailField.value.trim();
        if (email === '') {
            showValidationMessage('email', 'Email is required', false);
            isValid = false;
        } else if (!validateEmail(email)) {
            showValidationMessage('email', 'Please enter a valid email address', false);
            isValid = false;
        }
        
        // Validate Phone (if provided)
        const phone = phoneField.value.trim();
        if (phone !== '' && !validatePhone(phone)) {
            showValidationMessage('phone', 'Phone number can only contain numbers', false);
            isValid = false;
        }
        
        // Validate Password
        const password = passwordField.value;
        if (password === '') {
            showValidationMessage('password', 'Password is required', false);
            isValid = false;
        } else if (password.length < 6) {
            showValidationMessage('password', 'Password must be at least 6 characters', false);
            isValid = false;
        }
        
        // Validate Confirm Password
        const confirmPassword = confirmPasswordField.value;
        if (confirmPassword === '') {
            showValidationMessage('confirm_password', 'Please confirm your password', false);
            isValid = false;
        } else if (confirmPassword !== password) {
            showValidationMessage('confirm_password', 'Passwords do not match', false);
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
}

// ==========================================
// EDIT PROFILE FORM VALIDATION
// ==========================================
function initEditProfileValidation() {
    const form = document.getElementById('editProfileForm');
    if (!form) return;
    
    const firstNameField = document.getElementById('edit_first_name');
    const lastNameField = document.getElementById('edit_last_name');
    const emailField = document.getElementById('edit_email');
    const phoneField = document.getElementById('edit_phone');
    const profilePictureField = document.getElementById('profile_picture');
    
    // First Name validation
    firstNameField.addEventListener('input', function() {
        const value = this.value.trim();
        
        if (value === '') {
            showValidationMessage('edit_first_name', 'First name is required', false);
        } else if (!validateName(value)) {
            showValidationMessage('edit_first_name', 'First name can only contain letters and spaces', false);
        } else if (value.length < 2) {
            showValidationMessage('edit_first_name', 'First name must be at least 2 characters', false);
        } else {
            showValidationMessage('edit_first_name', 'First name is valid', true);
        }
    });
    
    // Last Name validation
    lastNameField.addEventListener('input', function() {
        const value = this.value.trim();
        
        if (value === '') {
            showValidationMessage('edit_last_name', 'Last name is required', false);
        } else if (!validateName(value)) {
            showValidationMessage('edit_last_name', 'Last name can only contain letters and spaces', false);
        } else if (value.length < 2) {
            showValidationMessage('edit_last_name', 'Last name must be at least 2 characters', false);
        } else {
            showValidationMessage('edit_last_name', 'Last name is valid', true);
        }
    });
    
    // Email validation
    emailField.addEventListener('input', function() {
        const value = this.value.trim();
        
        if (value === '') {
            showValidationMessage('edit_email', 'Email is required', false);
        } else if (!validateEmail(value)) {
            showValidationMessage('edit_email', 'Please enter a valid email address', false);
        } else {
            showValidationMessage('edit_email', 'Email is valid', true);
        }
    });
    
    // Phone validation
    phoneField.addEventListener('input', function() {
        const value = this.value.trim();
        
        if (value === '') {
            clearValidationMessage('edit_phone');
        } else if (!validatePhone(value)) {
            showValidationMessage('edit_phone', 'Phone number can only contain numbers', false);
        } else if (value.length < 8) {
            showValidationMessage('edit_phone', 'Phone number must be at least 8 digits', false);
        } else {
            showValidationMessage('edit_phone', 'Phone number is valid', true);
        }
    });
    
    // Profile Picture validation
    profilePictureField.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) {
            clearValidationMessage('profile_picture');
            return;
        }
        
        const validation = validateFile(file);
        if (!validation.valid) {
            showValidationMessage('profile_picture', validation.message, false);
            this.value = '';
        } else {
            showValidationMessage('profile_picture', validation.message, true);
            previewImage({ target: this });
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validate First Name
        const firstName = firstNameField.value.trim();
        if (firstName === '') {
            showValidationMessage('edit_first_name', 'First name is required', false);
            isValid = false;
        } else if (!validateName(firstName)) {
            showValidationMessage('edit_first_name', 'First name can only contain letters and spaces', false);
            isValid = false;
        }
        
        // Validate Last Name
        const lastName = lastNameField.value.trim();
        if (lastName === '') {
            showValidationMessage('edit_last_name', 'Last name is required', false);
            isValid = false;
        } else if (!validateName(lastName)) {
            showValidationMessage('edit_last_name', 'Last name can only contain letters and spaces', false);
            isValid = false;
        }
        
        // Validate Email
        const email = emailField.value.trim();
        if (email === '') {
            showValidationMessage('edit_email', 'Email is required', false);
            isValid = false;
        } else if (!validateEmail(email)) {
            showValidationMessage('edit_email', 'Please enter a valid email address', false);
            isValid = false;
        }
        
        // Validate Phone (if provided)
        const phone = phoneField.value.trim();
        if (phone !== '' && !validatePhone(phone)) {
            showValidationMessage('edit_phone', 'Phone number can only contain numbers', false);
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
}

// ==========================================
// CHANGE PASSWORD FORM VALIDATION
// ==========================================
function initChangePasswordValidation() {
    const form = document.getElementById('changePasswordForm');
    if (!form) return;
    
    const currentPasswordField = document.getElementById('current_password');
    const newPasswordField = document.getElementById('new_password');
    const confirmPasswordField = document.getElementById('change_confirm_password');
    
    // Current Password validation
    currentPasswordField.addEventListener('input', function() {
        const value = this.value;
        
        if (value === '') {
            showValidationMessage('current_password', 'Current password is required', false);
        } else {
            showValidationMessage('current_password', 'Current password entered', true);
        }
    });
    
    // New Password validation
    newPasswordField.addEventListener('input', function() {
        const value = this.value;
        const currentValue = currentPasswordField.value;
        
        if (value === '') {
            showValidationMessage('new_password', 'New password is required', false);
        } else if (value.length < 6) {
            showValidationMessage('new_password', 'Password must be at least 6 characters', false);
        } else if (value === currentValue && currentValue !== '') {
            showValidationMessage('new_password', 'New password must be different from current password', false);
        } else {
            showValidationMessage('new_password', 'Password is strong enough', true);
        }
        
        // Re-validate confirm password when new password changes
        if (confirmPasswordField.value !== '') {
            const confirmValue = confirmPasswordField.value;
            if (confirmValue !== value) {
                showValidationMessage('change_confirm_password', 'Passwords do not match', false);
            } else {
                showValidationMessage('change_confirm_password', 'Passwords match', true);
            }
        }
    });
    
    // Confirm Password validation
    confirmPasswordField.addEventListener('input', function() {
        const value = this.value;
        const newPasswordValue = newPasswordField.value;
        
        if (value === '') {
            showValidationMessage('change_confirm_password', 'Please confirm your new password', false);
        } else if (value !== newPasswordValue) {
            showValidationMessage('change_confirm_password', 'Passwords do not match', false);
        } else {
            showValidationMessage('change_confirm_password', 'Passwords match', true);
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validate Current Password
        const currentPassword = currentPasswordField.value;
        if (currentPassword === '') {
            showValidationMessage('current_password', 'Current password is required', false);
            isValid = false;
        }
        
        // Validate New Password
        const newPassword = newPasswordField.value;
        if (newPassword === '') {
            showValidationMessage('new_password', 'New password is required', false);
            isValid = false;
        } else if (newPassword.length < 6) {
            showValidationMessage('new_password', 'Password must be at least 6 characters', false);
            isValid = false;
        } else if (newPassword === currentPassword) {
            showValidationMessage('new_password', 'New password must be different from current password', false);
            isValid = false;
        }
        
        // Validate Confirm Password
        const confirmPassword = confirmPasswordField.value;
        if (confirmPassword === '') {
            showValidationMessage('change_confirm_password', 'Please confirm your new password', false);
            isValid = false;
        } else if (confirmPassword !== newPassword) {
            showValidationMessage('change_confirm_password', 'Passwords do not match', false);
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
}

