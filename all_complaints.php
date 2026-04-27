<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}

// Get stats for the cards
$pending = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status='Pending'")->fetch_assoc()['count'];
$in_progress = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status='In Progress'")->fetch_assoc()['count'];
$resolved = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status='Resolved'")->fetch_assoc()['count'];
$closed = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status='Closed'")->fetch_assoc()['count'];
$pending_review = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status='Pending Review'")->fetch_assoc()['count'];

// Get all complaints with student details (including phone)
$all_complaints = $conn->query("
    SELECT c.*, s.name, s.department, s.student_id as roll_no, s.phone 
    FROM complaints c 
    JOIN students s ON c.student_id=s.id 
    ORDER BY c.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>All Complaints - JurisGuide</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
        .header .date-badge {
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 10px;
            color: #666;
            font-size: 14px;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
            display: inline-block;
        }
        .logout-btn:hover {
            background: #c82333;
            color: white;
            transform: translateY(-2px);
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
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
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
        }
        .stat-icon.pending { background: rgba(251,191,36,0.1); color: #fbbf24; }
        .stat-icon.progress { background: rgba(59,130,246,0.1); color: #3b82f6; }
        .stat-icon.resolved { background: rgba(16,185,129,0.1); color: #10b981; }
        .stat-icon.closed { background: rgba(107,114,128,0.1); color: #6b7280; }
        .stat-icon.review { background: rgba(139,92,246,0.1); color: #8b5cf6; }
        .stat-info h6 { margin: 0; color: #666; font-size: 14px; }
        .stat-info h3 { margin: 5px 0 0; font-weight: 700; color: #333; }
        .stat-info small { font-size: 11px; color: #999; }
        
        /* Table Container */
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 25px;
        }
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .table-header h4 {
            color: #333;
            font-weight: 600;
            margin: 0;
        }
        .table-header h4 i {
            color: #667eea;
            margin-right: 10px;
        }
        .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .search-box input {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            width: 250px;
            transition: all 0.3s;
        }
        .search-box input:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        .search-box button {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .search-box button:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table thead th {
            background: #f8f9fa;
            padding: 15px;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
            white-space: nowrap;
        }
        table tbody td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            color: #666;
            vertical-align: middle;
        }
        table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge.bg-warning { background: #fff3cd !important; color: #856404; }
        .badge.bg-primary { background: #cce5ff !important; color: #004085; }
        .badge.bg-info { background: #d4edda !important; color: #155724; }
        .badge.bg-secondary { background: #e9ecef !important; color: #6c757d; }
        .badge.bg-purple { background: #e9d8fd !important; color: #6b46c1; }
        
        .action-btns {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-block;
        }
        .btn-pending { background: #fbbf24; color: white; }
        .btn-pending:hover { background: #f59e0b; transform: translateY(-2px); }
        .btn-progress { background: #3b82f6; color: white; }
        .btn-progress:hover { background: #2563eb; transform: translateY(-2px); }
        .btn-resolved { background: #10b981; color: white; }
        .btn-resolved:hover { background: #059669; transform: translateY(-2px); }
        .btn-closed { background: #6b7280; color: white; }
        .btn-closed:hover { background: #4b5563; transform: translateY(-2px); }
        .btn-view { background: #667eea; color: white; }
        .btn-view:hover { background: #5a67d8; transform: translateY(-2px); }
        
        .rating-display {
            display: flex;
            gap: 3px;
            align-items: center;
        }
        .rating-display i {
            font-size: 12px;
        }
        .rating-display i.filled {
            color: #ffc107;
        }
        .rating-display i.empty {
            color: #ddd;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        .empty-state i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 15px;
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
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .action-btns {
                flex-direction: column;
            }
            .btn-action {
                width: 100%;
                text-align: center;
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
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="all_complaints.php" class="active"><i class="fas fa-list"></i> All Complaints</a>
            <!-- <a href="#"><i class="fas fa-users"></i> Students</a> -->
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
                    <h2><i class="fas fa-list"></i> All Complaints</h2>
                    <p class="text-muted mb-0">Manage complaints: Pending → In Progress → Resolved → Closed</p>
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
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h6>Pending</h6>
                    <h3><?php echo $pending; ?></h3>
                    <small>Awaiting action</small>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon progress">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="stat-info">
                    <h6>In Progress</h6>
                    <h3><?php echo $in_progress; ?></h3>
                    <small>Being worked on</small>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon resolved">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h6>Resolved</h6>
                    <h3><?php echo $resolved; ?></h3>
                    <small>Waiting for feedback</small>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon closed">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="stat-info">
                    <h6>Closed</h6>
                    <h3><?php echo $closed; ?></h3>
                    <small>Completed</small>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon review">
                    <i class="fas fa-rotate-right"></i>
                </div>
                <div class="stat-info">
                    <h6>Pending Review</h6>
                    <h3><?php echo $pending_review; ?></h3>
                    <small>Needs attention</small>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h4><i class="fas fa-list"></i> Complaints List</h4>
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search complaints...">
                    <button onclick="searchTable()"><i class="fas fa-search"></i></button>
                </div>
            </div>

            <?php if($all_complaints->num_rows == 0): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h5>No complaints yet</h5>
                <p class="text-muted">There are no complaints in the system.</p>
            </div>
            <?php else: ?>

            <div class="table-responsive">
                <table id="complaintsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Phone</th>
                            <th>Roll No</th>
                            <th>Department</th>
                            <th>Category</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Rating</th>
                            <th>Actions</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $all_complaints->fetch_assoc()){ 
                            $status_class = '';
                            $status_icon = '';
                            if($row['status'] == 'Pending') {
                                $status_class = 'warning';
                                $status_icon = 'fa-clock';
                            } elseif($row['status'] == 'In Progress') {
                                $status_class = 'primary';
                                $status_icon = 'fa-spinner';
                            } elseif($row['status'] == 'Resolved') {
                                $status_class = 'info';
                                $status_icon = 'fa-check-circle';
                            } elseif($row['status'] == 'Closed') {
                                $status_class = 'secondary';
                                $status_icon = 'fa-check-double';
                            } elseif($row['status'] == 'Pending Review') {
                                $status_class = 'purple';
                                $status_icon = 'fa-rotate-right';
                            }
                        ?>
                        <tr>
                            <td><strong>#<?php echo $row['id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['roll_no']); ?></td>
                            <td><?php echo htmlspecialchars($row['department']); ?></td>
                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($row['category']); ?></span></td>
                            <td><?php echo htmlspecialchars(substr($row['title'], 0, 40)) . (strlen($row['title']) > 40 ? '...' : ''); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $status_class; ?>">
                                    <i class="fas <?php echo $status_icon; ?> me-1"></i> <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if($row['rating']): ?>
                                <div class="rating-display">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $row['rating'] ? 'filled' : 'empty'; ?>"></i>
                                    <?php endfor; ?>
                                    <?php if($row['feedback']): ?>
                                    <small class="text-muted d-block mt-1" title="<?php echo htmlspecialchars($row['feedback']); ?>">
                                        <i class="fas fa-comment"></i>
                                    </small>
                                    <?php endif; ?>
                                </div>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <?php if($row['status'] == 'Pending'): ?>
                                    <a href="update_status.php?id=<?php echo $row['id']; ?>&status=In%20Progress" class="btn-action btn-progress" onclick="return confirm('Move this complaint to In Progress?')">
                                        <i class="fas fa-play"></i> Start
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if($row['status'] == 'In Progress'): ?>
                                    <a href="update_status.php?id=<?php echo $row['id']; ?>&status=Resolved" class="btn-action btn-resolved" onclick="return confirm('Mark this complaint as Resolved?')">
                                        <i class="fas fa-check-circle"></i> Resolve
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if($row['status'] == 'Resolved'): ?>
                                    <a href="update_status.php?id=<?php echo $row['id']; ?>&status=Closed" class="btn-action btn-closed" onclick="return confirm('Close this complaint?')">
                                        <i class="fas fa-check-double"></i> Close
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if($row['status'] == 'Pending Review'): ?>
                                    <a href="update_status.php?id=<?php echo $row['id']; ?>&status=In%20Progress" class="btn-action btn-progress" onclick="return confirm('Review this complaint and move to In Progress?')">
                                        <i class="fas fa-rotate-right"></i> Review
                                    </a>
                                    <?php endif; ?>
                                    
                                    <button class="btn-action btn-view" onclick="viewDetails(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </div>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="footer-text">
            <p><i class="fas fa-balance-scale me-1"></i> © 2024 JurisGuide - University Complaint Management System</p>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complaint Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalBody">
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

        function viewDetails(complaint) {
            let modalBody = document.getElementById('modalBody');
            let statusClass = complaint.status == 'Pending' ? 'warning' : 
                             (complaint.status == 'In Progress' ? 'primary' : 
                             (complaint.status == 'Resolved' ? 'info' : 
                             (complaint.status == 'Pending Review' ? 'purple' : 'secondary')));
            
            let ratingHtml = '';
            if(complaint.rating) {
                ratingHtml = `
                <div class="mt-3">
                    <h6 class="fw-bold">Student Rating</h6>
                    <div class="rating-display">
                        ${Array(5).fill().map((_, i) => `<i class="fas fa-star ${i < complaint.rating ? 'filled' : 'empty'}"></i>`).join('')}
                    </div>
                    ${complaint.feedback ? `<p class="text-muted mt-2"><strong>Feedback:</strong> "${complaint.feedback}"</p>` : ''}
                </div>`;
            }
            
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Complaint ID:</strong> #${complaint.id}</p>
                        <p><strong>Student Name:</strong> ${complaint.name}</p>
                        <p><strong>Phone Number:</strong> ${complaint.phone || 'Not provided'}</p>
                        <p><strong>Roll No:</strong> ${complaint.roll_no}</p>
                        <p><strong>Department:</strong> ${complaint.department}</p>
                        <p><strong>Category:</strong> <span class="badge bg-primary">${complaint.category}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> <span class="badge bg-${statusClass}">${complaint.status}</span></p>
                        <p><strong>Location:</strong> ${complaint.location || 'Not specified'}</p>
                        <p><strong>Incident Date:</strong> ${complaint.date_of_incident || 'Not specified'}</p>
                        <p><strong>Submitted On:</strong> ${new Date(complaint.created_at).toLocaleString()}</p>
                        ${complaint.in_progress_date ? `<p><strong>In Progress Since:</strong> ${new Date(complaint.in_progress_date).toLocaleString()}</p>` : ''}
                        ${complaint.resolved_date ? `<p><strong>Resolved On:</strong> ${new Date(complaint.resolved_date).toLocaleString()}</p>` : ''}
                        ${complaint.closed_date ? `<p><strong>Closed On:</strong> ${new Date(complaint.closed_date).toLocaleString()}</p>` : ''}
                    </div>
                    <div class="col-12 mt-3">
                        <p><strong>Title:</strong> ${complaint.title}</p>
                        <p><strong>Description:</strong></p>
                        <div class="p-3 bg-light rounded">
                            ${complaint.description}
                        </div>
                        ${ratingHtml}
                    </div>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('detailsModal')).show();
        }

        function searchTable() {
            let input = document.getElementById('searchInput');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('complaintsTable');
            let rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                let row = rows[i];
                let text = row.textContent.toLowerCase();
                if (text.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }

        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                searchTable();
            }
        });
    </script>
</body>
</html>