<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Trading AI'; ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
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
        .page-title h1 {
            font-size: 18px;
            font-weight: 600;
            color: #181c32;
            margin: 0;
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
    </style>
</head>
<body>
    <!-- Sidebar -->
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
                <a href="dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Trading</span>
                </a>
                <a href="#" class="menu-item">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Analytics</span>
                </a>
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
                <?php if ($user['is_admin']): ?>
                <a href="users.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i>
                    <span>Gestione Utenti</span>
                </a>
                <a href="roles.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'roles.php' ? 'active' : ''; ?>">
                    <i class="bi bi-shield-check"></i>
                    <span>Gestione Ruoli</span>
                </a>
                <?php endif; ?>
                <a href="#" class="menu-item">
                    <i class="bi bi-gear"></i>
                    <span>Impostazioni</span>
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
            <h1><?php echo $pageTitle ?? 'Trading AI'; ?></h1>
        </div>
        
        <div class="toolbar-actions">
            <div class="user-panel">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                    <div class="user-role"><?php echo $user['is_admin'] ? 'Amministratore' : 'Utente'; ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
