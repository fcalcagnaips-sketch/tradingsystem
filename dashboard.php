<?php
require_once __DIR__ . '/includes/auth.php';

// Require login to access dashboard
requireLogin();

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Trading AI</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f8fa;
            font-size: 13px;
            font-weight: 400;
            color: #252f4a;
        }
        /* Sidebar Styles - Metronic 9 */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 265px;
            background: #1e1e2d;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            box-shadow: 0 0 28px 0 rgba(82, 63, 105, 0.05);
            transition: width 0.3s ease;
        }
        .sidebar.collapsed {
            width: 75px;
        }
        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
            padding: 0 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .sidebar.collapsed .sidebar-logo {
            justify-content: center;
            padding: 0 15px;
        }
        .sidebar-logo h3 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin: 0;
            white-space: nowrap;
            transition: opacity 0.2s;
        }
        .sidebar.collapsed .sidebar-logo h3 {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        .sidebar-toggle {
            background: transparent;
            border: none;
            color: #a1a5b7;
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sidebar-toggle:hover {
            color: #ffffff;
        }
        .sidebar.collapsed .sidebar-toggle {
            margin: 0 auto;
        }
        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            padding: 25px 0;
        }
        .menu-section {
            padding: 0 25px;
            margin-bottom: 10px;
        }
        .menu-section-title {
            color: #565674;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 0;
            margin-bottom: 5px;
            transition: opacity 0.2s;
        }
        .sidebar.collapsed .menu-section-title {
            opacity: 0;
            height: 0;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }
        .menu-item {
            display: flex;
            align-items: center;
            padding: 11px 15px;
            color: #a1a5b7;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.2s;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 2px;
            position: relative;
        }
        .menu-item:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #ffffff;
        }
        .menu-item.active {
            background: #1b1b28;
            color: #009ef7;
        }
        .menu-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 24px;
            background: #009ef7;
            border-radius: 0 3px 3px 0;
        }
        .menu-item i {
            font-size: 18px;
            margin-right: 12px;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }
        .sidebar.collapsed .menu-item {
            justify-content: center;
            padding: 11px 5px;
        }
        .sidebar.collapsed .menu-item span {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        .sidebar.collapsed .menu-item i {
            margin-right: 0;
        }
        .menu-item .menu-arrow {
            margin-left: auto;
            font-size: 11px;
            transition: transform 0.2s;
        }
        .menu-item.has-submenu.open .menu-arrow {
            transform: rotate(90deg);
        }
        .sidebar.collapsed .menu-arrow {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        .submenu {
            display: none;
            padding-left: 20px;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .submenu.open {
            display: block;
        }
        .submenu .menu-item {
            padding: 8px 15px;
            font-size: 12px;
        }
        .sidebar.collapsed .submenu {
            display: none !important;
        }
        /* Header Toolbar */
        .header-toolbar {
            position: fixed;
            top: 0;
            left: 265px;
            right: 0;
            height: 80px;
            background: #ffffff;
            border-bottom: 1px solid #eff2f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            z-index: 95;
            box-shadow: 0 0 28px 0 rgba(82, 63, 105, 0.05);
            transition: left 0.3s ease;
        }
        .sidebar.collapsed ~ .header-toolbar {
            left: 75px;
        }
        .page-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .page-title h1 {
            font-size: 18px;
            font-weight: 600;
            color: #181c32;
            margin: 0;
        }
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
            font-size: 12px;
        }
        .breadcrumb-item {
            color: #a1a5b7;
        }
        .breadcrumb-item.active {
            color: #181c32;
            font-weight: 500;
        }
        .breadcrumb-separator {
            color: #a1a5b7;
        }
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-panel {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 12px;
            border-radius: 6px;
            background: #f5f8fa;
            cursor: pointer;
            transition: all 0.2s;
        }
        .user-panel:hover {
            background: #e4e6ef;
        }
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 6px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
        }
        .user-details {
            display: flex;
            flex-direction: column;
        }
        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: #181c32;
            line-height: 1.2;
        }
        .user-role {
            font-size: 11px;
            color: #a1a5b7;
        }
        /* Main Content */
        .main-content {
            margin-left: 265px;
            margin-top: 80px;
            padding: 30px;
            min-height: calc(100vh - 80px);
            transition: margin-left 0.3s ease;
        }
        .sidebar.collapsed ~ .main-content {
            margin-left: 75px;
        }
        .content-container {
            max-width: 100%;
        }
        /* Cards */
        .card {
            background: #ffffff;
            border-radius: 12px;
            border: none;
            box-shadow: 0 0 28px 0 rgba(82, 63, 105, 0.05);
            margin-bottom: 25px;
        }
        .card-header {
            background: transparent;
            border-bottom: 1px solid #eff2f5;
            padding: 20px 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #181c32;
            margin: 0;
        }
        .card-body {
            padding: 25px;
        }
        /* Stats Widget */
        .stats-widget {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            flex-shrink: 0;
        }
        .stats-info {
            flex: 1;
        }
        .stats-label {
            font-size: 12px;
            color: #a1a5b7;
            font-weight: 500;
            margin-bottom: 5px;
        }
        .stats-value {
            font-size: 26px;
            font-weight: 700;
            color: #181c32;
            line-height: 1;
        }
        .stats-change {
            font-size: 12px;
            font-weight: 600;
            margin-top: 8px;
        }
        .stats-change.positive {
            color: #50cd89;
        }
        .stats-change.negative {
            color: #f1416c;
        }
        /* Button Styles */
        .btn {
            font-size: 13px;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 6px;
            transition: all 0.2s;
            border: none;
        }
        .btn-primary {
            background: #009ef7;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #0095e8;
            color: #ffffff;
        }
        .btn-danger {
            background: #f1416c;
            color: #ffffff;
        }
        .btn-danger:hover {
            background: #d9214e;
            color: #ffffff;
        }
        .btn-light {
            background: #f5f8fa;
            color: #7e8299;
        }
        .btn-light:hover {
            background: #e4e6ef;
            color: #5e6278;
        }
        /* Color Classes */
        .bg-light-primary {
            background: rgba(0, 158, 247, 0.1);
            color: #009ef7;
        }
        .bg-light-success {
            background: rgba(80, 205, 137, 0.1);
            color: #50cd89;
        }
        .bg-light-warning {
            background: rgba(255, 199, 0, 0.1);
            color: #ffc700;
        }
        .bg-light-danger {
            background: rgba(241, 65, 108, 0.1);
            color: #f1416c;
        }
        .bg-light-info {
            background: rgba(124, 128, 248, 0.1);
            color: #7c80f8;
        }
    </style>
