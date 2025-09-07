// Main Application JavaScript
import './bootstrap';

// Global utilities
window.Utils = {
    formatTime: function(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;

        if (diff < 60000) { // Less than 1 minute
            return 'Just now';
        } else if (diff < 3600000) { // Less than 1 hour
            return Math.floor(diff / 60000) + 'm ago';
        } else if (diff < 86400000) { // Less than 1 day
            return Math.floor(diff / 3600000) + 'h ago';
        } else {
            return date.toLocaleDateString();
        }
    },

    formatFileSize: function(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    showNotification: function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
};

// Global error handler
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    Utils.showNotification('An unexpected error occurred', 'error');
});

// Unhandled promise rejection handler
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled promise rejection:', e.reason);
    Utils.showNotification('An error occurred while processing your request', 'error');
});

// Check authentication status
document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('auth_token');
    const currentPath = window.location.pathname;
    
    // Redirect to login if not authenticated and not on auth pages
    if (!token && !currentPath.includes('/login') && !currentPath.includes('/register')) {
        window.location.href = '/login';
        return;
    }
    
    // Redirect to chat if authenticated and on auth pages
    if (token && (currentPath.includes('/login') || currentPath.includes('/register'))) {
        window.location.href = '/chat';
        return;
    }
});

// Add notification styles
const notificationStyles = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 300px;
        word-wrap: break-word;
    }
    
    .notification.show {
        transform: translateX(0);
    }
    
    .notification-info {
        background: #3182ce;
    }
    
    .notification-success {
        background: #38a169;
    }
    
    .notification-warning {
        background: #d69e2e;
    }
    
    .notification-error {
        background: #e53e3e;
    }
`;

// Inject notification styles
const styleSheet = document.createElement('style');
styleSheet.textContent = notificationStyles;
document.head.appendChild(styleSheet);