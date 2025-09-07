<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Telegram Clone</title>
    @vite(['resources/css/app.css', 'resources/css/auth.css'])
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Welcome Back</h1>
                <p>Sign in to your account</p>
            </div>
            
            <form id="loginForm" class="auth-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="device_type">Device Type</label>
                    <select id="device_type" name="device_type" required>
                        <option value="web">Web Browser</option>
                        <option value="desktop">Desktop App</option>
                        <option value="mobile">Mobile App</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="device_name">Device Name</label>
                    <input type="text" id="device_name" name="device_name" placeholder="e.g., Chrome Browser">
                </div>
                
                <button type="submit" class="btn btn-primary">Sign In</button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="{{ route('register') }}">Sign up</a></p>
            </div>
        </div>
    </div>
    
    @vite(['resources/js/auth.js'])
</body>
</html>
