<?php
require_once __DIR__ . '/../functions/functions.php';

// Ensure user is agent
requireAgent();

$pageTitle = 'Manage IP Whitelist';
include_once __DIR__ . '/../includes/header.php';
include_once __DIR__ . '/../includes/navbar.php';
include_once __DIR__ . '/../includes/sidebar.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('danger', 'Invalid token. Please try again.');
        redirect('/agent/manage_whitelist.php');
    }

    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'add':
                $ipAddress = sanitizeInput($_POST['ip_address']);
                $description = sanitizeInput($_POST['description']);

                // Validate IP address
                if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
                    throw new Exception('Invalid IP address format.');
                }

                addAgentIPToWhitelist($_SESSION['user_id'], $ipAddress, $description);
                setFlashMessage('success', 'IP address added to whitelist successfully.');
                break;

            case 'remove':
                $ipId = (int)$_POST['ip_id'];
                
                if (removeAgentIPFromWhitelist($_SESSION['user_id'], $ipId)) {
                    setFlashMessage('success', 'IP address removed from whitelist.');
                } else {
                    throw new Exception('Failed to remove IP address.');
                }
                break;
        }
    } catch (Exception $e) {
        setFlashMessage('danger', 'Error: ' . $e->getMessage());
    }

    redirect('/agent/manage_whitelist.php');
}

// Get agent's whitelisted IPs
$whitelistedIPs = getAgentWhitelistedIPs($_SESSION['user_id']);
?>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="h3 mb-4">Manage IP Whitelist</h1>

        <!-- Current IP Information -->
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Your current IP address is: <strong><?php echo $_SERVER['REMOTE_ADDR']; ?></strong>
        </div>

        <!-- Add IP Form -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Add IP to Whitelist</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="add">

                    <div class="row g-3">
                        <div class="col-md-5">
                            <label for="ip_address" class="form-label">IP Address</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="ip_address" 
                                   name="ip_address" 
                                   pattern="^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$"
                                   required>
                            <div class="invalid-feedback">
                                Please enter a valid IP address.
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="description" 
                                   name="description" 
                                   placeholder="Optional description">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" 
                                    class="btn btn-primary w-100" 
                                    <?php echo count($whitelistedIPs) >= 2 ? 'disabled' : ''; ?>>
                                <i class="fas fa-plus me-2"></i>Add IP
                            </button>
                        </div>
                    </div>
                    <?php if (count($whitelistedIPs) >= 2): ?>
                        <div class="text-danger mt-2">
                            <small><i class="fas fa-exclamation-circle me-1"></i>Maximum number of whitelisted IPs (2) reached.</small>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Whitelisted IPs Table -->
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="card-title mb-0">Your Whitelisted IPs</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>IP Address</th>
                                <th>Description</th>
                                <th>Added On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($whitelistedIPs)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No IP addresses whitelisted yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($whitelistedIPs as $ip): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($ip['ip_address']); ?></td>
                                        <td><?php echo htmlspecialchars($ip['description'] ?: 'N/A'); ?></td>
                                        <td><?php echo date('d M Y H:i', strtotime($ip['created_at'])); ?></td>
                                        <td>
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Are you sure you want to remove this IP?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="ip_id" value="<?php echo $ip['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- API Usage Instructions -->
        <div class="card custom-card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">API Usage Instructions</h5>
            </div>
            <div class="card-body">
                <ol class="mb-0">
                    <li class="mb-2">Add up to 2 IP addresses that will be making API requests to your whitelist.</li>
                    <li class="mb-2">Only requests from your whitelisted IPs will be allowed to access the API.</li>
                    <li class="mb-2">Include your agent ID and ensure requests come from whitelisted IPs.</li>
                    <li class="mb-2">Include the <code>X-Requested-With: XMLHttpRequest</code> header in your API requests.</li>
                    <li>View the <a href="/api/">API documentation</a> for available endpoints and usage examples.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-fill current IP
document.addEventListener('DOMContentLoaded', function() {
    const currentIP = '<?php echo $_SERVER['REMOTE_ADDR']; ?>';
    const ipInput = document.getElementById('ip_address');
    
    if (ipInput && !ipInput.value) {
        ipInput.value = currentIP;
    }
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>