<?php
session_start();
include("../config/db.php");
if(!isset($_SESSION['student_id'])){ 
    header("Location: login.php"); 
    exit; 
}

$student_id = $_SESSION['student_id'];

// Handle delete request
if(isset($_GET['delete']) && isset($_GET['id'])){
    $complaint_id = $_GET['id'];
    
    $check = $conn->query("SELECT * FROM complaints WHERE id='$complaint_id' AND student_id='$student_id' AND status='Pending'");
    
    if($check->num_rows > 0){
        $conn->query("DELETE FROM complaints WHERE id='$complaint_id'");
        $_SESSION['success'] = "Complaint deleted successfully!";
    } else {
        $_SESSION['error'] = "You can only delete pending complaints!";
    }
    header("Location: view_complaints.php");
    exit;
}

// Handle rating submission
if(isset($_POST['rate_complaint'])){
    $complaint_id = intval($_POST['complaint_id']);
    $rating = intval($_POST['rating']);
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    $mark_solved = isset($_POST['mark_solved']) ? 1 : 0;
    
    if($rating >= 1 && $rating <= 5){
        if($mark_solved == 1){
            $stmt = $conn->prepare("UPDATE complaints SET rating=?, feedback=?, resolved_by_student=1, resolved_date=NOW(), status='Closed' WHERE id=? AND student_id=?");
            $stmt->bind_param("isii", $rating, $feedback, $complaint_id, $student_id);
            $_SESSION['success'] = "Thank you for your feedback! Complaint marked as solved and closed.";
        } else {
            $stmt = $conn->prepare("UPDATE complaints SET rating=?, feedback=?, status='Pending Review' WHERE id=? AND student_id=?");
            $stmt->bind_param("isii", $rating, $feedback, $complaint_id, $student_id);
            $_SESSION['info'] = "Thank you for your feedback. We'll review this complaint again.";
        }
        
        if($stmt->execute()){
            $stmt->close();
        } else {
            $_SESSION['error'] = "Error submitting feedback: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "Please select a rating between 1-5 stars.";
    }
    header("Location: view_complaints.php");
    exit;
}

$result = $conn->query("SELECT * FROM complaints WHERE student_id='$student_id' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Complaints - JurisGuide</title>
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
        }
        
        .close-btn:hover {
            color: #dc3545;
        }
        
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
        
        .main-content {
            margin-left: 280px;
            padding: 30px;
            transition: all 0.3s ease;
        }
        .main-content.expanded {
            margin-left: 0;
        }
        
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
        
        /* Stats Cards - Total > Pending > In Progress > Resolved > Closed */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 22px;
        }
        
        .stat-icon.total { background: rgba(102,126,234,0.1); color: #667eea; }
        .stat-icon.pending { background: rgba(251,191,36,0.1); color: #fbbf24; }
        .stat-icon.progress { background: rgba(59,130,246,0.1); color: #3b82f6; }
        .stat-icon.resolved { background: rgba(16,185,129,0.1); color: #10b981; }
        .stat-icon.closed { background: rgba(107,114,128,0.1); color: #6b7280; }
        
        .stat-info h6 { margin: 0; color: #666; font-size: 13px; }
        .stat-info h3 { margin: 5px 0 0; font-weight: 700; color: #333; font-size: 28px; }
        .stat-info small { font-size: 10px; color: #999; display: block; margin-top: 3px; }
        
        .complaints-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }
        
        /* Complaint Card with TOP BORDERS */
        .complaint-card {
            background: white;
            border-radius: 18px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 25px;
            transition: all 0.3s;
            border: 1px solid #e9ecef;
            position: relative;
        }
        
        .complaint-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        /* COLORED TOP BORDERS FOR EACH STATUS */
        .complaint-card.pending {
            border-top: 5px solid #fbbf24;
        }
        
        .complaint-card.progress {
            border-top: 5px solid #3b82f6;
        }
        
        .complaint-card.resolved {
            border-top: 5px solid #10b981;
        }
        
        .complaint-card.closed {
            border-top: 5px solid #6b7280;
        }
        
        /* Fix - Make all cards same height */
        .complaint-card {
            min-height: 420px;
            display: flex;
            flex-direction: column;
        }
        
        .complaint-card .complaint-desc {
            flex: 1;
        }
        
        .complaint-card .complaint-footer {
            margin-top: auto;
        }
        
        .complaint-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .complaint-category {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge.bg-warning { background: #fff3cd !important; color: #856404; }
        .badge.bg-primary { background: #cce5ff !important; color: #004085; }
        .badge.bg-success { background: #d4edda !important; color: #155724; }
        .badge.bg-secondary { background: #e9ecef !important; color: #6c757d; }
        
        .complaint-title {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
        }
        
        .complaint-desc {
            color: #555;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
            background: #f9fafb;
            padding: 15px;
            border-radius: 12px;
        }
        
        .rating-display {
            display: flex;
            gap: 4px;
            align-items: center;
            margin: 12px 0;
        }
        
        .rating-display i.filled { color: #ffc107; }
        .rating-display i.empty { color: #ddd; }
        
        .feedback-text {
            font-size: 13px;
            padding: 12px;
            background: #f0fdf4;
            border-radius: 10px;
            margin: 12px 0;
        }
        
        .complaint-meta {
            display: flex;
            gap: 20px;
            color: #888;
            font-size: 13px;
            margin: 15px 0;
            padding: 12px 0;
            border-top: 1px solid #eef2f6;
            border-bottom: 1px solid #eef2f6;
            flex-wrap: wrap;
        }
        
        .complaint-meta i {
            margin-right: 5px;
            color: #667eea;
        }
        
        .complaint-footer {
            display: flex;
            justify-content: flex-end;
            padding-top: 15px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-view, .btn-rate {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .btn-view:hover, .btn-rate:hover {
            background: #667eea;
            color: white;
        }
        
        .btn-delete {
            background: transparent;
            color: #dc3545;
            border: 2px solid #dc3545;
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
        }
        
        .btn-delete:hover {
            background: #dc3545;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 18px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .btn-home {
            background: #f8f9fa;
            color: #333;
            padding: 12px 25px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-block;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 30px;
            color: #999;
            font-size: 13px;
        }
        
        @media (max-width: 1024px) {
            .stats-row {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 260px;
            }
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
            .header {
                flex-direction: column;
                text-align: center;
            }
            .header-left {
                flex-direction: column;
            }
            .action-buttons {
                flex-wrap: wrap;
            }
        }
        
        @media (max-width: 480px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
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
            <a href="submit_complaint.php"><i class="fas fa-plus-circle"></i> Submit Complaint</a>
            <a href="view_complaints.php" class="active"><i class="fas fa-list"></i> My Complaints</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <div class="header">
            <div class="header-left">
                <button class="toggle-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h2><i class="fas fa-list-alt"></i> My Complaints</h2>
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

        <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if(isset($_SESSION['info'])): ?>
        <div class="alert alert-info"><?php echo $_SESSION['info']; unset($_SESSION['info']); ?></div>
        <?php endif; ?>

        <?php if($result->num_rows == 0): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h4>No complaints yet</h4>
            <a href="submit_complaint.php" class="btn-view">Submit Your First Complaint</a>
        </div>
        <?php else: ?>

        <?php 
        $total_count = $result->num_rows;
        $pending_count = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE student_id='$student_id' AND status='Pending'")->fetch_assoc()['count'];
        $progress_count = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE student_id='$student_id' AND status='In Progress'")->fetch_assoc()['count'];
        $resolved_count = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE student_id='$student_id' AND status='Resolved'")->fetch_assoc()['count'];
        $closed_count = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE student_id='$student_id' AND status='Closed'")->fetch_assoc()['count'];
        ?>
        
        <!-- Stats Cards: Total > Pending > In Progress > Resolved > Closed -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon total"><i class="fas fa-chart-line"></i></div>
                <div class="stat-info">
                    <h6>Total</h6>
                    <h3><?php echo $total_count; ?></h3>
                    <small>All complaints</small>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <h6>Pending</h6>
                    <h3><?php echo $pending_count; ?></h3>
                    <small>Awaiting action</small>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon progress"><i class="fas fa-spinner"></i></div>
                <div class="stat-info">
                    <h6>In Progress</h6>
                    <h3><?php echo $progress_count; ?></h3>
                    <small>Being worked on</small>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon resolved"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <h6>Resolved</h6>
                    <h3><?php echo $resolved_count; ?></h3>
                    <small>Waiting for feedback</small>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon closed"><i class="fas fa-check-double"></i></div>
                <div class="stat-info">
                    <h6>Closed</h6>
                    <h3><?php echo $closed_count; ?></h3>
                    <small>Completed</small>
                </div>
            </div>
        </div>

        <div class="complaints-grid">
            <?php while($row = $result->fetch_assoc()){ 
                $status = $row['status'];
                $badge_class = "secondary";
                $icon = "fa-clock";
                $card_class = "";
                if($status == "Pending") { 
                    $badge_class = "warning";
                    $icon = "fa-hourglass-half";
                    $card_class = "pending";
                } elseif($status == "In Progress") { 
                    $badge_class = "primary";
                    $icon = "fa-spinner";
                    $card_class = "progress";
                } elseif($status == "Resolved") { 
                    $badge_class = "success";
                    $icon = "fa-check-circle";
                    $card_class = "resolved";
                } elseif($status == "Closed") { 
                    $badge_class = "secondary";
                    $icon = "fa-check-double";
                    $card_class = "closed";
                }
            ?>
            <div class="complaint-card <?php echo $card_class; ?>">
                <div class="complaint-header">
                    <span class="complaint-category"><i class="fas <?php echo $icon; ?> me-2"></i><?php echo htmlspecialchars($row['category']); ?></span>
                    <span class="badge bg-<?php echo $badge_class; ?>"><?php echo $status; ?></span>
                </div>
                <h5 class="complaint-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                
                <div class="complaint-desc">
                    <?php echo nl2br(htmlspecialchars($row['description'])); ?>
                </div>
                
                <?php if($row['rating']): ?>
                <div class="rating-display">
                    <strong>Your Rating:</strong>
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?php echo $i <= $row['rating'] ? 'filled' : 'empty'; ?>"></i>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
                
                <?php if($row['feedback']): ?>
                <div class="feedback-text">
                    <i class="fas fa-comment me-2"></i> <?php echo htmlspecialchars($row['feedback']); ?>
                </div>
                <?php endif; ?>
                
                <div class="complaint-meta">
                    <?php if(!empty($row['location'])){ ?>
                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['location']); ?></span>
                    <?php } ?>
                    <?php if(!empty($row['date_of_incident'])){ ?>
                    <span><i class="fas fa-calendar"></i> Incident: <?php echo date('M d, Y', strtotime($row['date_of_incident'])); ?></span>
                    <?php } ?>
                    <span><i class="fas fa-clock"></i> Submitted: <?php echo date('M d, Y', strtotime($row['created_at'])); ?></span>
                </div>
                
                <!-- COMPLAINT FOOTER WITH ACTION BUTTONS -->
                <div class="complaint-footer">
                    <div class="action-buttons">
                        <?php if($status == "Pending"): ?>
                        <a href="?delete=1&id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Delete this complaint?')">
                            <i class="fas fa-trash-alt me-1"></i>Delete
                        </a>
                        <?php endif; ?>
                        
                        <?php if($status == "Resolved" && !$row['rating']): ?>
                        <button class="btn-rate" onclick="openRatingModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['title']); ?>')">
                            <i class="fas fa-star me-1"></i>Rate & Mark Solved
                        </button>
                        <?php endif; ?>
                        
                        <!-- View button - Shows for EVERY complaint regardless of status -->
                        <button class="btn-view" onclick="viewComplaint(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                            <i class="fas fa-eye me-1"></i>View Details
                        </button>
                    </div>
                </div>
                <!-- END COMPLAINT FOOTER -->
                
            </div>
            <?php } ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="../index.php" class="btn-home">Back to Home Page</a>
        </div>
        <?php endif; ?>
        
        <div class="footer-text">
            <p>© 2024 JurisGuide - University Complaint Management System</p>
        </div>
    </div>

    <!-- Rating Modal -->
    <div class="modal fade" id="ratingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title">Rate Your Complaint Resolution</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <p><strong id="complaintTitle"></strong></p>
                            <p>How satisfied are you with the resolution?</p>
                            <div class="rating-stars" id="modalStars">
                                <i class="fas fa-star star" data-rating="1"></i>
                                <i class="fas fa-star star" data-rating="2"></i>
                                <i class="fas fa-star star" data-rating="3"></i>
                                <i class="fas fa-star star" data-rating="4"></i>
                                <i class="fas fa-star star" data-rating="5"></i>
                            </div>
                            <input type="hidden" name="rating" id="selectedRating" required>
                            <input type="hidden" name="complaint_id" id="complaintId">
                            
                            <div class="mt-3">
                                <label class="form-label">Your Feedback (Optional)</label>
                                <textarea name="feedback" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="mark_solved" id="markSolved" value="1" checked>
                                <label class="form-check-label" for="markSolved">✅ I'm satisfied - Mark this complaint as SOLVED</label>
                            </div>
                            
                            <div class="alert alert-info mt-3 small">
                                If you're NOT satisfied, uncheck the box above.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="rate_complaint" class="btn btn-primary">Submit Feedback</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="complaintModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complaint Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="complaintModalBody">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
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
        
        function openRatingModal(complaintId, title) {
            document.getElementById('complaintId').value = complaintId;
            document.getElementById('complaintTitle').innerHTML = title;
            document.getElementById('selectedRating').value = '';
            
            document.querySelectorAll('#modalStars .star').forEach(star => {
                star.classList.remove('active');
                star.style.color = '#ddd';
            });
            
            new bootstrap.Modal(document.getElementById('ratingModal')).show();
        }
        
        document.querySelectorAll('#modalStars .star').forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.getAttribute('data-rating');
                document.getElementById('selectedRating').value = rating;
                
                document.querySelectorAll('#modalStars .star').forEach(s => {
                    if(parseInt(s.getAttribute('data-rating')) <= parseInt(rating)) {
                        s.classList.add('active');
                        s.style.color = '#ffc107';
                    } else {
                        s.classList.remove('active');
                        s.style.color = '#ddd';
                    }
                });
            });
        });
        
        function viewComplaint(complaint) {
            let modalBody = document.getElementById('complaintModalBody');
            let statusClass = 'secondary';
            
            if(complaint.status == 'Pending') {
                statusClass = 'warning';
            } else if(complaint.status == 'In Progress') {
                statusClass = 'primary';
            } else if(complaint.status == 'Resolved') {
                statusClass = 'success';
            }
            
            let ratingHtml = '';
            if(complaint.rating) {
                let stars = '';
                for(let i = 1; i <= 5; i++) {
                    if(i <= complaint.rating) {
                        stars += '<i class="fas fa-star text-warning"></i> ';
                    } else {
                        stars += '<i class="fas fa-star text-muted"></i> ';
                    }
                }
                ratingHtml = '<div class="mt-3"><h6>Your Rating</h6><div>' + stars + '</div></div>';
            }
            
            let feedbackHtml = '';
            if(complaint.feedback) {
                feedbackHtml = '<div class="mt-3"><h6>Your Feedback</h6><p class="text-muted">' + complaint.feedback + '</p></div>';
            }
            
            modalBody.innerHTML = `
                <div class="text-center mb-4">
                    <span class="badge bg-${statusClass} p-3">${complaint.status}</span>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Category</h6>
                        <p class="text-muted">${complaint.category}</p>
                        <h6>Title</h6>
                        <p class="text-muted">${complaint.title}</p>
                        <h6>Location</h6>
                        <p class="text-muted">${complaint.location || 'Not specified'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Submitted On</h6>
                        <p class="text-muted">${new Date(complaint.created_at).toLocaleString()}</p>
                        <h6>Incident Date</h6>
                        <p class="text-muted">${complaint.date_of_incident || 'Not specified'}</p>
                        ${complaint.resolved_date ? `<h6>Resolved On</h6><p class="text-muted">${new Date(complaint.resolved_date).toLocaleString()}</p>` : ''}
                    </div>
                    <div class="col-12 mt-3">
                        <h6>Description</h6>
                        <div class="p-3 bg-light rounded">${complaint.description}</div>
                        ${ratingHtml}
                        ${feedbackHtml}
                    </div>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('complaintModal')).show();
        }
    </script>
</body>
</html>