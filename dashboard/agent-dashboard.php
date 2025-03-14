<?php
require_once __DIR__ . '/../functions/functions.php';

// Ensure user is agent
requireAgent();

$pageTitle = 'Agent Dashboard';
include_once __DIR__ . '/../includes/header.php';
include_once __DIR__ . '/../includes/navbar.php';
include_once __DIR__ . '/../includes/sidebar.php';

// Get database connection
$pdo = getConnection();

try {
    // Get agent's current balance
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $agentBalance = $stmt->fetch(PDO::FETCH_ASSOC)['balance'] ?? 0;

    // Get total number of sub-agents
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE parent_id = ? AND role = 'sub_agent'");
    $stmt->execute([$_SESSION['user_id']]);
    $totalSubAgents = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get total balance of sub-agents
    $stmt = $pdo->prepare("SELECT SUM(balance) as total FROM users WHERE parent_id = ? AND role = 'sub_agent'");
    $stmt->execute([$_SESSION['user_id']]);
    $totalSubAgentBalance = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Get recent transactions
    $stmt = $pdo->prepare("
        SELECT t.*, u.username 
        FROM transactions t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.user_id = ? OR u.parent_id = ?
        ORDER BY t.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent sub-agents
    $stmt = $pdo->prepare("
        SELECT id, username, email, balance, created_at 
        FROM users 
        WHERE parent_id = ? AND role = 'sub_agent'
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recentSubAgents = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Agent Dashboard Error: " . $e->getMessage());
    setFlashMessage('danger', 'Error loading dashboard data.');
}
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Welcome Message -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <a href="/agent/manage_subagents.php" class="btn btn-primary">
                <i class="fas fa-user-plus me-2"></i>Add New Sub-Agent
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <!-- Agent Balance Card -->
            <div class="col-md-6 col-lg-4">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Your Balance</h6>
                                <h2 class="card-title mb-0"><?php echo number_format($agentBalance); ?></h2>
                            </div>
                            <div class="icon-shape bg-primary text-white rounded-circle p-3">
                                <i class="fas fa-wallet fa-fw"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Sub-Agents Card -->
            <div class="col-md-6 col-lg-4">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Total Sub-Agents</h6>
                                <h2 class="card-title mb-0"><?php echo number_format($totalSubAgents); ?></h2>
                            </div>
                            <div class="icon-shape bg-success text-white rounded-circle p-3">
                                <i class="fas fa-users fa-fw"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sub-Agents Balance Card -->
            <div class="col-md-6 col-lg-4">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2 text-muted">Sub-Agents Balance</h6>
                                <h2 class="card-title mb-0"><?php echo number_format($totalSubAgentBalance); ?></h2>
                            </div>
                            <div class="icon-shape bg-info text-white rounded-circle p-3">
                                <i class="fas fa-coins fa-fw"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Transactions -->
            <div class="col-12 col-xl-7 mb-4">
                <div class="card custom-card h-100">
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
                        <a href="/agent/transactions.php" class="btn btn-primary btn-sm">
                            View All Transactions
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Sub-Agents -->
            <div class="col-12 col-xl-5 mb-4">
                <div class="card custom-card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Sub-Agents</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Balance</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentSubAgents as $agent): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($agent['username']); ?></td>
                                            <td><?php echo number_format($agent['balance']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($agent['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($recentSubAgents)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center">No sub-agents yet</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <a href="/agent/manage_subagents.php" class="btn btn-primary btn-sm">
                            Manage Sub-Agents
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>