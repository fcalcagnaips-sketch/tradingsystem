<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /tradingai/login.php');
        exit;
    }
}

// Login user
function loginUser($username, $password) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT id, username, email, password, full_name FROM users WHERE username = ? AND active = 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Password correct, create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Load user roles
            $roles = getUserRoles($user['id']);
            $_SESSION['roles'] = $roles;
            $_SESSION['is_admin'] = in_array('admin', $roles);
            
            // Update last login
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();
            $updateStmt->close();
            
            $stmt->close();
            $conn->close();
            return true;
        }
    }
    
    $stmt->close();
    $conn->close();
    return false;
}

// Logout user
function logoutUser() {
    session_unset();
    session_destroy();
    header('Location: /tradingai/login.php');
    exit;
}

// Get current user info
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'full_name' => $_SESSION['full_name'],
            'roles' => $_SESSION['roles'] ?? [],
            'is_admin' => $_SESSION['is_admin'] ?? false
        ];
    }
    return null;
}

// ===== ROLE MANAGEMENT FUNCTIONS =====

// Get user roles
function getUserRoles($userId) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        SELECT r.name 
        FROM roles r
        INNER JOIN user_roles ur ON r.id = ur.role_id
        WHERE ur.user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $roles = [];
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row['name'];
    }
    
    $stmt->close();
    $conn->close();
    
    return $roles;
}

// Check if user has specific role
function hasRole($roleName) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $roles = $_SESSION['roles'] ?? [];
    return in_array($roleName, $roles);
}

// Check if user has any of the specified roles
function hasAnyRole($roleNames) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $roles = $_SESSION['roles'] ?? [];
    foreach ($roleNames as $roleName) {
        if (in_array($roleName, $roles)) {
            return true;
        }
    }
    
    return false;
}

// Check if user is admin
function isAdmin() {
    return hasRole('admin');
}

// Require specific role
function requireRole($roleName) {
    requireLogin();
    
    if (!hasRole($roleName)) {
        header('HTTP/1.0 403 Forbidden');
        die('Accesso negato. Non hai i permessi necessari.');
    }
}

// Require admin role
function requireAdmin() {
    requireRole('admin');
}

// Assign role to user
function assignRole($userId, $roleName) {
    $conn = getDBConnection();
    
    // Get role ID
    $stmt = $conn->prepare("SELECT id FROM roles WHERE name = ?");
    $stmt->bind_param("s", $roleName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        return false;
    }
    
    $role = $result->fetch_assoc();
    $roleId = $role['id'];
    $stmt->close();
    
    // Assign role
    $stmt = $conn->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $roleId);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

// Remove role from user
function removeRole($userId, $roleName) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        DELETE ur FROM user_roles ur
        INNER JOIN roles r ON ur.role_id = r.id
        WHERE ur.user_id = ? AND r.name = ?
    ");
    $stmt->bind_param("is", $userId, $roleName);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

// Get all available roles
function getAllRoles() {
    $conn = getDBConnection();
    
    $result = $conn->query("SELECT * FROM roles ORDER BY name");
    $roles = [];
    
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row;
    }
    
    $conn->close();
    
    return $roles;
}
?>
