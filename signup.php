<?php
include("../config/db.php");
$success = false;
$error = false;

if(isset($_POST['signup'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);

    $allowed_domain = "muet.edu.pk";
    $email_parts = explode("@", $email);
    
    // Validate phone number (Pakistan format)
    $phone_pattern = "/^03[0-9]{9}$/"; // Pakistani mobile number format: 03XXXXXXXXX
    if(!preg_match($phone_pattern, $phone)){
        $error = "Invalid phone number! Must be 11 digits starting with 03 (e.g., 03001234567)";
    } elseif(count($email_parts) != 2 || $email_parts[1] != $allowed_domain){
        $error = "Only university email (@muet.edu.pk) allowed!";
    } else {
        // Check if email or student_id already exists
        $check = $conn->prepare("SELECT * FROM students WHERE email = ? OR student_id = ?");
        $check->bind_param("ss", $email, $student_id);
        $check->execute();
        $result = $check->get_result();
        
        if($result->num_rows > 0){
            $error = "Email or Student ID already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO students (name, email, phone, password, student_id, department) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $phone, $password, $student_id, $department);
            
            if($stmt->execute()){
                $success = "Signup successful! Redirecting to login...";
            } else {
                $error = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Signup - JurisGuide</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.05" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-repeat: no-repeat;
            background-position: bottom;
            background-size: cover;
            opacity: 0.3;
            pointer-events: none;
        }
        
        /* Signup Card */
        .signup-card {
            background: white;
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            padding: 45px 40px;
            width: 580px;
            max-width: 100%;
            position: relative;
            z-index: 1;
            transition: transform 0.3s ease;
        }
        
        .signup-card:hover {
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
        
        /* Form Groups */
        .form-group {
            margin-bottom: 20px;
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
        
        .form-group label .optional {
            color: #999;
            font-weight: normal;
            font-size: 11px;
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
        
        select.form-control {
            appearance: none;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23667eea' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: calc(100% - 16px) center;
            background-size: 14px;
            padding-right: 45px;
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
        
        /* Row for two columns */
        .row-two {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        /* Signup Button */
        .btn-signup {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102,126,234,0.3);
        }
        
        .btn-signup i {
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
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
        
        .links .separator {
            color: #ddd;
            margin: 0 10px;
        }
        
        /* Domain Info */
        .domain-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 12px 15px;
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border: 1px solid #e9ecef;
        }
        
        .domain-info i {
            color: #d4af37;
            margin-right: 6px;
        }
        
        .domain-info strong {
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
        @media (max-width: 600px) {
            .signup-card {
                padding: 35px 25px;
            }
            
            .row-two {
                grid-template-columns: 1fr;
                gap: 20px;
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
    <div class="signup-card">
        <!-- Logo Section - Consistent Branding -->
        <div class="logo-section">
            <a href="../index.php" class="logo-wrapper">
                <div class="logo-icon-square">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="logo-title">Juris<span>Guide</span></div>
                <div class="logo-tagline">Justice at your fingertips</div>
            </a>
        </div>
        
        <!-- Form Title -->
        <div class="form-title">
            <h3>Create Account</h3>
            <p>Join JurisGuide to report and track complaints</p>
        </div>
        
        <?php if($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error; ?></span>
        </div>
        <?php endif; ?>
        
        <?php if($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $success; ?></span>
        </div>
        <script>
            setTimeout(function(){
                window.location.href = 'login.php';
            }, 2000);
        </script>
        <?php endif; ?>

        <form method="POST">
            <!-- Full Name -->
            <div class="form-group">
                <label><i class="fas fa-user"></i> Full Name</label>
                <div class="input-container">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
                </div>
            </div>
            
            <!-- Phone Number -->
            <div class="form-group">
                <label><i class="fas fa-phone"></i> Phone Number</label>
                <div class="input-container">
                    <i class="fas fa-phone input-icon"></i>
                    <input type="tel" name="phone" class="form-control" placeholder="03XXXXXXXXX (e.g., 03001234567)" required>
                </div>
                <small class="text-muted" style="display: block; margin-top: 5px; font-size: 11px;">
                    <i class="fas fa-info-circle"></i> Enter 11-digit Pakistani mobile number starting with 03
                </small>
            </div>
            
            <!-- Two Column Layout for Student ID and Department -->
            <div class="row-two">
                <!-- Student ID -->
                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> Student ID</label>
                    <div class="input-container">
                        <i class="fas fa-id-card input-icon"></i>
                        <input type="text" name="student_id" class="form-control" placeholder="e.g., 21CS101" required>
                    </div>
                </div>
                
                <!-- Department -->
                <div class="form-group">
                    <label><i class="fas fa-building"></i> Department</label>
                    <div class="input-container">
                        <i class="fas fa-building input-icon"></i>
                        <select name="department" class="form-control" required>
                            <option value="" disabled selected>Select Department</option>
                            <option>Computer Systems Engineering</option>
                            <option>Software Engineering</option>
                            <option>Electrical Engineering</option>
                            <option>Mechanical Engineering</option>
                            <option>Civil Engineering</option>
                            <option>Electronics Engineering</option>
                            <option>Telecommunication Engineering</option>
                            <option>Biomedical Engineering</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- University Email -->
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> University Email</label>
                <div class="input-container">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" name="email" class="form-control" placeholder="your.name@muet.edu.pk" required>
                </div>
                <small class="text-muted" style="display: block; margin-top: 5px; font-size: 11px;">
                    <i class="fas fa-info-circle"></i> Only @muet.edu.pk emails are allowed
                </small>
            </div>
            
            <!-- Password -->
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <div class="input-container">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Create a strong password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
                <small class="text-muted" style="display: block; margin-top: 5px; font-size: 11px;">
                    <i class="fas fa-shield-alt"></i> Minimum 8 characters recommended
                </small>
            </div>
            
            <button type="submit" name="signup" class="btn-signup">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>
        
        <div class="links">
            <a href="login.php">Already have an account? Login</a>
            <span class="separator">|</span>
            <a href="../index.php">Back to Home</a>
        </div>
        
        <div class="domain-info">
            <i class="fas fa-info-circle"></i> 
            Registration requires: <strong>@muet.edu.pk email</strong> | <strong>Valid Student ID</strong> | <strong>Valid Phone Number</strong>
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