<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['student_id'])){
    header("Location: login.php");
    exit;
}

if(isset($_POST['submit'])){
    $student_id = $_SESSION['student_id'];
    $category = $_POST['category'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $location = !empty($_POST['location']) ? mysqli_real_escape_string($conn, $_POST['location']) : NULL;
    $date_of_incident = !empty($_POST['date_of_incident']) ? $_POST['date_of_incident'] : NULL;

    // Count words in description
    $word_count = str_word_count($description);
    if($word_count > 100){
        $error = "Description cannot exceed 100 words. Current word count: " . $word_count;
    } else {
        $stmt = $conn->prepare("INSERT INTO complaints 
        (student_id, category, title, description, location, date_of_incident, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'Pending')");

        $stmt->bind_param("isssss", $student_id, $category, $title, $description, $location, $date_of_incident);
        
        if($stmt->execute()){
            $_SESSION['success'] = "Complaint submitted successfully!";
            header("Location: view_complaints.php");
            exit;
        } else {
            $error = "Failed to submit complaint. Please try again.";
        }
        $stmt->close();
    }
}

// List of complaint categories
$categories = [
    'Any kind of Harassment',
    'Clash between students',
    'Hostel issues',
    'Misbehave',
    'Attendance issues',
    'Accident',
    'Complaint against cafeterias'
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Submit Complaint - JurisGuide</title>
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
            background: #f4f7fc;
            overflow-x: hidden;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: white;
            min-height: 100vh;
            box-shadow: 2px 0 20px rgba(0,0,0,0.05);
            position: fixed;
            left: 0;
            top: 0;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        .sidebar.collapsed {
            left: -280px;
        }
        
        /* Sidebar Header with Logo */
        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid #eef2f6;
            margin-bottom: 20px;
            position: relative;
        }
        
        .logo-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
        }
        
        .logo-icon-square {
            width: 65px;
            height: 65px;
            background: #0a2540;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            box-shadow: 0 4px 12px rgba(10,37,64,0.15);
            transition: transform 0.2s ease;
        }
        
        .logo-wrapper:hover .logo-icon-square {
            transform: translateY(-3px);
        }
        
        .logo-icon-square i {
            font-size: 32px;
            color: #d4af37;
        }
        
        .logo-title {
            font-size: 20px;
            font-weight: 700;
            color: #1a2a3a;
            letter-spacing: -0.3px;
        }
        
        .logo-title span {
            color: #d4af37;
        }
        
        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #999;
            transition: color 0.2s;
        }
        
        .close-btn:hover {
            color: #dc3545;
        }
        
        /* Sidebar Menu */
        .sidebar-menu {
            padding: 0 15px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 18px;
            margin-bottom: 8px;
            color: #5a6e8a;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.2s;
            font-weight: 500;
        }
        
        .sidebar-menu a i {
            width: 24px;
            font-size: 18px;
            margin-right: 12px;
            color: #8a9bb5;
        }
        
        .sidebar-menu a:hover {
            background: #f0f4f9;
            color: #2c3e50;
        }
        
        .sidebar-menu a.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .sidebar-menu a.active i {
            color: white;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 30px;
            transition: all 0.3s ease;
        }
        .main-content.expanded {
            margin-left: 0;
        }
        
        /* Header */
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .toggle-btn {
            width: 45px;
            height: 45px;
            background: #f8f9fa;
            border: none;
            border-radius: 12px;
            color: #667eea;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            font-size: 20px;
        }
        
        .toggle-btn:hover {
            background: #667eea;
            color: white;
            transform: scale(1.05);
        }
        
        .header h2 {
            color: #333;
            font-weight: 600;
            margin: 0;
        }
        .header h2 i {
            color: #667eea;
            margin-right: 10px;
        }
        
        .welcome-text {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .date-badge {
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 12px;
            color: #666;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 10px 22px;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .logout-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        /* Form Card */
        .form-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            padding: 35px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group label i {
            color: #667eea;
            margin-right: 8px;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 18px;
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
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23667eea' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: calc(100% - 16px) center;
            background-size: 14px;
            padding-right: 45px;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 140px;
        }
        
        /* Word Counter Styles */
        .word-counter {
            font-size: 12px;
            margin-top: 8px;
            padding: 8px 12px;
            border-radius: 10px;
            display: inline-block;
            width: auto;
        }
        
        .word-counter.valid {
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }
        
        .word-counter.invalid {
            color: #721c24;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        
        .word-counter.warning {
            color: #856404;
            background: #fff3cd;
            border: 1px solid #ffeeba;
        }
        
        .word-counter i {
            margin-right: 6px;
        }
        
        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102,126,234,0.3);
        }
        
        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Alert */
        .alert {
            padding: 15px 20px;
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
        
        .footer-text {
            text-align: center;
            margin-top: 30px;
            color: #999;
            font-size: 13px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 260px;
            }
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            .header {
                flex-direction: column;
                text-align: center;
            }
            .header-left {
                flex-direction: column;
            }
            .form-card {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar with Logo -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="logo-wrapper">
                <div class="logo-icon-square">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="logo-title">Juris<span>Guide</span></div>
            </a>
            <button class="close-btn" onclick="toggleSidebar()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="submit_complaint.php" class="active"><i class="fas fa-plus-circle"></i> Submit Complaint</a>
            <a href="view_complaints.php"><i class="fas fa-list"></i> My Complaints</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="header">
            <div class="header-left">
                <button class="toggle-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h2><i class="fas fa-pen-alt"></i> Submit Complaint</h2>
                    <p class="welcome-text">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>! 👋</p>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <span class="date-badge me-3">
                    <i class="fas fa-calendar-alt"></i> <?php echo date('l, F j, Y'); ?>
                </span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <?php if(isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <!-- Complaint Form -->
        <div class="form-card">
            <form method="POST" id="complaintForm" onsubmit="return validateForm()">
                <!-- Category Dropdown -->
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Select Category <span class="text-danger">*</span></label>
                    <select name="category" class="form-control" required>
                        <option value="" disabled selected>-- Choose a category --</option>
                        <?php foreach($categories as $category): ?>
                        <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Complaint Title -->
                <div class="form-group">
                    <label><i class="fas fa-heading"></i> Complaint Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" 
                           placeholder="e.g., Issue with hostel food, Harassment incident, etc." required>
                </div>

                <!-- Description with Word Limit -->
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Description <span class="text-danger">*</span></label>
                    <textarea name="description" id="description" class="form-control" 
                              placeholder="Please provide complete details of the incident (Maximum 100 words)..." 
                              rows="5" required onkeyup="countWords()" onkeydown="countWords()"></textarea>
                    <div id="wordCounter" class="word-counter valid mt-2">
                        <i class="fas fa-check-circle"></i> 0/100 words - 100 words remaining
                    </div>
                    <small class="text-muted mt-1">
                        <i class="fas fa-info-circle"></i> Maximum 100 words allowed. Please be concise and provide only essential details.
                    </small>
                </div>

                <!-- Location (Optional) -->
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Location <span class="text-muted">(Optional)</span></label>
                    <input type="text" name="location" class="form-control" 
                           placeholder="e.g., Boys Hostel Block A, Main Cafeteria, etc.">
                </div>

                <!-- Date of Incident (Optional) -->
                <div class="form-group">
                    <label><i class="fas fa-calendar"></i> Date of Incident <span class="text-muted">(Optional)</span></label>
                    <input type="date" name="date_of_incident" class="form-control" 
                           max="<?php echo date('Y-m-d'); ?>">
                </div>

                <!-- Submit Button -->
                <button type="submit" name="submit" id="submitBtn" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit Complaint
                </button>
            </form>
        </div>
        
        <!-- Footer -->
        <div class="footer-text">
            <p><i class="fas fa-balance-scale me-1"></i> © 2024 JurisGuide - University Complaint Management System</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            const toggleBtn = document.querySelector('.toggle-btn i');
            if (sidebar.classList.contains('collapsed')) {
                toggleBtn.className = 'fas fa-arrow-right';
            } else {
                toggleBtn.className = 'fas fa-bars';
            }
        }
        
        function countWords() {
            const textarea = document.getElementById('description');
            const text = textarea.value.trim();
            
            // Count words (split by spaces, filter out empty strings)
            const words = text.length > 0 ? text.split(/\s+/).filter(function(word) { return word.length > 0; }) : [];
            const wordCount = words.length;
            const remaining = 100 - wordCount;
            
            const counterDiv = document.getElementById('wordCounter');
            const submitBtn = document.getElementById('submitBtn');
            
            if (wordCount > 100) {
                // Too many words - Disable submit
                counterDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${wordCount}/100 words - ${Math.abs(remaining)} words over limit!`;
                counterDiv.className = 'word-counter invalid';
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';
            } else if (wordCount >= 90) {
                // Approaching limit - Warning
                counterDiv.innerHTML = `<i class="fas fa-clock"></i> ${wordCount}/100 words - ${remaining} words remaining`;
                counterDiv.className = 'word-counter warning';
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
            } else {
                // Within limit - Good
                counterDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${wordCount}/100 words - ${remaining} words remaining`;
                counterDiv.className = 'word-counter valid';
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
            }
        }
        
        function validateForm() {
            const textarea = document.getElementById('description');
            const text = textarea.value.trim();
            const words = text.length > 0 ? text.split(/\s+/).filter(function(word) { return word.length > 0; }) : [];
            const wordCount = words.length;
            
            if (wordCount > 100) {
                alert('❌ Please limit your description to 100 words.\nCurrent word count: ' + wordCount + ' words');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>