<?php
/**
 * Admin Authentication System for CampusMart
 * Handles admin login, session management, and authorization
 */

require_once dirname(__DIR__) . '/config/config.php';

class AdminAuth {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Admin login
     */
    public function login($username, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, password_hash, full_name, role, is_active 
                FROM admin_users 
                WHERE (username = ? OR email = ?) AND is_active = 1
            ");
            $stmt->execute([$username, $username]);
            $admin = $stmt->fetch();
            
            if (!$admin) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            if (!password_verify($password, $admin['password_hash'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            // Set admin session
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_last_activity'] = time();
            
            // Update last login
            $stmt = $this->db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$admin['id']]);
            
            // Log admin login
            $this->logAdminActivity($admin['id'], 'admin_login', 'admin_users', $admin['id']);
            
            return ['success' => true, 'message' => 'Login successful', 'admin' => $admin];
            
        } catch (PDOException $e) {
            error_log("Admin login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Admin logout
     */
    public function logout() {
        if (isset($_SESSION['admin_id'])) {
            $this->logAdminActivity($_SESSION['admin_id'], 'admin_logout', 'admin_users', $_SESSION['admin_id']);
        }
        
        // Clear admin session variables
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_email']);
        unset($_SESSION['admin_name']);
        unset($_SESSION['admin_role']);
        unset($_SESSION['admin_last_activity']);
        
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    /**
     * Check if admin is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['admin_id']) && isset($_SESSION['admin_role']);
    }
    
    /**
     * Get current admin info
     */
    public function getCurrentAdmin() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username'],
            'email' => $_SESSION['admin_email'],
            'name' => $_SESSION['admin_name'],
            'role' => $_SESSION['admin_role']
        ];
    }
    
    /**
     * Check admin permissions
     */
    public function hasPermission($permission) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $role = $_SESSION['admin_role'];
        
        switch ($permission) {
            case 'super_admin':
                return $role === 'super_admin';
            case 'admin':
                return in_array($role, ['super_admin', 'admin']);
            case 'moderator':
                return in_array($role, ['super_admin', 'admin', 'moderator']);
            default:
                return false;
        }
    }
    
    /**
     * Require admin login
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . SITE_URL . '/admin/login.php');
            exit();
        }
        
        // Check session timeout
        if (isset($_SESSION['admin_last_activity']) && 
            (time() - $_SESSION['admin_last_activity'] > SESSION_TIMEOUT)) {
            $this->logout();
            header('Location: ' . SITE_URL . '/admin/login.php?timeout=1');
            exit();
        }
        
        $_SESSION['admin_last_activity'] = time();
    }
    
    /**
     * Require specific permission
     */
    public function requirePermission($permission) {
        $this->requireLogin();
        
        if (!$this->hasPermission($permission)) {
            header('Location: ' . SITE_URL . '/admin/index.php?error=insufficient_permissions');
            exit();
        }
    }
    
    /**
     * Create new admin user
     */
    public function createAdmin($data) {
        try {
            // Check if current user has permission
            if (!$this->hasPermission('super_admin')) {
                return ['success' => false, 'message' => 'Insufficient permissions'];
            }
            
            // Validate required fields
            $required = ['username', 'email', 'password', 'full_name', 'role'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => "Field '$field' is required"];
                }
            }
            
            // Check if username or email exists
            $stmt = $this->db->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
            $stmt->execute([$data['username'], $data['email']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            // Hash password
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert new admin
            $stmt = $this->db->prepare("
                INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, 1, NOW())
            ");
            
            $result = $stmt->execute([
                $data['username'],
                $data['email'],
                $passwordHash,
                $data['full_name'],
                $data['role']
            ]);
            
            if ($result) {
                $adminId = $this->db->lastInsertId();
                $this->logAdminActivity($_SESSION['admin_id'], 'create_admin', 'admin_users', $adminId);
                return ['success' => true, 'message' => 'Admin created successfully', 'admin_id' => $adminId];
            } else {
                return ['success' => false, 'message' => 'Failed to create admin'];
            }
            
        } catch (PDOException $e) {
            error_log("Create admin error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Log admin activity
     */
    private function logAdminActivity($adminId, $action, $tableName, $recordId) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_logs (user_id, action, table_name, record_id, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $adminId,
                $action,
                $tableName,
                $recordId,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Admin activity log error: " . $e->getMessage());
        }
    }
}

// Global admin auth functions
function getAdminAuth() {
    static $adminAuth = null;
    if ($adminAuth === null) {
        $adminAuth = new AdminAuth();
    }
    return $adminAuth;
}

function isAdminLoggedIn() {
    return getAdminAuth()->isLoggedIn();
}

function getCurrentAdmin() {
    return getAdminAuth()->getCurrentAdmin();
}

function requireAdminLogin() {
    getAdminAuth()->requireLogin();
}

function requireAdminPermission($permission) {
    getAdminAuth()->requirePermission($permission);
}

function hasAdminPermission($permission) {
    return getAdminAuth()->hasPermission($permission);
}
?>
