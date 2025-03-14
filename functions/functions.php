<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../config.php';

// Session Management
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// User Authentication Functions
function validateUser($username, $password) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Error validating user: " . $e->getMessage());
        return false;
    }
}

// Session Functions
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please login to continue.');
        redirect('/auth/login.php');
    }
}

// Role Check Functions
function isAdmin() {
    startSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isAgent() {
    startSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'agent';
}

function isSubAgent() {
    startSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'sub_agent';
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        setFlashMessage('error', 'Insufficient permissions.');
        redirect('/dashboard/agent-dashboard.php');
    }
}

// Flash Messages
function setFlashMessage($type, $message) {
    startSession();
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    startSession();
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Navigation Functions
function redirect($path) {
    header("Location: $path");
    exit();
}

// Transaction Functions
function createTransaction($userId, $type, $amount, $description = '') {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $type, $amount, $description]);
    } catch (PDOException $e) {
        error_log("Error creating transaction: " . $e->getMessage());
        return false;
    }
}

function updateTransactionStatus($transactionId, $status) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $transactionId]);
    } catch (PDOException $e) {
        error_log("Error updating transaction status: " . $e->getMessage());
        return false;
    }
}

function getTransactions($userId, $limit = 10) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching transactions: " . $e->getMessage());
        return [];
    }
}

// Sub-agent Management Functions
function getSubAgents($parentId) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE parent_id = ? AND role = 'sub_agent'");
        $stmt->execute([$parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching sub-agents: " . $e->getMessage());
        return [];
    }
}

// Balance Management Functions
function updateBalance($userId, $amount, $type = 'add') {
    try {
        $pdo = getConnection();
        $sql = "UPDATE users SET balance = balance " . ($type === 'add' ? '+' : '-') . " ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$amount, $userId]);
    } catch (PDOException $e) {
        error_log("Error updating balance: " . $e->getMessage());
        return false;
    }
}

function getBalance($userId) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['balance'] : 0;
    } catch (PDOException $e) {
        error_log("Error getting balance: " . $e->getMessage());
        return 0;
    }
}

// IP Whitelist Functions
function addToWhitelist($agentId, $ipAddress, $description = '') {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("INSERT INTO agent_ip_whitelist (agent_id, ip_address, description) VALUES (?, ?, ?)");
        return $stmt->execute([$agentId, $ipAddress, $description]);
    } catch (PDOException $e) {
        error_log("Error adding IP to whitelist: " . $e->getMessage());
        return false;
    }
}

function removeFromWhitelist($agentId, $ipAddress) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("DELETE FROM agent_ip_whitelist WHERE agent_id = ? AND ip_address = ?");
        return $stmt->execute([$agentId, $ipAddress]);
    } catch (PDOException $e) {
        error_log("Error removing IP from whitelist: " . $e->getMessage());
        return false;
    }
}

// API Functions
function validateAPIRequest($agentId, $ipAddress) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT * FROM agent_ip_whitelist WHERE agent_id = ? AND ip_address = ?");
        $stmt->execute([$agentId, $ipAddress]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    } catch (PDOException $e) {
        error_log("Error validating API request: " . $e->getMessage());
        return false;
    }
}

// API Logging Functions
function logApiRequest($agentId, $ipAddress, $endpoint, $method, $statusCode, $requestData = '', $responseData = '') {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("INSERT INTO api_logs (agent_id, ip_address, endpoint, method, status_code, request_data, response_data) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$agentId, $ipAddress, $endpoint, $method, $statusCode, $requestData, $responseData]);
    } catch (PDOException $e) {
        error_log("Error logging API request: " . $e->getMessage());
        return false;
    }
}

// Utility Functions
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function formatCurrency($amount) {
    return number_format($amount, 2, '.', ',');
}

function logActivity($userId, $action, $details = '') {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $action, $details]);
    } catch (PDOException $e) {
        error_log("Error logging activity: " . $e->getMessage());
        return false;
    }
}