<?php
require_once __DIR__ . '/../functions/functions.php';

// Clear all session data
session_start();
session_unset();
session_destroy();

// Redirect to login page with success message
setFlashMessage('success', 'You have been successfully logged out.');
redirect('/auth/login.php');