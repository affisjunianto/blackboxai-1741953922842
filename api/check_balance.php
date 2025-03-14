<?php
require_once __DIR__ . '/../functions/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $postData = json_decode(file_get_contents('php://input'), true);
    
    // Validate username/password in body
    if (!isset($postData['username']) || !isset($postData['password'])) {
        jsonResponse(['status' => 'error', 'message' => 'Username and password required'], 400);
    }

    // Validate credentials
    $user = validateUser($postData['username'], $postData['password']);
    if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'agent')) {
        jsonResponse(['status' => 'error', 'message' => 'Invalid credentials'], 401);
    }

    // Get target user ID
    $targetUserId = isset($postData['user_id']) ? (int)$postData['user_id'] : $user['id'];
    
    // Agents can only check their own balance
    if ($user['role'] === 'agent' && $targetUserId !== $user['id']) {
        jsonResponse(['status' => 'error', 'message' => 'Agents can only check their own balance'], 403);
    }

    // Get balance
    $balance = getUserBalance($targetUserId);
    if ($balance === false) {
        jsonResponse(['status' => 'error', 'message' => 'User not found'], 404);
    }

    // Return success response
    jsonResponse([
        'status' => 'success',
        'data' => [
            'user_id' => $targetUserId,
            'balance' => $balance
        ]
    ]);
} else {
    jsonResponse(['status' => 'error', 'message' => 'Only POST method is allowed'], 405);
}