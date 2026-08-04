<?php
// admin/includes/auth.php
// Core authentication helper – MUST be included at the top of every admin page

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// Include main config for DB connection ($conn) and helpers (checkCSRF, jsonResponse)
require_once __DIR__ . '/../../api/config.php';

// Định nghĩa hằng số quyền
define('ROLE_SUPERADMIN', 'superadmin');
define('ROLE_ADMIN', 'admin');
define('ROLE_MODERATOR', 'moderator');

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

/**
 * Require admin login – redirect to login.php if not authenticated
 */
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Require a specific role (or higher)
 * @param string|array $requiredRole
 */
function requireRole($requiredRole) {
    requireAdmin();
    $admin = getCurrentAdmin();
    if (!$admin) {
        header('Location: login.php');
        exit;
    }
    $role = $admin['role'];
    if (is_array($requiredRole)) {
        if (!in_array($role, $requiredRole)) {
            die('Bạn không có quyền truy cập chức năng này.');
        }
    } else {
        if ($role !== $requiredRole && $role !== ROLE_SUPERADMIN) {
            die('Bạn không có quyền truy cập chức năng này.');
        }
    }
}

/**
 * Get current admin info from DB (cached in session to reduce DB calls)
 */
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) return null;
    
    // Optional: cache in session to avoid repeated DB queries
    if (isset($_SESSION['admin_data']) && is_array($_SESSION['admin_data'])) {
        return $_SESSION['admin_data'];
    }
    
    global $conn;
    $stmt = $conn->prepare('SELECT id, username, role FROM admin_users WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['admin_id']);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    if ($admin) {
        $_SESSION['admin_data'] = $admin;
    }
    return $admin;
}

/**
 * Log an admin action for audit trail
 */
function logAdminAction($action) {
    global $conn;
    if (!isAdminLoggedIn()) return;
    $stmt = $conn->prepare('INSERT INTO admin_logs (admin_id, action, ip) VALUES (?, ?, ?)');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt->bind_param('iss', $_SESSION['admin_id'], $action, $ip);
    $stmt->execute();
}

/**
 * Clear admin session data (useful after role changes)
 */
function clearAdminCache() {
    unset($_SESSION['admin_data']);
}
?>
