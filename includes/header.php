<?php 
require_once __DIR__ . '/../functions/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ampibet - <?php echo isset($pageTitle) ? $pageTitle : 'Welcome'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
        }
        
        /* Dark mode variables */
        [data-theme="dark"] {
            --bg-color: #1a1a1a;
            --text-color: #ffffff;
            --card-bg: #2d2d2d;
            --border-color: #404040;
        }
        
        /* Light mode variables */
        [data-theme="light"] {
            --bg-color: #ffffff;
            --text-color: #333333;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
        }
        
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: all 0.3s ease;
        }
        
        .card {
            background-color: var(--card-bg);
            border-color: var(--border-color);
        }
        
        /* Sidebar styles */
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 60px;
            background-color: var(--card-bg);
            border-right: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link {
            color: var(--text-color);
            padding: 10px 20px;
            margin: 5px 0;
            border-radius: 5px;
        }
        
        .sidebar .nav-link:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        /* Main content wrapper */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            padding-top: 80px;
        }
        
        /* Navbar customization */
        .navbar {
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
        }
        
        /* Dark mode toggle button */
        .theme-toggle {
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            border: 1px solid var(--border-color);
        }
        
        /* Custom DataTables styling */
        .dataTables_wrapper {
            padding: 20px;
            background-color: var(--card-bg);
            border-radius: 8px;
            margin: 20px 0;
        }
        
        /* Custom Select2 styling */
        .select2-container--default .select2-selection--single {
            background-color: var(--card-bg);
            border-color: var(--border-color);
            color: var(--text-color);
        }
        
        /* Custom card styling */
        .custom-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        /* Animation for loading states */
        .loading {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding-top: 0;
            }
            
            .main-content {
                margin-left: 0;
                padding-top: 20px;
            }
        }
    </style>
</head>
<body data-theme="light">
<?php
// Display flash messages if any
$flash = getFlashMessage();
if ($flash): 
?>
<div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
    <?php echo $flash['message']; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>