</head>
<body>
    <!-- Sidebar - Metronic 9 Style -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <h3>Trading AI</h3>
            <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                <i class="bi bi-list"></i>
            </button>
        </div>
        
        <div class="sidebar-menu">
            <div class="menu-section">
                <div class="menu-section-title">Main</div>
                <a href="dashboard.php" class="menu-item active">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="menu-item has-submenu" data-toggle="submenu">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Trading</span>
                    <i class="bi bi-chevron-right menu-arrow"></i>
                </a>
                <div class="submenu">
                    <a href="#" class="menu-item">
                        <i class="bi bi-dot"></i>
                        <span>Live Trading</span>
                    </a>
                    <a href="#" class="menu-item">
                        <i class="bi bi-dot"></i>
                        <span>Auto Trading</span>
                    </a>
                    <a href="#" class="menu-item">
                        <i class="bi bi-dot"></i>
                        <span>Manual Orders</span>
                    </a>
                </div>
                <a href="#" class="menu-item has-submenu" data-toggle="submenu">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Analytics</span>
                    <i class="bi bi-chevron-right menu-arrow"></i>
                </a>
                <div class="submenu">
                    <a href="#" class="menu-item">
                        <i class="bi bi-dot"></i>
                        <span>Performance</span>
                    </a>
                    <a href="#" class="menu-item">
                        <i class="bi bi-dot"></i>
                        <span>Risk Analysis</span>
                    </a>
                </div>
            </div>
            
            <div class="menu-section">
                <div class="menu-section-title">Management</div>
                <a href="#" class="menu-item">
                    <i class="bi bi-wallet2"></i>
                    <span>Portfolio</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-clock-history"></i>
                    <span>History</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Reports</span>
                </a>
            </div>
            
            <div class="menu-section">
                <div class="menu-section-title">Settings</div>
                <a href="#" class="menu-item">
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Header Toolbar -->
    <div class="header-toolbar">
        <div class="page-title">
            <h1>Dashboard</h1>
            <ul class="breadcrumb">
                <li class="breadcrumb-item">Home</li>
                <li class="breadcrumb-separator">/</li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ul>
        </div>
        
        <div class="toolbar-actions">
            <div class="user-panel">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                    <div class="user-role">Administrator</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="content-container">
            <!-- Stats Widgets Row -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="stats-widget">
                                <div class="stats-icon bg-light-primary">
                                    <i class="bi bi-graph-up"></i>
                                </div>
                                <div class="stats-info">
                                    <div class="stats-label">Total Balance</div>
                                    <div class="stats-value">$45,820</div>
                                    <div class="stats-change positive">
                                        <i class="bi bi-arrow-up"></i> 8.5% from last month
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="stats-widget">
                                <div class="stats-icon bg-light-success">
                                    <i class="bi bi-trophy"></i>
                                </div>
                                <div class="stats-info">
                                    <div class="stats-label">Today's Profit</div>
                                    <div class="stats-value">+12.5%</div>
                                    <div class="stats-change positive">
                                        <i class="bi bi-arrow-up"></i> $2,456 profit
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="stats-widget">
                                <div class="stats-icon bg-light-warning">
                                    <i class="bi bi-activity"></i>
                                </div>
                                <div class="stats-info">
                                    <div class="stats-label">Active Trades</div>
                                    <div class="stats-value">24</div>
                                    <div class="stats-change positive">
                                        <i class="bi bi-arrow-up"></i> 4 new today
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="stats-widget">
                                <div class="stats-icon bg-light-danger">
                                    <i class="bi bi-percent"></i>
                                </div>
                                <div class="stats-info">
                                    <div class="stats-label">Success Rate</div>
                                    <div class="stats-value">89%</div>
                                    <div class="stats-change positive">
                                        <i class="bi bi-arrow-up"></i> 3% improvement
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Welcome Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Welcome to Trading AI</h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">Hello <strong><?php echo htmlspecialchars($user['username']); ?></strong>, welcome back to your trading dashboard!</p>
                            <div class="alert alert-primary d-flex align-items-center mb-0" role="alert">
                                <i class="bi bi-info-circle-fill me-3" style="font-size: 20px;"></i>
                                <div>
                                    <strong>System Status:</strong> All systems operational. Real-time data streaming is active and all trading algorithms are running smoothly.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Info Row -->
            <div class="row mt-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Activity</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-0">Your recent trading activity will appear here.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Stats</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Win Rate</span>
                                    <span class="fw-bold">89%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 89%"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Portfolio Growth</span>
                                    <span class="fw-bold">76%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 76%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Risk Level</span>
                                    <span class="fw-bold">34%</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 34%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dynamic Sidebar Menu
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle functionality
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            // Load saved state
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
            }
            
            sidebarToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                sidebar.classList.toggle('collapsed');
                
                // Save state
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
            
            // Handle submenu toggles
            const menuItems = document.querySelectorAll('.menu-item[data-toggle="submenu"]');
            
            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const submenu = this.nextElementSibling;
                    const isOpen = this.classList.contains('open');
                    
                    // Close all other submenus
                    document.querySelectorAll('.menu-item.has-submenu').forEach(otherItem => {
                        if (otherItem !== this) {
                            otherItem.classList.remove('open');
                            const otherSubmenu = otherItem.nextElementSibling;
                            if (otherSubmenu && otherSubmenu.classList.contains('submenu')) {
                                otherSubmenu.classList.remove('open');
                            }
                        }
                    });
                    
                    // Toggle current submenu
                    if (isOpen) {
                        this.classList.remove('open');
                        submenu.classList.remove('open');
                    } else {
                        this.classList.add('open');
                        submenu.classList.add('open');
                    }
                });
            });
            
            // Active menu highlighting
            const currentPath = window.location.pathname;
            document.querySelectorAll('.menu-item').forEach(item => {
                if (item.getAttribute('href') === currentPath) {
                    item.classList.add('active');
                    // If it's in a submenu, open the parent
                    const parentSubmenu = item.closest('.submenu');
                    if (parentSubmenu) {
                        parentSubmenu.classList.add('open');
                        const parentMenuItem = parentSubmenu.previousElementSibling;
                        if (parentMenuItem) {
                            parentMenuItem.classList.add('open');
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
