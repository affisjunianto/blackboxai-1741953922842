<?php
require_once __DIR__ . '/../functions/functions.php';

// Ensure user is logged in and is either admin or agent
requireLogin();
if (!isAdmin() && !isAgent()) {
    setFlashMessage('error', 'Insufficient permissions');
    redirect('/dashboard/agent-dashboard.php');
}

try {
    // Generate new API credentials
    $credentials = generateApiCredentials($_SESSION['user_id']);
    
    if ($credentials) {
        setFlashMessage('success', 'API credentials generated successfully');
    } else {
        setFlashMessage('error', 'Failed to generate API credentials');
    }
} catch (Exception $e) {
    setFlashMessage('error', 'Error: ' . $e->getMessage());
}

// Redirect back to API documentation
redirect('/api/');