// Main JavaScript file for Training Platform

document.addEventListener('DOMContentLoaded', function() {
    initApp();
});

function initApp() {
    // Form validation
    initFormValidation();
    
    // Chat functionality
    initChat();
    
    // Material upload preview
    initMaterialUpload();
    
    // Navigation active state
    initNavigation();
    
    // Course interactions
    initCourseInteractions();
}

// Form Validation
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    showError(field, 'This field is required');
                } else {
                    clearError(field);
                }
                
                // Email validation
                if (field.type === 'email' && field.value.trim()) {
                    if (!isValidEmail(field.value)) {
                        isValid = false;
                        showError(field, 'Please enter a valid email address');
                    }
                }
                
                // Password validation
                if (field.type === 'password' && field.value.trim()) {
                    if (field.value.length < 6) {
                        isValid = false;
                        showError(field, 'Password must be at least 6 characters long');
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill all required fields correctly', 'error');
            }
        });
    });
}

// Email validation helper
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Error handling
function showError(field, message) {
    clearError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.color = '#e74c3c';
    errorDiv.style.fontSize = '0.8rem';
    errorDiv.style.marginTop = '5px';
    
    field.style.borderColor = '#e74c3c';
    field.parentNode.appendChild(errorDiv);
}

function clearError(field) {
    field.style.borderColor = '';
    const existingError = field.parentNode.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
}

// Chat functionality
function initChat() {
    const conversations = document.querySelectorAll('.conversation');
    const chatInput = document.querySelector('.chat-input');
    const messageInput = document.querySelector('#message-input');
    const sendButton = document.querySelector('#send-button');
    
    // Conversation selection
    conversations.forEach(conv => {
        conv.addEventListener('click', function() {
            // Remove active class from all conversations
            conversations.forEach(c => c.classList.remove('active'));
            // Add active class to clicked conversation
            this.classList.add('active');
            
            const userId = this.getAttribute('data-user');
            const courseId = this.getAttribute('data-course');
            
            // Show chat input
            chatInput.style.display = 'flex';
            
            // Load conversation
            loadConversation(userId, courseId);
        });
    });
    
    // Send message
    if (sendButton && messageInput) {
        sendButton.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }
}

function loadConversation(userId, courseId) {
    const chatMessages = document.querySelector('.chat-messages');
    const chatHeader = document.querySelector('.chat-header h4');
    
    // Simulate loading conversation
    chatMessages.innerHTML = `
        <div class="message received">
            <p>Hello! I'm interested in learning more about this course.</p>
            <span>10:30 AM</span>
        </div>
        <div class="message sent">
            <p>Hi! I'd be happy to help you. What would you like to know?</p>
            <span>10:32 AM</span>
        </div>
        <div class="message received">
            <p>Can you tell me about the course schedule?</p>
            <span>10:33 AM</span>
        </div>
    `;
    
    // Update header
    const conversation = document.querySelector('.conversation.active');
    const userName = conversation.querySelector('h5').textContent;
    const courseName = conversation.querySelector('p').textContent;
    chatHeader.textContent = `${userName} - ${courseName}`;
    
    // Scroll to bottom
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function sendMessage() {
    const messageInput = document.querySelector('#message-input');
    const chatMessages = document.querySelector('.chat-messages');
    
    if (messageInput.value.trim()) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message sent';
        
        const messageText = document.createElement('p');
        messageText.textContent = messageInput.value;
        
        const messageTime = document.createElement('span');
        messageTime.textContent = getCurrentTime();
        
        messageDiv.appendChild(messageText);
        messageDiv.appendChild(messageTime);
        chatMessages.appendChild(messageDiv);
        
        // Clear input
        messageInput.value = '';
        
        // Scroll to bottom
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        // Simulate reply after 1 second
        setTimeout(simulateReply, 1000);
    }
}

function simulateReply() {
    const chatMessages = document.querySelector('.chat-messages');
    const replies = [
        "Thanks for your message! I'll get back to you soon.",
        "That's a great question! Let me check that for you.",
        "I appreciate your interest in the course.",
        "Let me provide you with more details about that."
    ];
    
    const randomReply = replies[Math.floor(Math.random() * replies.length)];
    
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message received';
    
    const messageText = document.createElement('p');
    messageText.textContent = randomReply;
    
    const messageTime = document.createElement('span');
    messageTime.textContent = getCurrentTime();
    
    messageDiv.appendChild(messageText);
    messageDiv.appendChild(messageTime);
    chatMessages.appendChild(messageDiv);
    
    // Scroll to bottom
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function getCurrentTime() {
    const now = new Date();
    return now.getHours().toString().padStart(2, '0') + ':' + 
           now.getMinutes().toString().padStart(2, '0');
}

// Material upload preview
function initMaterialUpload() {
    const materialTypeSelect = document.querySelector('select[name="material_type"]');
    const contentField = document.querySelector('input[name="content"], textarea[name="content"]');
    
    if (materialTypeSelect && contentField) {
        materialTypeSelect.addEventListener('change', function() {
            const type = this.value;
            const label = contentField.previousElementSibling;
            
            switch(type) {
                case 'pdf':
                    label.textContent = 'PDF File URL *';
                    contentField.placeholder = 'https://example.com/document.pdf';
                    break;
                case 'youtube':
                    label.textContent = 'YouTube Video ID *';
                    contentField.placeholder = 'dQw4w9WgXcQ';
                    break;
                case 'video_link':
                    label.textContent = 'Video URL *';
                    contentField.placeholder = 'https://example.com/video.mp4';
                    break;
            }
        });
    }
}

// Navigation active state
function initNavigation() {
    const currentPage = window.location.pathname + window.location.search;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage || 
            currentPage.includes(link.getAttribute('href').replace('index.php', ''))) {
            link.classList.add('active');
        }
    });
}

// Course interactions
function initCourseInteractions() {
    // Course card hover effects
    const courseCards = document.querySelectorAll('.course-card, .feature-card');
    courseCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
            this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
        });
    });
}

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Add styles
    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '15px 20px',
        borderRadius: '5px',
        color: 'white',
        zIndex: '1000',
        maxWidth: '300px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        fontWeight: '500'
    });
    
    switch(type) {
        case 'success':
            notification.style.background = '#27ae60';
            break;
        case 'error':
            notification.style.background = '#e74c3c';
            break;
        case 'warning':
            notification.style.background = '#f39c12';
            break;
        default:
            notification.style.background = '#3498db';
    }
    
    document.body.appendChild(notification);
    
    // Remove after 5 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.5s';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 500);
    }, 5000);
}

// AJAX helper functions
function makeRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                resolve(xhr.responseText);
            } else {
                reject(new Error(xhr.statusText));
            }
        };
        
        xhr.onerror = function() {
            reject(new Error('Network error'));
        };
        
        if (data) {
            xhr.send(new URLSearchParams(data).toString());
        } else {
            xhr.send();
        }
    });
}

// Utility functions
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Export to global scope
window.TrainingApp = {
    showNotification,
    makeRequest,
    formatDate,
    initChat,
    sendMessage
};