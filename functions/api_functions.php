<?php
require_once __DIR__ . '/db.php';

// API Authentication Functions
function validateAPIRequest($agentId, $ipAddress) {
    try {
        // Verify the user is an agent or admin
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ? AND role IN ('agent', 'admin')");
        $stmt->execute([$agentId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return false;
        }

        // For admin, bypass IP check
        if ($user['role'] === 'admin') {
            return true;
        }

        // For agents, check if IP is whitelisted
        return isIPWhitelistedForAgent($agentId, $ipAddress);
    } catch (PDOException $e) {
        error_log("Error validating API request: " . $e->getMessage());
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

// Balance Functions
function getUserBalance($userId) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['balance'] : false;
    } catch (PDOException $e) {
        error_log("Error getting user balance: " . $e->getMessage());
        return false;
    }
}

// IP Whitelist Functions
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

// API Logging Functions
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

// Response Helper Functions
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}