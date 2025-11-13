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
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F5F8FA;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 265px;
            background: #1E1E2D;
            padding: 0;
            z-index: 1000;
        }
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #2B2B40;
        }
        .sidebar-header h3 {
            color: #fff;
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }
        .sidebar-menu {
            padding: 20px 0;
        }
        .menu-item {
            display: block;
            padding: 12px 25px;
            color: #7E8299;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 14px;
            font-weight: 500;
        }
        .menu-item:hover,
        .menu-item.active {
            background: #1B1B28;
            color: #fff;
        }
        .menu-item i {
            margin-right: 12px;
            font-size: 16px;
        }
        .main-content {
            margin-left: 265px;
            padding: 0;
        }
        .header {
            background: #fff;
            padding: 20px 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 600;
            color: #181C32;
            margin: 0;
        }
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-info {
            text-align: right;
        }
        .user-info .name {
            font-weight: 600;
            color: #181C32;
            font-size: 14px;
        }
        .user-info .email {
            font-size: 12px;
            color: #7E8299;
        }
        .content-area {
            padding: 30px;
        }
        .stats-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        .stats-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        .stats-card h3 {
            font-size: 28px;
            font-weight: 700;
            color: #181C32;
            margin-bottom: 5px;
        }
        .stats-card p {
            color: #7E8299;
            font-size: 14px;
            margin: 0;
        }
        .btn-logout {
            background: #F1416C;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s;
        }
        .btn-logout:hover {
            background: #D9214E;
            color: #fff;
        }
        .bg-primary-light {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }
        .bg-success-light {
            background: rgba(80, 205, 137, 0.1);
            color: #50CD89;
        }
        .bg-warning-light {
            background: rgba(255, 184, 34, 0.1);
            color: #FFB822;
        }
        .bg-danger-light {
            background: rgba(241, 65, 108, 0.1);
            color: #F1416C;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Trading AI</h3>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item active">
                <i class="bi bi-grid-fill"></i>Dashboard
            </a>
            <a href="#" class="menu-item">
                <i class="bi bi-graph-up"></i>Trading
            </a>
            <a href="#" class="menu-item">
                <i class="bi bi-bar-chart-line"></i>Analytics
            </a>
            <a href="#" class="menu-item">
                <i class="bi bi-wallet2"></i>Portfolio
            </a>
            <a href="#" class="menu-item">
                <i class="bi bi-gear"></i>Settings
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <h1>Dashboard</h1>
            <div class="user-menu">
                <div class="user-info">
                    <div class="name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                    <div class="email"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <a href="logout.php" class="btn btn-logout">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <div class="row">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="icon bg-primary-light">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h3>$45,820</h3>
                        <p>Total Balance</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="icon bg-success-light">
                            <i class="bi bi-arrow-up-circle"></i>
                        </div>
                        <h3>+12.5%</h3>
                        <p>Today's Profit</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="icon bg-warning-light">
                            <i class="bi bi-activity"></i>
                        </div>
                        <h3>24</h3>
                        <p>Active Trades</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="icon bg-danger-light">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <h3>89%</h3>
                        <p>Success Rate</p>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="stats-card">
                        <h4 class="mb-4">Welcome to Trading AI Dashboard</h4>
                        <p class="mb-3">You are logged in as <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>System Status:</strong> All systems operational. Real-time data streaming active.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
