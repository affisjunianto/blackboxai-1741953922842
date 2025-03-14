<?php
require_once __DIR__ . '/../functions/functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'status' => 'error',
        'message' => 'Only POST method is allowed'
    ], 405);
}

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

try {
    // For agents, only allow checking their own balance
    $targetUserId = isset($postData['user_id']) ? (int)$postData['user_id'] : $user['id'];
    
    if ($user['role'] === 'agent' && $targetUserId !== $user['id']) {
        throw new Exception('Agents can only check their own balance', 403);
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
        $user['id'],
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['REQUEST_URI'],
        'POST',
        200,
        $postData,
        $response
    );

    jsonResponse($response);

} catch (Exception $e) {
    $statusCode = $e->getCode() ?: 500;
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];

    // Log failed request
    logAPIRequest(
        $user['id'] ?? 0,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['REQUEST_URI'],
        'POST',
        $statusCode,
        $postData,
        $response
    );

    jsonResponse($response, $statusCode);
}