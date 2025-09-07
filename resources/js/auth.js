// Authentication JavaScript
class AuthAPI {
    constructor() {
        this.baseURL = '/api';
        this.token = localStorage.getItem('auth_token');
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...options.headers
            },
            ...options
        };

        if (this.token) {
            config.headers['Authorization'] = `Bearer ${this.token}`;
        }

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'An error occurred');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    async login(credentials) {
        const data = await this.request('/auth/login', {
            method: 'POST',
            body: JSON.stringify(credentials)
        });

        if (data.success && data.data.token) {
            this.token = data.data.token;
            localStorage.setItem('auth_token', this.token);
            localStorage.setItem('user_data', JSON.stringify(data.data.user));
        }

        return data;
    }

    async register(userData) {
        const data = await this.request('/auth/register', {
            method: 'POST',
            body: JSON.stringify(userData)
        });

        return data;
    }

    async logout() {
        try {
            await this.request('/auth/logout', {
                method: 'POST'
            });
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            this.token = null;
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user_data');
        }
    }

    async getCurrentUser() {
        const data = await this.request('/auth/me');
        return data;
    }

    isAuthenticated() {
        return !!this.token;
    }
}

// Initialize API
const authAPI = new AuthAPI();

// Form handling
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }

    // Check if user is already logged in
    if (authAPI.isAuthenticated()) {
        window.location.href = '/chat';
    }
});

async function handleLogin(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);
    
    const credentials = {
        email: formData.get('email'),
        password: formData.get('password'),
        device_type: formData.get('device_type'),
        device_name: formData.get('device_name') || navigator.userAgent
    };

    try {
        setLoading(submitBtn, true);
        clearMessages();

        const response = await authAPI.login(credentials);
        
        if (response.success) {
            showMessage('Login successful! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = '/chat';
            }, 1000);
        }
    } catch (error) {
        showMessage(error.message, 'error');
    } finally {
        setLoading(submitBtn, false);
    }
}

async function handleRegister(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);
    
    const userData = {
        username: formData.get('username'),
        email: formData.get('email'),
        password: formData.get('password'),
        password_confirmation: formData.get('password_confirmation'),
        full_name: formData.get('full_name'),
        phone: formData.get('phone'),
        date_of_birth: formData.get('date_of_birth')
    };

    try {
        setLoading(submitBtn, true);
        clearMessages();

        const response = await authAPI.register(userData);
        
        if (response.success) {
            showMessage('Registration successful! Please check your email for verification.', 'success');
            setTimeout(() => {
                window.location.href = '/login';
            }, 2000);
        }
    } catch (error) {
        showMessage(error.message, 'error');
    } finally {
        setLoading(submitBtn, false);
    }
}

function setLoading(button, loading) {
    if (loading) {
        button.disabled = true;
        button.classList.add('loading');
        button.textContent = 'Loading...';
    } else {
        button.disabled = false;
        button.classList.remove('loading');
        button.textContent = button.getAttribute('data-original-text') || 'Submit';
    }
}

function showMessage(message, type) {
    clearMessages();
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `${type}-message`;
    messageDiv.textContent = message;
    
    const form = document.querySelector('.auth-form');
    form.insertBefore(messageDiv, form.firstChild);
}

function clearMessages() {
    const messages = document.querySelectorAll('.error-message, .success-message');
    messages.forEach(msg => msg.remove());
}

// Password confirmation validation
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    
    if (password && passwordConfirmation) {
        function validatePassword() {
            if (password.value !== passwordConfirmation.value) {
                passwordConfirmation.setCustomValidity('Passwords do not match');
            } else {
                passwordConfirmation.setCustomValidity('');
            }
        }
        
        password.addEventListener('change', validatePassword);
        passwordConfirmation.addEventListener('keyup', validatePassword);
    }
});
