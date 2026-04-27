<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}

// Get stats - Total, Pending, Closed
$total = $conn->query("SELECT COUNT(*) as total FROM complaints")->fetch_assoc()['total'] ?? 0;
$pending = $conn->query("SELECT COUNT(*) as total FROM complaints WHERE status='Pending'")->fetch_assoc()['total'] ?? 0;
$closed = $conn->query("SELECT COUNT(*) as total FROM complaints WHERE status='Closed'")->fetch_assoc()['total'] ?? 0;

// Get recent 5 complaints for dashboard preview
$recent_complaints = $conn->query("
    SELECT c.*, s.name, s.department, s.student_id as roll_no 
    FROM complaints c 
    JOIN students s ON c.student_id=s.id 
    ORDER BY c.created_at DESC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - JurisGuide</title>
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
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 30px 25px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .stat-card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 150px;
            height: 150px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: rotate(45deg);
        }
        
        .stat-card .stat-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .stat-card .stat-title {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .stat-card .stat-number {
            font-size: 42px;
            font-weight: 800;
            margin: 0;
            line-height: 1;
        }
        
        .stat-card.total { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stat-card.pending { 
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
        }
        
        .stat-card.closed { 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        /* Recent Complaints Container */
        .recent-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 25px;
        }
        
        .recent-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .recent-header h4 {
            color: #333;
            font-weight: 600;
            margin: 0;
            font-size: 18px;
        }
        
        .recent-header h4 i {
            color: #667eea;
            margin-right: 10px;
        }
        
        .view-all-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .view-all-link:hover {
            background: rgba(102,126,234,0.1);
            transform: translateX(3px);
        }
        
        /* Recent Table */
        .table-responsive {
            overflow-x: auto;
        }
        
        .recent-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .recent-table th {
            text-align: left;
            padding: 15px 12px;
            background: #f8f9fa;
            color: #333;
            font-weight: 600;
            font-size: 14px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .recent-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            color: #666;
            vertical-align: middle;
        }
        
        .recent-table tr:hover td {
            background: #f8f9fa;
        }
        
        /* Badges */
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
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 64px;
            color: #ccc;
            margin-bottom: 20px;
        }
        
        .empty-state h5 {
            color: #333;
            margin-bottom: 10px;
        }
        
        /* Footer */
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
                padding: 20px;
            }
            .header {
                flex-direction: column;
                text-align: center;
            }
            .header-left {
                flex-direction: column;
            }
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .stat-card {
                padding: 25px;
            }
            .stat-card .stat-number {
                font-size: 36px;
            }
            .recent-table th, .recent-table td {
                padding: 8px;
                font-size: 12px;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
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
            <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="all_complaints.php"><i class="fas fa-list"></i> All Complaints</a>
            <!-- <a href="#"><i class="fas fa-users"></i> Students</a> -->
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header with Toggle Button -->
        <div class="header">
            <div class="header-left">
                <button class="toggle-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
                    <p class="text-muted mb-0">Welcome back, <strong>Admin</strong></p>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <span class="date-badge me-3">
                    <i class="fas fa-calendar-alt me-1"></i> <?php echo date('l, F j, Y'); ?>
                </span>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-title">Total Complaints</div>
                <div class="stat-number"><?php echo $total; ?></div>
            </div>
            <div class="stat-card pending">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-title">Pending</div>
                <div class="stat-number"><?php echo $pending; ?></div>
            </div>
            <div class="stat-card closed">
                <div class="stat-icon">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="stat-title">Closed</div>
                <div class="stat-number"><?php echo $closed; ?></div>
            </div>
        </div>

        <!-- Recent Complaints -->
        <div class="recent-container">
            <div class="recent-header">
                <h4><i class="fas fa-history"></i> Recent Complaints</h4>
                <a href="all_complaints.php" class="view-all-link">
                    View All <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>

            <?php if($recent_complaints->num_rows == 0): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h5>No complaints yet</h5>
                <p class="text-muted">There are no complaints in the system.</p>
            </div>
            <?php else: ?>

            <div class="table-responsive">
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Category</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $recent_complaints->fetch_assoc()){ 
                            $status = $row['status'];
                            $badge_class = "";
                            if($status == "Pending") $badge_class = "warning";
                            elseif($status == "In Progress") $badge_class = "primary";
                            elseif($status == "Resolved") $badge_class = "success";
                            elseif($status == "Closed") $badge_class = "secondary";
                            elseif($status == "Pending Review") $badge_class = "info";
                        ?>
                        <tr>
                            <td><strong>#<?php echo $row['id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($row['category']); ?></span></td>
                            <td><?php echo htmlspecialchars(substr($row['title'], 0, 35)) . (strlen($row['title']) > 35 ? '...' : ''); ?></td>
                            <td><span class="badge bg-<?php echo $badge_class; ?>"><?php echo $status; ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
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
    </script>
</body>
</html>