<?php
require_once __DIR__ . '/db.php';

// Authentication Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isAgent() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'agent';
}

// Security Functions
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Balance Functions
function convertRupiahToCoins($rupiah) {
    return $rupiah * COIN_RATE;
}

function getUserBalance($userId) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['balance'] : 0;
    } catch (PDOException $e) {
        error_log("Error getting user balance: " . $e->getMessage());
        return false;
    }
}

function updateUserBalance($userId, $newBalance) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
        return $stmt->execute([$newBalance, $userId]);
    } catch (PDOException $e) {
        error_log("Error updating user balance: " . $e->getMessage());
        return false;
    }
}

// Agent IP Whitelist Functions
function getAgentWhitelistedIPs($agentId) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM agent_ip_whitelist 
            WHERE agent_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$agentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting agent whitelisted IPs: " . $e->getMessage());
        return [];
    }
}

function addAgentIPToWhitelist($agentId, $ipAddress, $description = '') {
    try {
        $pdo = getConnection();
        
        // Verify the user is an agent
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ? AND role = 'agent'");
        $stmt->execute([$agentId]);
        if (!$stmt->fetch()) {
            throw new Exception('Invalid agent ID or user is not an agent.');
        }
        
        // Check if agent has reached the limit (2 IPs)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM agent_ip_whitelist WHERE agent_id = ?");
        $stmt->execute([$agentId]);
        if ($stmt->fetchColumn() >= 2) {
            throw new Exception('Maximum number of whitelisted IPs (2) reached.');
        }
        
        // Add new IP to whitelist
        $stmt = $pdo->prepare("
            INSERT INTO agent_ip_whitelist (agent_id, ip_address, description) 
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$agentId, $ipAddress, $description]);
    } catch (Exception $e) {
        error_log("Error adding agent IP to whitelist: " . $e->getMessage());
        throw $e;
    }
}

function removeAgentIPFromWhitelist($agentId, $ipId) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("
            DELETE FROM agent_ip_whitelist 
            WHERE id = ? AND agent_id = ?
        ");
        return $stmt->execute([$ipId, $agentId]);
    } catch (PDOException $e) {
        error_log("Error removing agent IP from whitelist: " . $e->getMessage());
        return false;
    }
}

function isIPWhitelistedForAgent($agentId, $ipAddress) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM agent_ip_whitelist 
            WHERE agent_id = ? AND ip_address = ?
        ");
        $stmt->execute([$agentId, $ipAddress]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error checking agent IP whitelist: " . $e->getMessage());
        return false;
    }
}

// API Functions
function validateAPIRequest($agentId, $ipAddress) {
    try {
        // Verify the user is an agent
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'agent'");
        $stmt->execute([$agentId]);
        if (!$stmt->fetch()) {
            return false;
        }

        // Check if IP is whitelisted for this agent
        return isIPWhitelistedForAgent($agentId, $ipAddress);
    } catch (PDOException $e) {
        error_log("Error validating API request: " . $e->getMessage());
        return false;
    }
}

function logAPIRequest($agentId, $ipAddress, $endpoint, $method, $statusCode, $requestData = null, $responseData = null) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO api_logs 
            (agent_id, ip_address, endpoint, method, status_code, request_data, response_data)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $agentId,
            $ipAddress,
            $endpoint,
            $method,
            $statusCode,
            $requestData ? json_encode($requestData) : null,
            $responseData ? json_encode($responseData) : null
        ]);
    } catch (PDOException $e) {
        error_log("Error logging API request: " . $e->getMessage());
        return false;
    }
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// User Management Functions
function createUser($username, $password, $role, $email = null) {
    try {
        $pdo = getConnection();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, email) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$username, $hashedPassword, $role, $email]);
    } catch (PDOException $e) {
        error_log("Error creating user: " . $e->getMessage());
        return false;
    }
}

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

// Flash Messages
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Redirection Functions
function redirect($path) {
    header("Location: " . $path);
    exit();
}

// Access Control
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/auth/login.php');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect('/dashboard/agent-dashboard.php');
    }
}

function requireAgent() {
    requireLogin();
    if (!isAgent()) {
        redirect('/dashboard/admin-dashboard.php');
    }
}