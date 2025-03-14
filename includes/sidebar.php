<?php
require_once __DIR__ . '/../functions/functions.php';

// Get current page for active menu highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar col-md-3 col-lg-2">
    <div class="position-sticky">
        <ul class="nav flex-column">
            <!-- Dashboard - Visible to all logged in users -->
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>" 
                   href="/dashboard/<?php echo isAdmin() ? 'admin' : 'agent'; ?>-dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>

            <?php if (isAdmin()): ?>
                <!-- Admin Only Menu Items -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'manage_balance.php' ? 'active' : ''; ?>" 
                       href="/admin/manage_balance.php">
                        <i class="fas fa-coins me-2"></i>
                        Manage Balance
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'manage_agents.php' ? 'active' : ''; ?>" 
                       href="/admin/manage_agents.php">
                        <i class="fas fa-users me-2"></i>
                        Manage Agents
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'manage_subagents.php' ? 'active' : ''; ?>" 
                       href="/admin/manage_subagents.php">
                        <i class="fas fa-user-friends me-2"></i>
                        Manage Sub Agents
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'transactions.php' ? 'active' : ''; ?>" 
                       href="/admin/transactions.php">
                        <i class="fas fa-exchange-alt me-2"></i>
                        Transactions
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'index.php' ? 'active' : ''; ?>" 
                       href="/api/index.php">
                        <i class="fas fa-code me-2"></i>
                        API Documentation
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'settings.php' ? 'active' : ''; ?>" 
                       href="/admin/settings.php">
                        <i class="fas fa-cog me-2"></i>
                        Settings
                    </a>
                </li>
            <?php endif; ?>

            <?php if (isAgent()): ?>
                <!-- Agent Only Menu Items -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'manage_subagents.php' ? 'active' : ''; ?>" 
                       href="/agent/manage_subagents.php">
                        <i class="fas fa-user-friends me-2"></i>
                        Manage Sub Agents
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'transactions.php' ? 'active' : ''; ?>" 
                       href="/agent/transactions.php">
                        <i class="fas fa-exchange-alt me-2"></i>
                        My Transactions
                    </a>
                </li>
            <?php endif; ?>

            <!-- Common Menu Items -->
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage == 'profile.php' ? 'active' : ''; ?>" 
                   href="/profile.php">
                    <i class="fas fa-user-circle me-2"></i>
                    Profile
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="/auth/logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
</div>