<?php
require_once __DIR__ . '/../functions/functions.php';

// Ensure user is admin
requireAdmin();

$pageTitle = 'Admin Dashboard';
include_once __DIR__ . '/../includes/header.php';
include_once __DIR__ . '/../includes/navbar.php';
include_once __DIR__ . '/../includes/sidebar.php';

// Get database connection
$pdo = getConnection();

// Get statistics
try {
    // Total users (excluding admin)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin'");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total agents
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'agent'");
    $totalAgents = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total sub-agents
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'sub_agent'");
    $totalSubAgents = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total balance across all users
    $stmt = $pdo->query("SELECT SUM(balance) as total FROM users WHERE role != 'admin'");
    $totalBalance = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Recent transactions
    $stmt = $pdo->query("
        SELECT t.*, u.username 
        FROM transactions t 
        JOIN users u ON t.user_id = u.id 
        ORDER BY t.created_at DESC 
        LIMIT 5
    ");
    $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    setFlashMessage('danger', 'Error loading dashboard data.');
}
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Welcome Message -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <div class="btn-group">
                <a href="/admin/manage_agents.php" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i>Add New Agent
                </a>
                <a href="/admin/manage_balance.php" class="btn btn-success">
                    <i class="fas fa-coins me-2"></i>Manage Balance
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <!-- Total Users Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Users</h6>
                                <h2 class="card-title mb-0"><?php echo number_format($totalUsers); ?></h2>
                            </div>
                            <div class="icon-shape bg-primary text-white rounded-circle p-3">
                                <i class="fas fa-users fa-fw"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Agents Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Agents</h6>
                                <h2 class="card-title mb-0"><?php echo number_format($totalAgents); ?></h2>
                            </div>
                            <div class="icon-shape bg-success text-white rounded-circle p-3">
                                <i class="fas fa-user-tie fa-fw"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Sub-Agents Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Sub-Agents</h6>
                                <h2 class="card-title mb-0"><?php echo number_format($totalSubAgents); ?></h2>
                            </div>
                            <div class="icon-shape bg-info text-white rounded-circle p-3">
                                <i class="fas fa-user-friends fa-fw"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Balance Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Balance</h6>
                                <h2 class="card-title mb-0"><?php echo number_format($totalBalance); ?></h2>
                            </div>
                            <div class="icon-shape bg-warning text-white rounded-circle p-3">
                                <i class="fas fa-coins fa-fw"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Transactions</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTransactions as $transaction): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($transaction['username']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $transaction['type'] === 'deposit' ? 'success' : 
                                                ($transaction['type'] === 'withdrawal' ? 'danger' : 'info'); 
                                        ?>">
                                            <?php echo ucfirst($transaction['type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($transaction['amount']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $transaction['status'] === 'completed' ? 'success' : 
                                                ($transaction['status'] === 'pending' ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst($transaction['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y H:i', strtotime($transaction['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentTransactions)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No recent transactions</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-end">
                <a href="/admin/transactions.php" class="btn btn-primary btn-sm">
                    View All Transactions
                </a>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>