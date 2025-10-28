<?php
/**
 * Unified Admin Authentication System
 * Handles login, session management, and permission checks
 */

// Start output buffering to prevent header issues
ob_start();

require_once __DIR__ . '/../includes/db_connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Validate admin credentials against database
 * @param string $email
 * @param string $password
 * @return array|false Admin data or false on failure
 */
function validateAdminCredentials($email, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                a.AdminID,
                a.Name,
                a.Email,
                a.Phone,
                a.Role,
                a.Password,
                a.Status,
                a.IsSuperAdmin,
                a.LastLogin
            FROM Admin a
            WHERE a.Email = ? AND a.Status = 'Active'
            LIMIT 1
        ");
        
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($password, $admin['Password'])) {
            return $admin;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Authentication error: " . $e->getMessage());
        return false;
    }
}

/**
 * Initialize admin session after successful login
 * @param array $admin Admin data
 */
function initializeAdminSession($admin) {
    global $pdo;
    
    // Set session variables
    $_SESSION['admin_id'] = $admin['AdminID'];
    $_SESSION['AdminID'] = $admin['AdminID'];
    $_SESSION['admin_name'] = $admin['Name'];
    $_SESSION['admin_email'] = $admin['Email'];
    $_SESSION['admin_role'] = $admin['Role'];
    $_SESSION['is_superadmin'] = (bool)$admin['IsSuperAdmin'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    
    // Update last login time in database
    try {
        $stmt = $pdo->prepare("
            UPDATE Admin 
            SET LastLogin = CURRENT_TIMESTAMP 
            WHERE AdminID = ?
        ");
        $stmt->execute([$admin['AdminID']]);
        
        // Log the login activity
        logAdminActivity($admin['AdminID'], 'Login', 'User logged into the system');
    } catch (PDOException $e) {
        error_log("Session initialization error: " . $e->getMessage());
    }
    
    // Load admin permissions into session
    loadAdminPermissions($admin['AdminID'], $admin['Role']);
}

/**
 * Load admin permissions based on role
 * @param int $adminId
 * @param string $roleName
 */
function loadAdminPermissions($adminId, $roleName) {
    global $pdo;
    
    try {
        // Get role ID
        $stmt = $pdo->prepare("SELECT RoleID FROM AdminRoles WHERE RoleName = ? LIMIT 1");
        $stmt->execute([$roleName]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$role) {
            $_SESSION['permissions'] = [];
            return;
        }
        
        // Get all permissions for this role
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.PermissionKey
            FROM AdminPermissions p
            INNER JOIN RolePermissions rp ON p.PermissionID = rp.PermissionID
            WHERE rp.RoleID = ?
        ");
        $stmt->execute([$role['RoleID']]);
        
        $permissions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $permissions[] = $row['PermissionKey'];
        }
        
        $_SESSION['permissions'] = $permissions;
        $_SESSION['role_id'] = $role['RoleID'];
        
    } catch (PDOException $e) {
        error_log("Permission loading error: " . $e->getMessage());
        $_SESSION['permissions'] = [];
    }
}

/**
 * Check if user is logged in
 * @return bool
 */
function isAdminLoggedIn() {
    // Check if session variables are set
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        return false;
    }
    
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_email'])) {
        return false;
    }
    
    // Check session timeout (30 minutes of inactivity)
    if (isset($_SESSION['last_activity'])) {
        $timeout = 1800; // 30 minutes in seconds
        if ((time() - $_SESSION['last_activity']) > $timeout) {
            destroyAdminSession();
            return false;
        }
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Check if admin has specific permission
 * @param string $permissionKey Permission key to check
 * @return bool
 */
function hasPermission($permissionKey) {
    // Super admins have all permissions
    if (isset($_SESSION['is_superadmin']) && $_SESSION['is_superadmin']) {
        return true;
    }
    
    // Check if permission exists in session
    if (!isset($_SESSION['permissions']) || !is_array($_SESSION['permissions'])) {
        return false;
    }
    
    return in_array($permissionKey, $_SESSION['permissions']);
}

/**
 * Check if admin has any of the specified permissions
 * @param array $permissionKeys Array of permission keys
 * @return bool
 */
function hasAnyPermission($permissionKeys) {
    foreach ($permissionKeys as $key) {
        if (hasPermission($key)) {
            return true;
        }
    }
    return false;
}

/**
 * Require specific permission or redirect
 * @param string $permissionKey Permission key required
 * @param string $redirectUrl URL to redirect to if no permission
 */
function requirePermission($permissionKey, $redirectUrl = null) {
    if (!hasPermission($permissionKey)) {
        if ($redirectUrl) {
            header("Location: $redirectUrl");
        } else {
            http_response_code(403);
            die("Access Denied: You don't have permission to access this resource.");
        }
        exit();
    }
}

/**
 * Get admin's permissions list
 * @return array
 */
function getAdminPermissions() {
    if (!isset($_SESSION['permissions'])) {
        return [];
    }
    return $_SESSION['permissions'];
}

/**
 * Check if current admin is super admin
 * @return bool
 */
function isSuperAdmin() {
    return isset($_SESSION['is_superadmin']) && $_SESSION['is_superadmin'] === true;
}

/**
 * Destroy admin session (logout)
 */
function destroyAdminSession() {
    // Log the logout activity before destroying session
    if (isset($_SESSION['admin_id'])) {
        logAdminActivity($_SESSION['admin_id'], 'Logout', 'User logged out from the system');
    }
    
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Log admin activity
 * @param int $adminId
 * @param string $action Action performed
 * @param string $description Detailed description
 */
function logAdminActivity($adminId, $action, $description = '') {
    global $pdo;
    
    try {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $stmt = $pdo->prepare("
            INSERT INTO ActivityLog (AdminID, Action, Description, IPAddress, UserAgent)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$adminId, $action, $description, $ipAddress, $userAgent]);
    } catch (PDOException $e) {
        error_log("Activity logging error: " . $e->getMessage());
    }
}

/**
 * Get admin details from session
 * @return array
 */
function getAdminDetails() {
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'name' => $_SESSION['admin_name'] ?? 'Unknown',
        'email' => $_SESSION['admin_email'] ?? '',
        'role' => $_SESSION['admin_role'] ?? '',
        'is_superadmin' => $_SESSION['is_superadmin'] ?? false,
        'permissions' => $_SESSION['permissions'] ?? []
    ];
}

