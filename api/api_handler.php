<?php
require_once __DIR__ . '/../functions/functions.php';

// API Request Handler
header('Content-Type: application/json');

// Handle both POST and GET methods
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $postData = json_decode(file_get_contents('php://input'), true);
    
    // Check credentials in POST body
    if (!isset($postData['username']) || !isset($postData['password'])) {
        jsonResponse([
            'status' => 'error',
            'message' => 'Username and password are required'
        ], 400);
    }

    // Validate user credentials
    $user = validateUser($postData['username'], $postData['password']);
    if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'agent')) {
        jsonResponse([
            'status' => 'error',
            'message' => 'Invalid credentials'
        ], 401);
    }

    $agentId = $user['id'];
} else {
    // Legacy GET method
    $agentId = $_GET['agent_id'] ?? null;
    if (!$agentId) {
        jsonResponse([
            'status' => 'error',
            'message' => 'Agent ID is required'
        ], 400);
    }
}

// Validate agent and IP for both methods
if (!validateAPIRequest($agentId, $_SERVER['REMOTE_ADDR'])) {
    logAPIRequest(
        $agentId,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['REQUEST_URI'],
        $_SERVER['REQUEST_METHOD'],
        403,
        $_REQUEST,
        ['status' => 'error', 'message' => 'Unauthorized IP address']
    );

    jsonResponse([
        'status' => 'error',
        'message' => 'Unauthorized IP address'
    ], 403);
}

// Handle API endpoints
$endpoint = $_GET['endpoint'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($endpoint) {
        case 'balance':
            // For POST requests with user credentials
            if ($method === 'POST') {
                $targetUserId = isset($postData['user_id']) ? (int)$postData['user_id'] : $user['id'];
                
                // Agents can only check their own balance
                if ($user['role'] === 'agent' && $targetUserId !== $user['id']) {
                    throw new Exception('Agents can only check their own balance', 403);
                }
            } else {
                // For GET requests, use the provided user_id or agent_id
                $targetUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $agentId;
            }

            $balance = getUserBalance($targetUserId);
            if ($balance === false) {
                throw new Exception('User not found', 404);
            }

            $response = [
                'status' => 'success',
                'data' => [
                    'user_id' => $targetUserId,
                    'balance' => $balance
                ]
            ];

            // Log successful request
            logAPIRequest(
                $agentId,
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['REQUEST_URI'],
                $method,
                200,
                $method === 'POST' ? $postData : $_REQUEST,
                $response
            );

            jsonResponse($response);
            break;

        default:
            throw new Exception('Invalid endpoint', 404);
    }
} catch (Exception $e) {
    $statusCode = $e->getCode() ?: 500;
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];

    // Log failed request
    logAPIRequest(
        $agentId,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['REQUEST_URI'],
        $method,
        $statusCode,
        $method === 'POST' ? $postData : $_REQUEST,
        $response
    );

    jsonResponse($response, $statusCode);
}