<?php
session_start();
if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    if($username=="admin" && $password=="admin123"){
        $_SESSION['admin'] = $username;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - JurisGuide</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }
        
        /* Background Pattern */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.03" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-repeat: no-repeat;
            background-position: bottom;
            background-size: cover;
            opacity: 0.5;
            pointer-events: none;
        }
        
        /* Login Card */
        .login-card {
            background: white;
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            padding: 45px 40px;
            width: 480px;
            max-width: 100%;
            position: relative;
            z-index: 1;
            transition: transform 0.3s ease;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
        }
        
        /* Logo Section */
        .logo-section {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .logo-wrapper {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
        }
        
        .logo-icon-square {
            width: 75px;
            height: 75px;
            background: #0a2540;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            box-shadow: 0 10px 20px rgba(10,37,64,0.15);
            transition: all 0.3s ease;
        }
        
        .logo-wrapper:hover .logo-icon-square {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(10,37,64,0.2);
        }
        
        .logo-icon-square i {
            font-size: 38px;
            color: #d4af37;
        }
        
        .logo-title {
            font-size: 26px;
            font-weight: 800;
            color: #1a2a3a;
            letter-spacing: -0.5px;
        }
        
        .logo-title span {
            color: #d4af37;
        }
        
        .logo-tagline {
            font-size: 11px;
            color: #888;
            margin-top: 4px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        /* Form Title */
        .form-title {
            text-align: center;
            margin-bottom: 28px;
        }
        
        .form-title h3 {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 6px;
        }
        
        .form-title p {
            font-size: 14px;
            color: #666;
            margin: 0;
        }
        
        .form-title .admin-badge {
            display: inline-block;
            background: #f0f2f5;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            color: #667eea;
            margin-top: 8px;
        }
        
        /* Form Groups */
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group label i {
            color: #667eea;
            margin-right: 8px;
        }
        
        /* Input Container */
        .input-container {
            position: relative;
            width: 100%;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 16px;
            z-index: 2;
            pointer-events: none;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px 14px 45px;
            border: 2px solid #e9ecef;
            border-radius: 14px;
            font-size: 15px;
            transition: all 0.3s;
            background: #f8fafc;
        }
        
        .form-control:focus {
            border-color: #667eea;
            outline: none;
            background: white;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
        }
        
        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #9ca3af;
            font-size: 16px;
            z-index: 2;
            background: transparent;
            border: none;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .password-toggle:hover {
            color: #667eea;
        }
        
        /* Login Button */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(26,26,46,0.3);
        }
        
        .btn-login i {
            margin-right: 8px;
        }
        
        /* Alert */
        .alert {
            padding: 14px 18px;
            border-radius: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-danger {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert i {
            font-size: 18px;
        }
        
        /* Links */
        .links {
            text-align: center;
            margin-top: 20px;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .links a:hover {
            color: #5a67d8;
            text-decoration: underline;
        }
        
        /* Admin Info */
        .admin-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 12px 15px;
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border: 1px solid #e9ecef;
        }
        
        .admin-info i {
            color: #d4af37;
            margin-right: 6px;
        }
        
        .admin-info strong {
            color: #0a2540;
        }
        
        /* Footer */
        .footer-text {
            text-align: center;
            margin-top: 25px;
            font-size: 12px;
            color: #999;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 35px 25px;
            }
            
            .logo-icon-square {
                width: 65px;
                height: 65px;
            }
            
            .logo-icon-square i {
                font-size: 32px;
            }
            
            .logo-title {
                font-size: 22px;
            }
            
            .form-title h3 {
                font-size: 20px;
            }
            
            .form-control {
                padding: 12px 12px 12px 42px;
                font-size: 14px;
            }
            
            .input-icon {
                left: 14px;
                font-size: 14px;
            }
            
            .password-toggle {
                right: 14px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <!-- Logo Section - Consistent Branding -->
        <div class="logo-section">
            <a href="../index.php" class="logo-wrapper">
                <div class="logo-icon-square">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="logo-title">Juris<span>Guide</span></div>
                <div class="logo-tagline">Administrator Portal</div>
            </a>
        </div>
        
        <!-- Form Title -->
        <div class="form-title">
            <h3>Admin Login</h3>
            <p>Access the administrative dashboard</p>
            <span class="admin-badge"><i class="fas fa-shield-alt"></i> Secure Access</span>
        </div>
        
        <?php if(isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error; ?></span>
        </div>
        <?php endif; ?>

        <form method="POST">
            <!-- Username Field -->
            <div class="form-group">
                <label><i class="fas fa-user-shield"></i> Username</label>
                <div class="input-container">
                    <i class="fas fa-user-shield input-icon"></i>
                    <input type="text" name="username" class="form-control" placeholder="Enter admin username" required>
                </div>
            </div>
            
            <!-- Password Field with Eye Icon -->
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <div class="input-container">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter admin password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" name="login" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <div class="links">
            <a href="../index.php"><i class="fas fa-home"></i> Back to Home</a>
        </div>
        
        <div class="admin-info">
            <i class="fas fa-info-circle"></i> 
            <strong>Demo Credentials:</strong> Username: <strong>admin</strong> | Password: <strong>admin123</strong>
        </div>
        
        <div class="footer-text">
            <p><i class="fas fa-balance-scale me-1"></i> © 2024 JurisGuide - University Complaint Management System</p>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Auto hide alert after 5 seconds
        setTimeout(function() {
            const alert = document.querySelector('.alert');
            if(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    </script>
</body>
</html>