/**
 * Redirect to login page if not authenticated
 * @param string $loginUrl Login page URL
 */
function requireLogin($loginUrl = 'login.php') {
    if (!isAdminLoggedIn()) {
        // Store the requested URL to redirect back after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: $loginUrl");
        exit();
    }
}

/**
 * Get redirect URL after successful login
 * @param string $default Default URL if no redirect stored
 * @return string
 */
function getLoginRedirectUrl($default = 'dashboard.php') {
    if (isset($_SESSION['redirect_after_login'])) {
        $url = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        return $url;
    }
    return $default;
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get all roles with their permissions
 * @return array
 */
function getAllRolesWithPermissions() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                r.RoleID,
                r.RoleName,
                r.Description,
                COUNT(rp.PermissionID) as PermissionCount
            FROM AdminRoles r
            LEFT JOIN RolePermissions rp ON r.RoleID = rp.RoleID
            GROUP BY r.RoleID
            ORDER BY r.RoleName
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Roles fetch error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get permissions for a specific role
 * @param int $roleId
 * @return array
 */
function getRolePermissions($roleId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                p.PermissionID,
                p.PermissionName,
                p.PermissionKey,
                p.Category
            FROM AdminPermissions p
            INNER JOIN RolePermissions rp ON p.PermissionID = rp.PermissionID
            WHERE rp.RoleID = ?
            ORDER BY p.Category, p.PermissionName
        ");
        
        $stmt->execute([$roleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Role permissions fetch error: " . $e->getMessage());
        return [];
    }
}
